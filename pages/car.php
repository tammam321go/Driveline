<?php
include("../controllers/page_controller.php");
require_once("../repositories/db_connect.php");

if (!isset($_SESSION['user']['id'])) {
    die("Not logged in.");
}
$userId = (int) $_SESSION['user']['id'];

if (!isset($_GET['c_id']) || !is_numeric($_GET['c_id'])) {
    die("Invalid Car ID");
}
$carId = (int) $_GET['c_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'give') {
            $stars = (int) ($_POST['stars'] ?? 0);
            $topic = trim($_POST['topic'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // prevent duplicate review by same user for same car
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE u_id = :u_id AND c_id = :c_id");
            $checkStmt->execute([':u_id' => $userId, ':c_id' => $carId]);
            $alreadyReviewed = (int) $checkStmt->fetchColumn();

            if ($alreadyReviewed > 0) {
                echo "<script>alert('You have already reviewed this car. Please edit your review instead.');</script>";
            } else {
                $stmt = $pdo->prepare("CALL GiveReview(:u_id, :c_id, :stars, :topic, :description)");
                $stmt->execute([
                    ':u_id' => $userId,
                    ':c_id' => $carId,
                    ':stars' => $stars,
                    ':topic' => $topic,
                    ':description' => $description
                ]);
                $stmt->closeCursor();
            }
        } elseif ($action === 'edit') {
            $reviewId = (int) ($_POST['review_id'] ?? 0);
            $stars = (int) ($_POST['stars'] ?? 0);
            $topic = trim($_POST['topic'] ?? '');
            $description = trim($_POST['description'] ?? '');

            $stmt = $pdo->prepare("CALL EditReview(:r_id, :u_id, :stars, :topic, :description)");
            $stmt->execute([
                ':r_id' => $reviewId,
                ':u_id' => $userId,
                ':stars' => $stars,
                ':topic' => $topic,
                ':description' => $description
            ]);
            $stmt->closeCursor();
        } elseif ($action === 'delete') {
            $reviewId = (int) ($_POST['review_id'] ?? 0);

            $stmt = $pdo->prepare("CALL DeleteReview(:r_id, :u_id)");
            $stmt->execute([
                ':r_id' => $reviewId,
                ':u_id' => $userId
            ]);
            $stmt->closeCursor();
        } elseif ($action === 'like') {
            $reviewId = (int) ($_POST['review_id'] ?? 0);

            $stmt = $pdo->prepare("CALL AddReviewLike(:r_id, :u_id)");
            $stmt->execute([
                ':r_id' => $reviewId,
                ':u_id' => $userId
            ]);
            $stmt->closeCursor();
        } elseif ($action === 'unlike') {
            $reviewId = (int) ($_POST['review_id'] ?? 0);

            $stmt = $pdo->prepare("CALL RemoveReviewLike(:r_id, :u_id)");
            $stmt->execute([
                ':r_id' => $reviewId,
                ':u_id' => $userId
            ]);
            $stmt->closeCursor();
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch (PDOException $e) {
        die("Database error (action): " . $e->getMessage());
    }
}


$ownStmt = $pdo->prepare("SELECT * FROM reviews WHERE u_id = :u_id AND c_id = :c_id LIMIT 1");
$ownStmt->execute([':u_id' => $userId, ':c_id' => $carId]);
$ownReview = $ownStmt->fetch(PDO::FETCH_ASSOC);

try {
    $stmtAvgUser = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
    $stmtAvgUser->bindParam(':car_id', $carId, PDO::PARAM_INT);
    $stmtAvgUser->execute();
    $avgDataUser = $stmtAvgUser->fetch(PDO::FETCH_ASSOC);
    $stmtAvgUser->closeCursor();

    $avgRatingUser = $avgDataUser['avg_rating'] ?? 0;
    $totalReviewsUser = $avgDataUser['total_reviews'] ?? 0;
} catch (PDOException $e) {
    die("Database error (avg): " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("CALL GetCarDetailsWithImages(:car_id)");
    $stmt->bindParam(':car_id', $carId, PDO::PARAM_INT);
    $stmt->execute();

    $carDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->nextRowset();
    $carImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


try {
    $stmtReviews = $pdo->prepare("CALL GetCarReviews(:car_id)");
    $stmtReviews->bindParam(':car_id', $carId, PDO::PARAM_INT);
    $stmtReviews->execute();
    $carReviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);
    $stmtReviews->closeCursor();
} catch (PDOException $e) {
    die("Database error (reviews): " . $e->getMessage());
}


$reviewIds = array_map(fn($r) => (int)$r['r_id'], $carReviews);
$likeCounts = [];
$userLikedSet = [];

if (!empty($reviewIds)) {

    $inPlaceholders = implode(',', array_fill(0, count($reviewIds), '?'));
    $q1 = $pdo->prepare("SELECT r_id, COUNT(*) AS total_likes FROM review_likes WHERE r_id IN ($inPlaceholders) GROUP BY r_id");
    $q1->execute($reviewIds);
    foreach ($q1->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $likeCounts[(int)$row['r_id']] = (int)$row['total_likes'];
    }

    // Which of these the user liked
    $params = $reviewIds;
    array_unshift($params, $userId);
    $q2 = $pdo->prepare("SELECT r_id FROM review_likes WHERE u_id = ? AND r_id IN ($inPlaceholders)");
    $q2->execute($params);
    foreach ($q2->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $userLikedSet[(int)$row['r_id']] = true;
    }
}


$ownReviews = [];
$otherReviews = [];
foreach ($carReviews as $review) {
    if ((int)$review['u_id'] === $userId) {
        $ownReviews[] = $review;
    } else {
        $otherReviews[] = $review;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($carDetails['c_name'] ?? 'Car Details') ?> - Car Details</title>
    <link rel="stylesheet" href="../styles/styles/car.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
</head>
<body>

<header>
  <div class="navbar">
      <div id="back_button" class="nav-button">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back</span>
      </div>
  </div>
</header>

<main class="car-details-container">
  <div class="image-section">
    <?php if (!empty($carImages)) : ?>
      <div class="image-zoom-container">
          <img id="mainImage" 
               src="<?= htmlspecialchars($carImages[0]['img_url']) ?>" 
               alt="Car Image" 
               class="main-image">
          <div id="zoomLens"></div>
          <div id="zoomResult"></div>
      </div>

      <div class="thumbnail-container">
          <?php foreach ($carImages as $img): ?>
              <img src="<?= htmlspecialchars($img['img_url']) ?>" 
                   class="thumbnail" 
                   onclick="changeImage(this)">
          <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No images available for this car.</p>
    <?php endif; ?>
  </div>

  <div class="details-section">
      <h1><?= htmlspecialchars($carDetails['c_name'] ?? '') ?></h1>
      <p><strong>Type:</strong> <?= htmlspecialchars($carDetails['c_type'] ?? '') ?></p>
      <p><strong>Brand:</strong> <?= htmlspecialchars($carDetails['c_brand'] ?? '') ?></p>
      <p><strong>Model:</strong> <?= htmlspecialchars($carDetails['c_model'] ?? '') ?></p>
      <p><strong>Engine:</strong> <?= htmlspecialchars($carDetails['c_engine'] ?? '') ?></p>
      <p><strong>Release Date:</strong> <?= htmlspecialchars($carDetails['c_release_date'] ?? '') ?></p>
      <p><strong>Country:</strong> <?= htmlspecialchars($carDetails['c_country'] ?? '') ?></p>
      
      <?php if (!empty($carDetails['c_features'])): ?>
          <div class="info-block">
              <h3>Features</h3>
              <p><?= nl2br(htmlspecialchars($carDetails['c_features'])) ?></p>
          </div>
      <?php endif; ?>

      <?php if (!empty($carDetails['c_interesting_facts'])): ?>
          <div class="info-block">
              <h3>Interesting Facts</h3>
              <p><?= nl2br(htmlspecialchars($carDetails['c_interesting_facts'])) ?></p>
          </div>
      <?php endif; ?>

      <?php if (!empty($carDetails['c_description'])): ?>
          <div class="info-block">
              <h3>Description</h3>
              <p><?= nl2br(htmlspecialchars($carDetails['c_description'])) ?></p>
          </div>
      <?php endif; ?>
  </div>
</main>

<section class="review-section">
  <div class="review-form">
  <?php if (!$ownReview): ?>
      <h2>Write a Review</h2>
      <form method="POST" class="give-review-form">
          <input type="hidden" name="action" value="give">
          <input type="hidden" name="c_id" value="<?= $carId ?>">

          <label for="stars">Stars:</label>
          <select id="stars" name="stars" required>
              <option value="5">★★★★★</option>
              <option value="4">★★★★☆</option>
              <option value="3">★★★☆☆</option>
              <option value="2">★★☆☆☆</option>
              <option value="1">★☆☆☆☆</option>
          </select>

          <label for="topic">Topic:</label>
          <input type="text" id="topic" name="topic" placeholder="Short summary" required>

          <label for="description">Description:</label>
          <textarea id="description" name="description" placeholder="Your detailed review..." required></textarea>

          <div class="form-actions" style="justify-content:flex-end;">
              <button type="submit" class="btn-save">Save</button>
          </div>
      </form>
  <?php endif; ?>
  </div>

  <div class="review-list">
    <h2>User Reviews</h2>
    <p class="review-summary">
        ⭐ <?= htmlspecialchars((string)$avgRatingUser) ?> / 5 
        (<?= htmlspecialchars((string)$totalReviewsUser) ?> reviews)
    </p>

    <div class="review-scroll">
      <?php if (!empty($ownReviews)): ?>
        <?php foreach ($ownReviews as $review): ?>
          <div class="review-card own-review" id="review-<?= (int)$review['r_id'] ?>">
              <div class="review-header">
                  <span class="review-user">
                      <?= htmlspecialchars($review['u_name']) ?>
                      <span class="review-usertype">(<?= htmlspecialchars($review['u_type']) ?>)</span>
                  </span>
                  <span class="review-stars">
                      <?= str_repeat("★", (int)$review['r_star']) . str_repeat("☆", 5 - (int)$review['r_star']) ?>
                  </span>
              </div>

              <div class="review-topic"><strong><?= htmlspecialchars($review['r_topic']) ?></strong></div>
              <div class="review-text"><?= nl2br(htmlspecialchars($review['r_description'])) ?></div>

              <div class="review-footer" style="display:flex;gap:12px;align-items:center;margin-top:8px;">
                  <span title="Total likes">
                    <i class="fa-solid fa-heart"></i>
                    <?= (int)($likeCounts[(int)$review['r_id']] ?? 0) ?>
                  </span>
              </div>

              <div class="review-actions">
                  <!-- Edit -->
                  <button type="button" class="btn-edit" onclick="editReview(<?= (int)$review['r_id'] ?>)">
                      <i class="fa-solid fa-pen"></i>
                  </button>
                  <!-- Delete -->
                  <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="review_id" value="<?= (int)$review['r_id'] ?>">
                      <input type="hidden" name="c_id" value="<?= $carId ?>">
                      <button type="submit" class="btn-delete" onclick="return confirm('Delete this review?')">
                          <i class="fa-solid fa-trash"></i>
                      </button>
                  </form>
              </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($otherReviews)): ?>
        <?php foreach ($otherReviews as $review): ?>
          <?php
            $rid = (int)$review['r_id'];
            $totalLikes = (int)($likeCounts[$rid] ?? 0);
            $hasLiked = !empty($userLikedSet[$rid]);
          ?>
          <div class="review-card" id="review-<?= $rid ?>">
              <div class="review-header">
                  <span class="review-user">
                      <?= htmlspecialchars($review['u_name']) ?>
                      <span class="review-usertype">(<?= htmlspecialchars($review['u_type']) ?>)</span>
                  </span>
                  <span class="review-stars">
                      <?= str_repeat("★", (int)$review['r_star']) . str_repeat("☆", 5 - (int)$review['r_star']) ?>
                  </span>
              </div>

              <div class="review-topic"><strong><?= htmlspecialchars($review['r_topic']) ?></strong></div>
              <div class="review-text"><?= nl2br(htmlspecialchars($review['r_description'])) ?></div>

              <div class="review-footer" style="display:flex;gap:12px;align-items:center;margin-top:8px;">
                  <span title="Total likes">
                    <i class="fa-solid fa-heart"></i>
                    <?= $totalLikes ?>
                  </span>

                  <div class="review-actions" style="margin-left:auto;">
                      <form method="POST" style="display:inline;">
                          <input type="hidden" name="review_id" value="<?= $rid ?>">
                          <?php if ($hasLiked): ?>
                              <input type="hidden" name="action" value="unlike">
                              <button type="submit" class="btn-unlike" title="Unlike this review">
                                  <i class="fa-solid fa-thumbs-down"></i> Unlike
                              </button>
                          <?php else: ?>
                              <input type="hidden" name="action" value="like">
                              <button type="submit" class="btn-like" title="Like this review">
                                  <i class="fa-solid fa-thumbs-up"></i> Like
                              </button>
                          <?php endif; ?>
                      </form>
                  </div>
              </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer class="custom-footer">
    <div class="footer-content">
        <div class="footer-column">
            <h3>Help Centre</h3>
            <p>Monday to Friday 9.00 - 18.00</p>
            <p>Saturday 9.00 - 17.30</p>
            <p>Sundays and Bank Holidays CLOSED</p>
            <div class="social-icons">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-x-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-youtube"></i>
                <i class="fab fa-tiktok"></i>
            </div>
        </div>
        <div class="footer-column">
            <a href="#">About us</a>
            <a href="#">Contact us</a>
            <a href="#">Authors and experts</a>
            <a href="#">Carverly newsroom</a>
        </div>
        <div class="footer-column">
            <a href="#">Careers</a>
            <a href="#">Dealer & brand partners</a>
        </div>
    </div>
    <hr>
    <div class="footer-bottom">
        <p>© 2025 Carverly Ltd. All rights reserved</p>
    </div>
</footer>

<script>
function imageZoom(imgID, resultID, lensID) {
    let img = document.getElementById(imgID);
    let result = document.getElementById(resultID);
    let lens = document.getElementById(lensID);

    let scaleX, scaleY, drawOffsetX, drawOffsetY, drawWidth, drawHeight;

    function initZoom() {
        if (img.naturalWidth === 0) {
            img.onload = initZoom;
            return;
        }

        let rect = img.getBoundingClientRect();

        let imgAspect = img.naturalWidth / img.naturalHeight;
        let containerAspect = rect.width / rect.height;

        if (imgAspect > containerAspect) {
            drawWidth = rect.width;
            drawHeight = rect.width / imgAspect;
            drawOffsetX = 0;
            drawOffsetY = (rect.height - drawHeight) / 2;
        } else {
            drawHeight = rect.height;
            drawWidth = rect.height * imgAspect;
            drawOffsetX = (rect.width - drawWidth) / 2;
            drawOffsetY = 0;
        }

        scaleX = img.naturalWidth / drawWidth;
        scaleY = img.naturalHeight / drawHeight;

        result.style.backgroundImage = `url('${img.src}')`;
        result.style.backgroundSize = `${img.naturalWidth}px ${img.naturalHeight}px`;
    }

    function moveLens(e) {
        e.preventDefault();
        let rect = img.getBoundingClientRect();

        let x = e.clientX - rect.left - lens.offsetWidth / 2 - drawOffsetX;
        let y = e.clientY - rect.top - lens.offsetHeight / 2 - drawOffsetY;

        if (x < 0) x = 0;
        if (y < 0) y = 0;
        if (x > drawWidth - lens.offsetWidth) x = drawWidth - lens.offsetWidth;
        if (y > drawHeight - lens.offsetHeight) y = drawHeight - lens.offsetHeight;

        lens.style.left = `${x + drawOffsetX}px`;
        lens.style.top = `${y + drawOffsetY}px`;

        let natX = (x * scaleX) + (lens.offsetWidth / 2) * scaleX - (result.offsetWidth / 2);
        let natY = (y * scaleY) + (lens.offsetHeight / 2) * scaleY - (result.offsetHeight / 2);

        result.style.backgroundPosition = `-${natX}px -${natY}px`;
    }

    img.parentElement.addEventListener("mouseenter", () => {
        initZoom();
        lens.style.display = "block";
        result.style.display = "block";
    });

    img.parentElement.addEventListener("mouseleave", () => {
        lens.style.display = "none";
        result.style.display = "none";
    });

    img.parentElement.addEventListener("mousemove", moveLens);
    lens.addEventListener("mousemove", moveLens);

    initZoom();
}

const updateZoom = () => imageZoom("mainImage", "zoomResult", "zoomLens");
updateZoom();

function changeImage(element) {
    let mainImage = document.getElementById("mainImage");
    mainImage.src = element.src;
    mainImage.onload = updateZoom;
}

// Back button
document.getElementById("back_button").addEventListener("click", () => {
    window.location.href = "../pages/homepage.php";
});
</script>

<script>
function editReview(id) {
    const reviewCard = document.getElementById(`review-${id}`);
    if (!reviewCard) return;

    const originalHTML = reviewCard.innerHTML;

    const topicEl = reviewCard.querySelector('.review-topic');
    const textEl = reviewCard.querySelector('.review-text');
    const starsEl = reviewCard.querySelector('.review-stars');

    const topic = topicEl ? topicEl.innerText.trim() : '';
    const description = textEl ? textEl.innerText.trim() : '';
    const starsCount = starsEl ? starsEl.innerText.replace(/☆/g, '').length : 5;

    reviewCard.innerHTML = `
        <form method="POST" class="edit-review-form" style="display:flex;flex-direction:column;gap:10px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="review_id" value="${id}">
            <input type="hidden" name="c_id" value="<?= $carId ?>">

            <h3 style="margin:0;color:#002b5c;">Edit Review</h3>

            <label>Stars</label>
            <select name="stars" required>
                <option value="5" ${starsCount==5 ? 'selected' : ''}>★★★★★</option>
                <option value="4" ${starsCount==4 ? 'selected' : ''}>★★★★☆</option>
                <option value="3" ${starsCount==3 ? 'selected' : ''}>★★★☆☆</option>
                <option value="2" ${starsCount==2 ? 'selected' : ''}>★★☆☆☆</option>
                <option value="1" ${starsCount==1 ? 'selected' : ''}>★☆☆☆☆</option>
            </select>

            <label>Topic</label>
            <input type="text" name="topic" value="${escapeHtml(topic)}" required>

            <label>Description</label>
            <textarea name="description" required>${escapeHtml(description)}</textarea>

            <div class="form-actions" style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="submit" class="btn-save">Save</button>
                <button type="button" class="btn-cancel">Cancel</button>
            </div>
        </form>
    `;

    const cancelBtn = reviewCard.querySelector('.btn-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            reviewCard.innerHTML = originalHTML;
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
}
</script>

</body>
</html>
