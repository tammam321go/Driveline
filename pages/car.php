<?php
session_start();
require_once("../repositories/db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: ../pages/login.html");
    exit;
}

if (!isset($_GET['c_id']) || !is_numeric($_GET['c_id'])) {
    die("Invalid Car ID");
}

$carId = (int) $_GET['c_id'];

//GiveReview
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user']['id'];
    $carId = isset($_POST['c_id']) ? (int) $_POST['c_id'] : (int) $_GET['c_id'];

    if (isset($_POST['action']) && $_POST['action'] === 'give') {
        $stars = (int) $_POST['stars'];
        $topic = trim($_POST['topic']);
        $description = trim($_POST['description']);

        $stmt = $pdo->prepare("CALL GiveReview(:u_id, :c_id, :stars, :topic, :description)");
        $stmt->execute([
            ':u_id' => $userId,
            ':c_id' => $carId,
            ':stars' => $stars,
            ':topic' => $topic,
            ':description' => $description
        ]);
    }
//EditReview
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $reviewId = (int) $_POST['review_id'];
        $stars = (int) $_POST['stars'];
        $topic = trim($_POST['topic']);
        $description = trim($_POST['description']);

        $stmt = $pdo->prepare("CALL EditReview(:r_id, :u_id, :stars, :topic, :description)");
        $stmt->execute([
            ':r_id' => $reviewId,
            ':u_id' => $userId,
            ':stars' => $stars,
            ':topic' => $topic,
            ':description' => $description
        ]);
    }
//DeleteReview
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $reviewId = (int) $_POST['review_id'];

        $stmt = $pdo->prepare("CALL DeleteReview(:r_id, :u_id)");
        $stmt->execute([
            ':r_id' => $reviewId,
            ':u_id' => $userId
        ]);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($carDetails['c_name']) ?> - Car Details</title>
    <link rel="stylesheet" href="../styles/styles/car.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
</head>
<body>

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
        <h1><?= htmlspecialchars($carDetails['c_name']) ?></h1>
        <p><strong>Type:</strong> <?= htmlspecialchars($carDetails['c_type']) ?></p>
        <p><strong>Brand:</strong> <?= htmlspecialchars($carDetails['c_brand']) ?></p>
        <p><strong>Model:</strong> <?= htmlspecialchars($carDetails['c_model']) ?></p>
        <p><strong>Engine:</strong> <?= htmlspecialchars($carDetails['c_engine']) ?></p>
        <p><strong>Release Date:</strong> <?= htmlspecialchars($carDetails['c_release_date']) ?></p>
        <p><strong>Country:</strong> <?= htmlspecialchars($carDetails['c_country']) ?></p>
        
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

    
</section>

</main>

<section class="review-section">
    
<div class="review-form">
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
        <input type="text" id="topic" name="topic" placeholder="Short summary (e.g. 'Smooth ride')" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" placeholder="Tell others about your experience..." required></textarea>

        <div class="form-actions" style="justify-content:flex-end;">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>


    <!-- Right: Reviews -->
    <div class="review-list">
        <h2>User Reviews</h2>
        <div class="review-scroll">

            <?php
            $ownReview = [];
            $otherReviews = [];

            if (!empty($carReviews)) {
                foreach ($carReviews as $review) {
                    if (
                        isset($_SESSION['user']['id'], $review['u_id']) &&
                        (int)$review['u_id'] === (int)$_SESSION['user']['id']
                    ) {
                        $ownReview[] = $review;
                    } else {
                        $otherReviews[] = $review;
                    }
                }
            }

            if (!empty($ownReview)) {
                foreach ($ownReview as $review) {
            ?>
                <div class="review-card own-review" id="review-<?= $review['r_id'] ?>">
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
            
                    <div class="review-actions">
                        <!-- Edit Button -->
                        <button type="button" class="btn-edit" onclick="editReview(<?= $review['r_id'] ?>)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
            
                        <!-- Delete Review -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="review_id" value="<?= $review['r_id'] ?>">
                            <input type="hidden" name="c_id" value="<?= $carId ?>">
                            <button type="submit" class="btn-delete" onclick="return confirm('Delete this review?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            
           
            if (!empty($otherReviews)) {
                foreach ($otherReviews as $review) {
            ?>
                <div class="review-card" id="review-<?= $review['r_id'] ?>">
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
                </div>
            <?php
                }
            }
            ?>          
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

        // Calculate actual drawn image size (account for aspect ratio & padding)
        let imgAspect = img.naturalWidth / img.naturalHeight;
        let containerAspect = rect.width / rect.height;

        if (imgAspect > containerAspect) {
            // Image fills width, height has top/bottom padding
            drawWidth = rect.width;
            drawHeight = rect.width / imgAspect;
            drawOffsetX = 0;
            drawOffsetY = (rect.height - drawHeight) / 2;
        } else {
            // Image fills height, width has left/right padding
            drawHeight = rect.height;
            drawWidth = rect.height * imgAspect;
            drawOffsetX = (rect.width - drawWidth) / 2;
            drawOffsetY = 0;
        }

        // Map displayed pixels to natural pixels
        scaleX = img.naturalWidth / drawWidth;
        scaleY = img.naturalHeight / drawHeight;

        // Use natural resolution for zoom background
        result.style.backgroundImage = `url('${img.src}')`;
        result.style.backgroundSize = `${img.naturalWidth}px ${img.naturalHeight}px`;
    }

    function moveLens(e) {
        e.preventDefault();
        let rect = img.getBoundingClientRect();

        // Mouse position within actual drawn area
        let x = e.clientX - rect.left - lens.offsetWidth / 2 - drawOffsetX;
        let y = e.clientY - rect.top - lens.offsetHeight / 2 - drawOffsetY;

        // Limit lens movement inside drawn image only
        if (x < 0) x = 0;
        if (y < 0) y = 0;
        if (x > drawWidth - lens.offsetWidth) x = drawWidth - lens.offsetWidth;
        if (y > drawHeight - lens.offsetHeight) y = drawHeight - lens.offsetHeight;

        // Position lens over actual image area
        lens.style.left = `${x + drawOffsetX}px`;
        lens.style.top = `${y + drawOffsetY}px`;

        // Calculate background position in natural pixels
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



// Keep your back button event
document.getElementById("back_button").addEventListener("click", () => {
    window.location.href = "../pages/homepage.php";
});

</script>

<script>
function editReview(id) {
    const reviewCard = document.getElementById(`review-${id}`);
    if (!reviewCard) return;

    // Keep original HTML so we can restore on cancel
    const originalHTML = reviewCard.innerHTML;

    // Grab visible values (topic and description may contain line breaks)
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

    // Cancel restores original card without reloading
    const cancelBtn = reviewCard.querySelector('.btn-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            reviewCard.innerHTML = originalHTML;
        });
    }

    // small helper to guard injected text (prevent breaking template)
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