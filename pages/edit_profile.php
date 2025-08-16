<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Information</title>
  <link rel="stylesheet" href="../styles/styles/profile.css" />
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
        <div><i class="fa-solid fa-user"></i><span>Profile</span></div>
        <div><i class="fa-solid fa-tag"></i><span>best deals</span></div>
        <div><i class="fa-solid fa-magnifying-glass"></i><span>search cars</span></div>
        <div><i class="fa-solid fa-users"></i><span>community</span></div>
        <div><i class="fa-solid fa-unlock"></i><span>Logout</span></div>
      </div>
      </div>
   </header>    
<main>
    <button class="back-button" onclick="history.back()">Back</button>
<div class="login-container">
      <h2>PROFILE INFORMATION</h2>
      <form class="login-form">
        <label for="email">User Name</label>
        <input type="email" id="email" placeholder="Enter User Name " required>

        <label for="password">Email</label>
        <div class="password-wrapper">
          <input type="password" id="password" placeholder="Enter Email" required>
        </div>
        <label for="password">Contact</label>
        <div class="password-wrapper">
          <input type="password" id="password" placeholder="Enter Contact" required>
        </div>
        <label for="intent">Are you the owner or planning to buy? <span class="required"></span></label>
      <select id="intent" name="intent" required>
        <option value="">Select an option...</option>
        <option>Car Owner</option>
        <option>Interested Buyer</option>
      </select>

        <label for="password">Car Model</label>
       <select id="intent" name="intent" required>
        <option value="">Select the car model...</option>
        <option>Audi Q4 e-tron</option>
        <option>Hyundai Santa Fe</option>
        <option>Ford Kuga</option>
        <option>Kia Sportage</option>
        <option> MG 4 </option>
         <option>new Audi Q7</option>
          <option>Kia EV3</option>
           <option>Tesla Model 3</option>
            <option>BMW 3 Series</option>
             <option>Porsche Taycan GT3 </option>
              <option>BMW M5 F90 </option>
               <option>Jaecoo 7</option>
                <option>Lamborghini Huracan</option>
                 <option>Volkswagen</option>
                  <option>The spacious Kia Sportage</option>
                   <option>New BYD Atto 3</option>
                    <option>Audi A1 Sportback</option>
                     <option>Omoda 5</option>
                      <option>Mercedes-Benz</option>
                       <option>Volkswagen</option>
      </select>

        <label for="password">Ownership Type</label>
        <select id="intent" name="intent" required>
        <option value="">Select the Type...</option>
        <option> First-hand</option>
        <option> second-hand</option>
        </select>

         <label for="password">Registered City</label>
        <select id="intent" name="intent" required>
        <option value="">Select the City ...</option>
        <option>Dhaka</option>
        <option>Rajshahi</option>
        <option>khulna</option>
        <option>Shylet</option>
        </select>
      <br> 
        <button type="third-btn">Submit</button>
       </form>
</div> 
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
document.addEventListener("DOMContentLoaded", () => {
  const card = document.getElementById("profileCard");
  setTimeout(() => {
    card.classList.add("show");
  }, 200);
});
</script>

</body>
</html>

    