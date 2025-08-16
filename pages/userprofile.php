<?php
session_start();
require_once("../repositories/db_connect.php");

// Get the logged-in user's ID
$user_id = $_SESSION['user']['id'];

$query = "CALL get_user_profile_by_session(?)";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor(); 


// Optional: If user not found (e.g., deleted), redirect or show error
if (!$user) {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Information</title>
  <link rel="stylesheet" href="../styles/styles/profile.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
</head>
<body>

</head>

<body>
  <header>
    <div class="navbar">
        
        <div id="back_button" class="nav-button">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </div>

        
        <div id="edit_profile" class="nav-button">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit Profile</span>
        </div>
    </div>
  </header>

  <main> 
  <div class="profile-panel">
    <!-- Left Card: Profile Info -->
    <div class="profile-card" id="profile-card">
      <img src="<?= htmlspecialchars($user['u_profile_pic']) ?>" alt="Profile Picture">
      <div class="mini-info">
        <h3><?= htmlspecialchars($user['u_name']) ?></h3>
        <p><?= htmlspecialchars($user['u_type']) ?></p>
        <p><?= htmlspecialchars($user['u_email']) ?></p>
        <p><strong>Points:</strong> <?= htmlspecialchars($user['u_points']) ?></p>
        <p><strong>About</strong></p>
        <p><?= nl2br(htmlspecialchars($user['u_about'])) ?></p>
      </div>
    </div>

    <!-- Right Card: Recent Activities -->
    <div class="profile-card" id="activities-card">
      <h3>Recent Activities</h3>
      <p>No activities yet.</p>
    </div>
  </div>
</main>




<!-- Footer -->
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

    <div class="footer-column trustpilot">
      <p>Rated <strong>4.4</strong>/5 from <strong>3,590</strong> reviews</p>
      <p style="font-size: 22px; color: #00B67A; font-weight: bold;">★ Trustcarverly</p>
      <p>⭐⭐⭐⭐⭐</p>
    </div>
  </div>

  <hr>

  <div class="footer-bottom">
    <p>© 2025 Carverly Ltd. All rights reserved</p>
    <div class="footer-links">
      <a href="#">Terms & conditions</a>
      <a href="#">Manage cookies & privacy</a>
      <a href="#">Fraud disclaimer</a>
      <a href="#">ESG Policy</a>
      <a href="#">Privacy policy</a>
      <a href="#">Modern slavery statement</a>
      <a href="#">Accessibility notice</a>
      <a href="#">Sitemap</a>
    </div>
    <div class="footer-flags">
      <span><i class="fa-solid fa-flag"></i> BD </span>
      <span><i class="fa-solid fa-flag"></i> Germany </span>
      <span><i class="fa-solid fa-flag"></i> Spain </span>
    </div>
  </div>
</footer>
<script>
document.getElementById("back_button").addEventListener("click", function () {
  window.location.href = "../pages/homepage.php"; 
});

document.getElementById("edit_profile").addEventListener("click", function () {
  window.location.href = "../pages/edit_profile.php"; 
});


</script>
</body>
</html>
