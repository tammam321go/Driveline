<?php include("../controllers/page_controller.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Car HOME Page</title>
  <link rel="stylesheet" href="../styles/styles/home.css" />
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <!-- Add this inside <head> -->
<link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>

</head>
<body>
  <header>
    <div class="navbar">
   <div class="brand-container">
  <img src=https://i.postimg.cc/63bx44WN/car-logo.jpg alt="Logo" />
  <div class="logo-text">carverly</div>

</div>
 <div class="nav-icons">
        
        
        <div id="ProfileBtn" style="cursor: pointer;">
  <i class="fa-solid fa-user"></i>
  <span>Profile</span>
</div>
<div id="SearchBtn" style="cursor: pointer;">
  <i class="fa-solid fa-magnifying-glass"></i>
  <span>Search Cars</span>
</div>
        
        <div id="CommunityBtn" style="cursor: pointer;">
          <i class="fa-solid fa-users"></i>
          <span>community</span>
          </div>

          <div><i class="fa-solid fa-tag"></i><span>best deals</span></div>
        
<div id="logoutBtn" style="cursor: pointer;">
  <i class="fa-solid fa-unlock"></i>
  <span>Logout</span>
</div>

      </div>
      </div>

</header>
<div class="breadcrumb">  <span> </span></div>

<h1 class="section-title"> </h1>

<?php 
require_once("../repositories/db_connect.php"); 

//Get Average Review for Thumbnail Car
$stmtAvg = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
$stmtAvg->bindParam(':car_id', $posterCarId, PDO::PARAM_INT);
$stmtAvg->execute();
$avgData = $stmtAvg->fetch(PDO::FETCH_ASSOC);
$stmtAvg->closeCursor();

$avgRating = $avgData['avg_rating'] ?? 0;
$totalReviews = $avgData['total_reviews'] ?? 0;

try {
    // Get the Thumbnail Car
    $stmt = $pdo->prepare("CALL Get_Thumbnail()");
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $carName = $row['c_name'];
        $imgUrl = $row['img_url'];
        $posterCarId = $row['c_id'];  
    } else {
        $carName = "Unknown Car";
        $imgUrl = "default.jpg"; 
        $posterCarId = 0;
    }

    $stmt->closeCursor();


    // Get average review for thumbnail car
    $stmtAvg = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
    $stmtAvg->bindParam(':car_id', $posterCarId, PDO::PARAM_INT);
    $stmtAvg->execute();
    $avgData = $stmtAvg->fetch(PDO::FETCH_ASSOC);
    $stmtAvg->closeCursor();

    $avgRating = $avgData['avg_rating'] ?? 0;
    $totalReviews = $avgData['total_reviews'] ?? 0;

    // Get Four Other Cars
    $stmt2 = $pdo->prepare("CALL Get_Four_Other_Cars(:excluded_id)");
    $stmt2->bindParam(':excluded_id', $posterCarId, PDO::PARAM_INT);
    $stmt2->execute();
    $otherCars = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $stmt2->closeCursor(); 

    //Get average reviews for each other car
    foreach ($otherCars as &$car) {
      $stmtAvgOther = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
      $stmtAvgOther->bindParam(':car_id', $car['c_id'], PDO::PARAM_INT);
      $stmtAvgOther->execute();
      $avgDataOther = $stmtAvgOther->fetch(PDO::FETCH_ASSOC);
      $stmtAvgOther->closeCursor();

      $car['avg_rating'] = $avgDataOther['avg_rating'] ?? 0;
      $car['total_reviews'] = $avgDataOther['total_reviews'] ?? 0;
  }
  unset($car); 

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>


<div>
    <a href="car.php?c_id=<?php echo urlencode($posterCarId); ?>" style="text-decoration: none; color: inherit;">
        <div class="car-banner" style="background-image: url('<?php echo htmlspecialchars($imgUrl); ?>'); cursor: pointer;">
            <div class="banner-text">
                <h2>
                    <?php echo htmlspecialchars($carName); ?><br>
                    <span>
                        ⭐ <?php echo $avgRating; ?> / 5 
                        (<?php echo $totalReviews; ?> reviews)
                    </span>
                </h2>
            </div>
        </div>
    </a>
</div>



<!-- 4 Other Cars Section -->
<section class="deals-section"> 
  <div class="card-container">
    <?php foreach ($otherCars as $car): ?>
      <a href="car.php?c_id=<?php echo urlencode($car['c_id']); ?>" style="text-decoration: none; color: inherit;">
        <div class="deal-card" style="cursor: pointer;">
          <img src="<?php echo htmlspecialchars($car['img_url']); ?>" alt="<?php echo htmlspecialchars($car['c_name']); ?>">
          <h3><?php echo htmlspecialchars($car['c_name']); ?></h3>
          <p class="tagline">
            ⭐ <?php echo $car['avg_rating']; ?> / 5 
            (<?php echo $car['total_reviews']; ?> reviews)
          </p>
          <div class="arrow"><i class="fa-solid fa-circle-chevron-right"></i></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>




<section class="used-car-list">
  <h2>Popular used car models </h2>
  <div class="car-columns">
    <ul>
      <li>Used Audi A1 Sportback</li>
      <li>Used Citroen C5 Aircross</li>
      <li>Used Fiat 500</li>
      <li>Used Ford Fiesta</li>
      <li>Used Hyundai i10</li>
      <li>Used Hyundai Ioniq 5</li>
      <li>Used Jaguar E-PACE</li>
    </ul>

    <ul>
      <li>Used Jaguar F-PACE</li>
      <li>Used Jaguar I-PACE</li>
      <li>Used Kia Ceed</li>
      <li>Used Kia Niro</li>
      <li>Used Kia Picanto</li>
      <li>Used Kia XCeed</li>
      <li>Used Land Rover Defender 110</li>
    </ul>

    <ul>
      <li>Used Mazda CX-5</li>
      <li>Used Mercedes-Benz A-Class</li>
      <li>Used Mercedes-Benz CLA</li>
      <li>Used Mercedes-Benz GLA</li>
      <li>Used MG MG4 EV</li>
      <li>Used MG ZS</li>
      <li>Used Peugeot 208</li>
    </ul>
    <ul>
      <li>Used Peugeot 3008</li>
      <li>Used Polestar 2</li>
      <li>Used Renault Clio</li>
      <li>Used SEAT Ateca</li>
      <li>Used SEAT Ibiza</li>
      <li>Used SEAT Leon</li>
      <li>Used Skoda Kodiaq</li>
    </ul>
    <ul>
        <li>Used Toyota Aygo X</li>
        <li>Used Toyota Yaris Cross</li>
        <li>Used Vauxhall Corsa</li>
        <li>Used Vauxhall Grandland X</li>
        <li>Used Vauxhall Mokka</li>
        <li>Used Volkswagen T-Cross</li>
        <li>Used Volkswagen Tiguan</li>
      </ul>
  </div>
</section>
<section class="faq-section">
        <h2>FAQ</h2>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">What is Carverly </div>
                <div class="faq-answer">Carverly is an online platform for buying a new car or selling your old one! We bring you great offers from thousands of trusted partners so you can buy or sell your car in just a few clicks. No haggling and no fees.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">How does Carverly work?</div>
                <div class="faq-answer">   It only takes a few minutes to get started. Whether you are buying or selling, you answer a few questions, we send your information out to local and national partners and they come back to you with great offers.</div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Does it cost me anything to use Carverly?</div>
                <div class="faq-answer">No, it’s completely free! Whether buying or selling, you won’t face any costs – just great offers!</div>
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
      <a href="#">Sitemap</a>S
    </div>
   <div class="footer-flags">
  <span><i class="fa-solid fa-flag"></i> BD </span>
  <span><i class="fa-solid fa-flag"></i> Germany </span>
  <span><i class="fa-solid fa-flag"></i> Spain </span>
</div>


  </div>
</footer>
<script>
document.getElementById("logoutBtn").addEventListener("click", function () {
  // Show confirmation dialog
  const confirmLogout = confirm("Are you sure you want to log out?");
  
  // If user clicks "OK"
  if (confirmLogout) {
    // Redirect to logout.php to destroy session and go to login.html
    window.location.href = "../controllers/logout.php"; 
  }
});
document.getElementById("ProfileBtn").addEventListener("click", function () {
  window.location.href = "../pages/userprofile.php";
});
document.getElementById("SearchBtn").addEventListener("click", function () {
  window.location.href = "../pages/search.php";
});
document.getElementById("CommunityBtn").addEventListener("click", function () {
  window.location.href = "../pages/community.php";
});


</script>

</body>
</html>
