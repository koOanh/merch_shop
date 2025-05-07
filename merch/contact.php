<?php include_once('./includes/headerNav.php'); ?>



<div class="overlay" data-overlay></div>
<!--
    - HEADER
  -->

<!-- get tables data from db -->

<header>
  <!-- desktop navigation -->
  <!-- inc/desktopnav.php -->
  <?php require_once './includes/desktopnav.php' ?>
  <!-- mobile nav in php -->
  <!-- inc/mobilenav.php -->
  <?php require_once './includes/mobilenav.php'; ?>

</header>

<!--
    - MAIN
  -->

<main>

  <div class="product-container">
    <div class="container">
      <!--
          - SIDEBAR
        -->
    

      <div class="product-box">
        <!-- get id and url for each category and display its dat from table her in this secton -->
        <div class="product-main">

          <!-- contact cards -->
          <!-- MAIL -->
          <div class="contact-card-container mail">
            <div class="contact-icon">
              <a href="#">
                <ion-icon class="contact-icons mail-icon" name="mail-outline"></ion-icon>
              </a>
            </div>
            <div class="contact-details">
              <contact-title>
                <h2>Mail</h2>
              </contact-title>
              <p>
                <a href="mailto:<?php echo($site_info_email) ?>"><?php echo($site_info_email) ?></a>
              </p>
            </div>
          </div>
          <!--  -->

          <!-- Whatsapp -->
          <div class="contact-card-container whatsapp">
            <div class="contact-icon">
              <a href="#">
                <ion-icon class="contact-icons whatsapp-icon" name="logo-whatsapp"></ion-icon>
              </a>
            </div>
            <div class="contact-details">
              <contact-title>
                <h2>Whatsapp</h2>
              </contact-title>
              <p>
                <a href="#"><?php echo($site_contact_num) ?></a>
              </p>
            </div>
          </div>
          <!--  -->

          <!-- Location -->
          <div class="contact-card-container location">
            <div class="contact-icon">
              <a href="#">
                <ion-icon class="contact-icons location-icon" name="location-outline"></ion-icon>
              </a>
            </div>
            <div class="contact-details">
              <contact-title>
                <h2>Location</h2>
              </contact-title>
              <p>
              <?php echo($site_address) ?>
              </p>
            </div>
          </div>
          <!--  -->


        </div>

            <!-- Map -->


      </div>
    </div>





  </div>

  <!--
      - TESTIMONIALS, CTA & SERVICE
    -->

  <!--
      - BLOG
    -->

<script src="./js/jquery.js" type="text/javascript"></script>
<script src="./js/bootstrap.min.js" type="text/javascript"></script>

</main>

<?php require_once './includes/footer.php'; ?>