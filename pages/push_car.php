<?php
require_once("../repositories/db_connect.php"); 
include("../controllers/page_controller.php");

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
    <style>
        input.invalid, textarea.invalid {
            border: 2px solid red;
            background-color: #ffecec;
        }
        small.error {
            color: red;
            font-size: 0.85em;
        }
        .preview-wrapper {
            position: relative;
            display: inline-block;
            margin-top: 5px;
        }
        .preview {
            max-width: 200px;
            max-height: 120px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .preview-error {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            font-weight: bold;
            font-size: 0.9em;
            text-align: center;
            line-height: 120px;
            border-radius: 8px;
        }
    </style>
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

    <form method="POST" class="form-section" id="carForm" novalidate>
        <h2>Car Details</h2>
        <div class="field">
            <input type="text" name="c_name" placeholder="Car Name" required minlength="2" maxlength="50">
            <small class="error"></small>
        </div>
        <div class="field">
            <input type="text" name="c_type" placeholder="Car Type" required minlength="2" maxlength="50">
            <small class="error"></small>
        </div>
        <div class="field">
            <input type="text" name="c_model" placeholder="Car Model" required>
            <small class="error"></small>
        </div>
        <div class="field">
            <input type="text" name="c_brand" placeholder="Car Brand" required>
            <small class="error"></small>
        </div>
        <div class="field">
            <textarea name="c_engine" placeholder="Engine Details" required></textarea>
            <small class="error"></small>
        </div>
        <div class="field">
            <input type="date" name="c_release_date" required>
            <small class="error"></small>
        </div>
        <div class="field">
            <input type="text" name="c_country" placeholder="Country" required>
            <small class="error"></small>
        </div>
        <div class="field">
            <textarea name="c_features" placeholder="Features" required></textarea>
            <small class="error"></small>
        </div>
        <div class="field">
            <textarea name="c_interesting_facts" placeholder="Interesting Facts"></textarea>
            <small class="error"></small>
        </div>
        <div class="field">
            <textarea name="c_description" placeholder="Description"></textarea>
            <small class="error"></small>
        </div>

        <h2>Image URLs</h2>
        <div id="image-fields">
            <div class="image-field">
                <input type="url" name="img_url[]" placeholder="Image URL" oninput="updatePreview(this)">
                <div class="preview-wrapper">
                    <img class="preview" style="display:none;">
                    <div class="preview-error">❌ Image failed to load</div>
                </div>
                <small class="error"></small>
            </div>
        </div>
        <button type="button" id="addImageBtn" onclick="addImageField()">+ Add Another Image</button>

        <p id="form-error-message" style="color:red;display:none;"></p>
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
            <div class="preview-wrapper">
                <img class="preview" style="display:none;">
                <div class="preview-error">❌ Image failed to load</div>
            </div>
            <small class="error"></small>
        `;
        document.getElementById('image-fields').appendChild(div);
        imageCount++;

        if (imageCount >= 5) {
            document.getElementById('addImageBtn').style.display = 'none';
        }
    }

    function updatePreview(input) {
        const wrapper = input.parentElement.querySelector(".preview-wrapper");
        const img = wrapper.querySelector(".preview");
        const errorOverlay = wrapper.querySelector(".preview-error");

        if (input.value.trim() !== "") {
            img.src = input.value;
            img.style.display = "block";
            errorOverlay.style.display = "none";

            img.onload = () => {
                errorOverlay.style.display = "none";
            };
            img.onerror = () => {
                errorOverlay.style.display = "block";
            };
        } else {
            img.style.display = "none";
            errorOverlay.style.display = "none";
            img.src = "";
        }
    }

    // Validation helpers
    function showError(input, message) {
        const errorEl = input.parentElement.querySelector(".error");
        if (message) {
            errorEl.textContent = message;
            input.classList.add("invalid");
        } else {
            errorEl.textContent = "";
            input.classList.remove("invalid");
        }
    }

    function validateField(input) {
        const value = input.value.trim();
        let message = "";

        switch (input.name) {
            case "c_name":
            case "c_type":
                if (value.length < 2 || value.length > 50) {
                    message = `${input.placeholder} must be 2–50 characters.`;
                }
                break;
            case "c_model":
            case "c_brand":
            case "c_country":
                if (!value) message = `${input.placeholder} is required.`;
                break;
            case "c_engine":
            case "c_features":
                if (!value) message = `${input.placeholder} is required.`;
                break;
            case "c_release_date":
                if (!value) {
                    message = "Release date is required.";
                } else {
                    const releaseDate = new Date(value);
                    const today = new Date();
                    if (releaseDate > today) {
                        message = "Release date cannot be in the future.";
                    }
                }
                break;
            case "img_url[]":
                if (value) {
                    try {
                        new URL(value);
                    } catch {
                        message = "Invalid image URL.";
                    }
                }
                break;
        }
        showError(input, message);
        return message === "";
    }

    // Real-time validation
    document.querySelectorAll("#carForm input, #carForm textarea").forEach(input => {
        input.addEventListener("input", () => validateField(input));
        input.addEventListener("blur", () => validateField(input));
    });

    // Final check before submit
    document.getElementById("carForm").addEventListener("submit", function(e) {
        let valid = true;
        this.querySelectorAll("input, textarea").forEach(input => {
            if (!validateField(input)) valid = false;
        });
        if (!valid) {
            e.preventDefault();
            const errorDiv = document.getElementById("form-error-message");
            errorDiv.textContent = "Please fix the highlighted errors before submitting.";
            errorDiv.style.display = "block";
        }
    });

    // Back button functionality
    document.getElementById("back_button").addEventListener("click", () => {
        window.location.href = "../pages/homepage.php";
    });
  </script>
</body>
</html>
