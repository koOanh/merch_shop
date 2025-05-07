<?php
// Get categories
$categories = get_categories();
$music=get_music_category();
$collections=get_collections_category();
$merchandise=get_merchandise_category();
?>


<!--
          - SIDEBAR
        -->

<div class="sidebar has-scrollbar" data-mobile-menu>
  <div class="sidebar-category">
    <div class="sidebar-top">
      <h2 class="sidebar-title">Category</h2>

      <button class="sidebar-close-btn" data-mobile-menu-close-btn>
        <ion-icon name="close-outline"></ion-icon>
      </button>
    </div>

    <ul class="sidebar-menu-category-list">
      <!-- get data from categories table -->
      <?php
      while ($row = mysqli_fetch_assoc($categories)) {
      ?>
      
        <li class="sidebar-menu-category">
          <button class="sidebar-accordion-menu" data-accordion-btn>
            <div class="menu-title-flex">

              <p class="menu-title"><?php echo $row['name'] ?></p>
            </div>

            <div>
              <ion-icon name="add-outline" class="add-icon"></ion-icon>
              <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
            </div>
          </button>

          <ul class="sidebar-submenu-category-list" data-accordion>
            <!-- get category data from table -->
            <!--  music category -->
            <?php
            if ($row['name'] == "Music" || $row['name'] == "music") {
              while ($clothrow = mysqli_fetch_assoc($music)) {

            ?>
                <!--  -->
                <li class="sidebar-submenu-category">
            <!-- updated it. set to form and will send data to search page -->
                  
                  <form class="search-form" method="post" action="./search.php">
                    <input type="hidden" name="search" value="<?php echo $clothrow['cloth_category_name'] ?>" />
                        <button type="submit" name="submit" class="sidebar-submenu-title">

                          <p class="product-name">
                            <?php echo $clothrow['cloth_category_name'] ?>
                          </p>

                        </button>
                  </form>    
                </li>




            <?php

              }
            }
            ?>
            <!-- merch  category --> 
            <?php
            if ($row['name'] == "Merchandise" || $row['name'] == "merchandise") {
              while ($footwearrow = mysqli_fetch_assoc($merchandise)) {


            ?>
                <!--  -->
                <li class="sidebar-submenu-category">
                  <form class="search-form" method="post" action="./search.php">
                    <input type="hidden" name="search" value="<?php echo $footwearrow['footwear_category_name'] ?>" />
                        <button type="submit" name="submit" class="sidebar-submenu-title">

                          <p class="product-name">
                            <?php echo $footwearrow['footwear_category_name'] ?>
                          </p>

                        </button>
                  </form>    
                </li>

            <?php

              }
            }
            ?>
            <!--  -->
            
<!-- collections category -->
            <?php
            if ($row['name'] == "Collections"|| $row['name'] == "collections") {
              while ($perfumesrow = mysqli_fetch_assoc($collections)) {


            ?>
                <!--  -->
                <li class="sidebar-submenu-category">
                  <form class="search-form" method="post" action="./search.php">
                    <input type="hidden" name="search" value="<?php echo $perfumesrow['perfume_category_name'] ?>" />
                        <button type="submit" name="submit" class="sidebar-submenu-title">

                          <p class="product-name">
                            <?php echo $perfumesrow['perfume_category_name'] ?>
                          </p>

                        </button>
                  </form>                  
                </li>

            <?php

              }
            }
            ?>
            <!--  -->
          </ul>
        </li>



      <?php
      }
      ?>
      <!--  -->






      <!--  -->
    </ul>
  </div>
</div>