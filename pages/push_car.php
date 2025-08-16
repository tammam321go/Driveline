<?php
require_once("../repositories/db_connect.php"); 

$message = "";

if (isset($_POST['add_car_with_images'])) {
    // Insert car
    $stmt = $pdo->prepare("CALL InsertCar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['c_name'],
        $_POST['c_type'],
        $_POST['c_model'],
        $_POST['c_brand'],
        $_POST['c_engine'],
        $_POST['c_release_date'],
        $_POST['c_country'],
        $_POST['c_features'],
        $_POST['c_interesting_facts'],
        $_POST['c_description']
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($result && isset($result['new_car_id'])) {
        $car_id = $result['new_car_id'];

        // Insert all image URLs for this car
        if (!empty($_POST['img_url'])) {
            foreach ($_POST['img_url'] as $url) {
                if (trim($url) !== '') {
                    $stmt = $pdo->prepare("CALL InsertCarImage(?, ?)");
                    $stmt->execute([$car_id, $url]);
                    $stmt->closeCursor();
                }
            }
        }

        $message = "Car and images added successfully! Car ID: {$car_id}";
    } else {
        $message = "Error adding car.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add Car</title>
    <link rel="stylesheet" href="../styles/styles/home.css">
    <link rel="stylesheet" href="../styles/styles/push_car.css">
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

  <main class="container">
    <h1>Add a New Car with Images</h1>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="form-section">
        <h2>Car Details</h2>
        <input type="text" name="c_name" placeholder="Car Name" required>
        <input type="text" name="c_type" placeholder="Car Type" required>
        <input type="text" name="c_model" placeholder="Car Model" required>
        <input type="text" name="c_brand" placeholder="Car Brand" required>
        <textarea name="c_engine" placeholder="Engine Details" required></textarea>
        <input type="date" name="c_release_date" required>
        <input type="text" name="c_country" placeholder="Country" required>
        <textarea name="c_features" placeholder="Features" required></textarea>
        <textarea name="c_interesting_facts" placeholder="Interesting Facts"></textarea>
        <textarea name="c_description" placeholder="Description"></textarea>

        <h2>Image URLs</h2>
        <div id="image-fields">
            <div class="image-field">
                <input type="url" name="img_url[]" placeholder="Image URL" oninput="updatePreview(this)">
                <img class="preview" style="display:none;">
            </div>
        </div>
        <button type="button" id="addImageBtn" onclick="addImageField()">+ Add Another Image</button>

        <button type="submit" name="add_car_with_images">Add Car & Images</button>
    </form>
  </main>

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
    let imageCount = 1;
    function addImageField() {
        if (imageCount >= 5) return;

        const div = document.createElement('div');
        div.className = 'image-field';
        div.innerHTML = `
            <input type="url" name="img_url[]" placeholder="Image URL" oninput="updatePreview(this)">
            <img class="preview" style="display:none;">
        `;
        document.getElementById('image-fields').appendChild(div);
        imageCount++;

        if (imageCount >= 5) {
            document.getElementById('addImageBtn').style.display = 'none';
        }
    }

    function updatePreview(input) {
        const img = input.nextElementSibling;
        if (input.value.trim() !== '') {
            img.src = input.value;
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
            img.src = '';
        }
    }

    // Back button functionality
    document.getElementById("back_button").addEventListener("click", () => {
        window.location.href = "../pages/homepage.php";
    });
  </script>
</body>
</html>