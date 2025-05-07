<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="httsps://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" rel="stylesheet">
</head>

<?php
      $total_cart_items = 0;
     if(isset($_SESSION['mycart']))
     {
      $total_cart_items = count($_SESSION['mycart']);
     }
?>

 <div class="header-top" style="background-color: #FFE4E1;">
        <div class="container">
          <ul class="header-social-container">
            <li>
              <a href="https://www.facebook.com/sabrinacarpenter/" class="social-link">
                <ion-icon name="logo-facebook"></ion-icon>
              </a>
            </li>

            <li>
              <a href="https://x.com/sabrinaannlynn" class="social-link">
                <ion-icon name="logo-twitter"></ion-icon>
              </a>
            </li>

            <li>
              <a href="https://www.instagram.com/sabrinacarpenter/#" class="social-link">
                <ion-icon name="logo-instagram"></ion-icon>
              </a>
            </li>
          </ul>


	<div class="header-user-actions">

            <!-- Logout button -->
    <?php if( isset( $_SESSION['id'])) { ?>

            <button id="lg-btn" class="action-btn">
              <a   href="logout.php"  id="a" role="button" >
                <ion-icon name="log-out-outline"></ion-icon>
              </a>
            </button> 

            <!-- TODO: This script doesnot execute: Work o this, Directly logout user -->
		        <script src="./js/logout.js"></script>

	  <?php } else { ?>
            <!-- Login Button -->
            <button class="action-btn">
              <a href="./login.php"  id="a">
                <ion-icon name="person-outline"></ion-icon>
              </a>
            </button>

	  <?php } ?>

           <!-- Prfile Link Setup -->
      <!-- if logged in -->
      <?php if(isset($_SESSION['id'])) { ?>

        <li class="menu-category" style="opacity:1">
          <a href="profile.php?id=<?php echo (isset( $_SESSION['customer_name']))? $_SESSION['id']: 'unknown';?>" class="menu-title">
           <i class="fas fa-user"></i>
          </a>
        </li>

      <!-- if not logged in reduce opacity  -->
      <?php } else { ?>

        <li class="menu-category" style="opacity:0.5">
          <a style="cursor: not-allowed;" href="#?loginfirst" class="menu-title">
            <i class="fas fa-user"></i> Not signed
          </a>
        </li>

      <?php } ?> 

      <!-- Visit Admin Panel After Login -->
	 <?php  if(isset($_SESSION['logged-in'])){?>
        <li class="menu-category">
          <a href="admin/post.php" class="menu-title">
            <i class="fas fa-user"></i> Admin 
          </a>
        </li> 
	<?php } ?>

            <!-- Cart Button -->
	  <?php if(!(isset($_SESSION['logged-in']))){?>
            
            <button class="action-btn" ">
              <a href="./cart.php" >
                <ion-icon name="bag-handle-outline"></ion-icon>
              </a>
              <span class="count"> 
              <?php
                echo $total_cart_items ;
              ?>
              </span>
            </button>

    <?php } ?>

          </div>
        </div>
      </div>

      <div class="header-main" style="background-color:white;">
          <!-- logo section -->
          <a href="./index.php?id=<?php echo (isset( $_SESSION['customer_name']))? $_SESSION['id']: 'unknown';?>" class="header-logo" style="color: white;">

            <h1 style="text-align: center; color: black; font-weight: bold">
                sabrina capenter
            </h1>

          </a>
      </div>

      <!-- search input -->
      <div class="header-search-container" style="background-color: white;">
            <form class="search-form" method="post" action="./search.php">
              <input type="search" name="search" class="search-field" style="background-color: #FFE4E1;" placeholder="search..." required oninvalid="this.setCustomValidity('Enter product name...')" oninput="this.setCustomValidity('')" />

              <button class="search-btn" type="submit" name="submit">
                <ion-icon name="search-outline"></ion-icon>
              </button>
            </form>
          </div>


<!-- desktop navigation -->
<nav class="desktop-navigation-menu">
  <div class="container">
    <ul class="desktop-menu-category-list">

      <li class="menu-category">
        <a href="index.php?id=<?php echo (isset( $_SESSION['customer_name']))? $_SESSION['id']: 'unknown';?>" class="menu-title">
          Home
        </a>
      </li>

      <li class="menu-category">
        <a href="./category.php?category=<?php echo "women"; ?>" class="menu-title">Shop</a>
      </li>

      
      
      

    </ul>
  </div>
</nav>