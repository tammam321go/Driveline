<?php
session_start();
require_once("../repositories/db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: ../pages/login.html");
    exit;
}

$u_id = $_SESSION['user']['id'];
$message = "";

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['u_name'] ?? '';
    $email = $_POST['u_email'] ?? '';
    $about = $_POST['u_about'] ?? '';

    // Profile picture (URL or file upload)
    $profile_pic = $_POST['u_profile_pic'] ?? '';

    if (!empty($_FILES['profile_pic_upload']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["profile_pic_upload"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["profile_pic_upload"]["tmp_name"], $target_file)) {
            $profile_pic = $target_file;
        }
    }

    try {
        $stmt = $pdo->prepare("CALL update_user_profile(:u_id, :u_name, :u_email, :u_about, :u_profile_pic)");
        $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
        $stmt->bindParam(':u_name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':u_email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':u_about', $about, PDO::PARAM_STR);
        $stmt->bindParam(':u_profile_pic', $profile_pic, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        $message = "✅ Profile updated successfully!";
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// --- Fetch user profile for display ---
$stmt = $pdo->prepare("CALL get_user_profile_by_session(:u_id)");
$stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../styles/styles/profile.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="navbar">
        
        <div id="back_button" class="nav-button">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </div>

    </div>
  </header>

  <!-- Main Content -->
  <main>
    <div class="profile-panel">
      <!-- Profile Card -->
      <div class="profile-card" id="profile-card">
        <img src="<?= htmlspecialchars($user['u_profile_pic'] ?? 'https://via.placeholder.com/250') ?>" alt="Profile Picture">
        <div class="mini-info">
          <h3><?= htmlspecialchars($user['u_name'] ?? 'Unknown User') ?></h3>
          <p><?= htmlspecialchars($user['u_email'] ?? '') ?></p>
        </div>
      </div>

      <!-- Edit Form -->
<div class="profile-details">
  <div class="profile-section edit-box">
    <h2>Edit Your Information</h2>
    <?php if ($message): ?>
      <p class="status-msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    
    <form action="" method="post" enctype="multipart/form-data" class="edit-form">
      <div class="form-group">
        <label for="u_name">Name</label>
        <input type="text" id="u_name" name="u_name" value="<?= htmlspecialchars($user['u_name'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="u_email">Email</label>
        <input type="email" id="u_email" name="u_email" value="<?= htmlspecialchars($user['u_email'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="u_about">About</label>
        <textarea id="u_about" name="u_about" rows="4"><?= htmlspecialchars($user['u_about'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label for="u_profile_pic">Profile Picture (URL)</label>
        <input type="text" id="u_profile_pic" name="u_profile_pic" value="<?= htmlspecialchars($user['u_profile_pic'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="profile_pic_upload">Or Upload New Picture</label>
        <input type="file" id="profile_pic_upload" name="profile_pic_upload" accept="image/*">
      </div>

      <button type="submit" class="update-btn">
        <i class="fa-solid fa-check"></i> Save Changes
      </button>
    </form>
  </div>
</div>

    </div>
  </main>

  <!-- Footer -->
  <footer class="custom-footer">
    <div class="footer-content">
      <div class="footer-column">
        <h3>Help Centre</h3>
        <p>Mon–Fri: 9.00 - 18.00</p>
        <p>Sat: 9.00 - 17.30</p>
        <p>Sun & Holidays: CLOSED</p>
        <div class="social-icons">
          <i class="fab fa-facebook-f"></i>
          <i class="fab fa-x-twitter"></i>
          <i class="fab fa-instagram"></i>
          <i class="fab fa-youtube"></i>
        </div>
      </div>
      <div class="footer-column">
        <a href="#">About us</a>
        <a href="#">Contact us</a>
        <a href="#">Newsroom</a>
      </div>
      <div class="footer-column trustpilot">
        <p>Rated <strong>4.4</strong>/5 from <strong>3,590</strong> reviews</p>
        <p style="font-size: 22px; color: #00B67A; font-weight: bold;">★ Trustcarverly</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Carverly Ltd. All rights reserved</p>
    </div>
  </footer>


  <script>
document.getElementById("back_button").addEventListener("click", function () {
  window.location.href = "../pages/userprofile.php"; 
});

</script>

</body>
</html>
