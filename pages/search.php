<?php
include("../controllers/page_controller.php");

// DB connect
require_once __DIR__ . '/../repositories/db_connect.php';

// Read filters
$brand      = $_GET['brand']      ?? '';
$engine     = $_GET['engine']     ?? '';
$year_start = $_GET['year_start'] ?? '';
$year_end   = $_GET['year_end']   ?? '';
$country    = $_GET['country']    ?? '';
$keyword    = $_GET['keyword']    ?? '';

$year_start = ($year_start === '') ? null : (int)$year_start;
$year_end   = ($year_end   === '') ? null : (int)$year_end;

$rows  = [];
$error = null;

//average reviews
foreach ($rows as &$car) {
  $carId = $car['c_id'] ?? 0;

  $stmtAvg = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
  $stmtAvg->bindParam(':car_id', $carId, PDO::PARAM_INT);
  $stmtAvg->execute();
  $avgData = $stmtAvg->fetch(PDO::FETCH_ASSOC);
  $stmtAvg->closeCursor();

  // Attach review info with defaults
  $car['avg_rating']    = $avgData['avg_rating'] ?? 0;
  $car['total_reviews'] = $avgData['total_reviews'] ?? 0;
}
unset($car); // break reference

// DB search
if (isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("CALL search_cars(?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $error = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("ssiiss", $brand, $engine, $year_start, $year_end, $country, $keyword);
        if (!$stmt->execute()) {
            $error = "Execute failed: " . $stmt->error;
        } else {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
        }
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) {}
    }
} elseif (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("CALL search_cars(?, ?, ?, ?, ?, ?)");
        $stmt->execute([$brand, $engine, $year_start, $year_end, $country, $keyword]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
} else {
    $error = "No database connection found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search Cars — Carverly</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Search Page Styles -->
  <link rel="stylesheet" href="../styles/styles/search.css" />
</head>
<body>
<header>
    <div class="navbar">
        <div class="brand-container">
            <img src="https://i.postimg.cc/63bx44WN/car-logo.jpg" alt="Logo" />
            <div class="logo-text">carverly</div>
        </div>
        <div class="nav-icons">
          
        </div>
    </div>
</header>

<div class="breadcrumb">
    <a href="homepage.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <span>Search</span>
</div>

<h1 class="section-title">Find your next car</h1>

<main class="wrap">
    <!-- Search form -->
    <form method="get" class="search-form" autocomplete="off">
      <div class="grid">
        <div class="field">
          <label>Brand</label>
          <input type="text" name="brand" value="<?= htmlspecialchars($brand) ?>">
        </div>
        <div class="field">
          <label>Engine (contains)</label>
          <input type="text" name="engine" value="<?= htmlspecialchars($engine) ?>">
        </div>
        <div class="field">
          <label>Release Year From</label>
          <input type="number" name="year_start" value="<?= htmlspecialchars($_GET['year_start'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Release Year To</label>
          <input type="number" name="year_end" value="<?= htmlspecialchars($_GET['year_end'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Country</label>
          <input type="text" name="country" value="<?= htmlspecialchars($country) ?>">
        </div>
        <div class="field">
          <label>Keyword</label>
          <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="actions">
  <button class="btn search-btn" type="submit">
    <i class="fa-solid fa-magnifying-glass"></i> Search
  </button>
  <a class="btn clear-btn" href="search.php">Clear</a>
</div>

      </div>
    </form>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Results -->
    <section class="results">
    <table class="results-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Model</th>
      <th>Brand</th>
      <th>Engine</th>
      <th>Release Date</th>
      <th>Country</th>
      <th>Reviews</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($rows)): ?>
      <?php foreach ($rows as $r): ?>
      <tr onclick="window.location='car.php?c_id=<?= urlencode($r['c_id'] ?? '') ?>'">
        <td>
          <div class="car-name"><?= htmlspecialchars($r['c_name'] ?? '') ?></div>
          <div class="muted small"><?= htmlspecialchars(mb_strimwidth($r['c_description'] ?? '', 0, 120, '…')) ?></div>
        </td>
        <td><?= htmlspecialchars($r['c_model'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_brand'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_engine'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_release_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_country'] ?? '') ?></td>
        <td>
        ⭐ <?= htmlspecialchars($r['avg_rating'] ?? 0) ?> / 5 
        (<?= htmlspecialchars($r['total_reviews'] ?? 0) ?>)
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="7" class="muted">No results found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

    </section>
</main>

<footer class="custom-footer small-footer">
    <div class="footer-content">
        <div class="footer-column">
            <h3>Help Centre</h3>
            <p>Mon–Fri: 9.00 - 18.00</p>
        </div>
        <div class="footer-column">
            <a href="#">About us</a>
            <a href="#">Contact us</a>
        </div>
        <div class="footer-column trustpilot">
            <p>Rated <strong>4.4</strong>/5</p>
        </div>
    </div>
    <hr>
    <div class="footer-bottom">
        <p>© 2025 Carverly Ltd. All rights reserved</p>
    </div>
</footer>

<script>
document.getElementById("logoutBtn").addEventListener("click", function () {
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = "../controllers/logout.php";
    }
});
document.getElementById("ProfileBtn").addEventListener("click", function () {
    window.location.href = "../pages/userprofile.php";
});
</script>
</body>
</html>
