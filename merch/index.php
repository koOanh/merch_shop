<?php
  include_once('./includes/headerNav.php');

  // Get all banner products
  $banner_products = get_banner_details();

// Get top rated products
$top_rated_products1 = get_top_rated_products();
$top_rated_products2 = get_top_rated_products();
?>

<div class="overlay" data-overlay></div>

<header>
  <?php require_once './includes/desktopnav.php' ?>
  <?php require_once './includes/mobilenav.php'; ?>

</header>

<main>
  <!--
      - BANNER: Coursal
    -->

  <div class="banner">
    <div class="container">
      <div class="slider-container has-scrollbar">
        <!-- Display data from db in banner -->
        <?php
        while ($row = mysqli_fetch_assoc($banner_products)) {
        ?>

          <div class="slider-item">
            <img src="images/carousel/<?php
                                      echo $row['banner_image'];
                                      ?>" alt="women's latest fashion sale" class="banner-img" />

            <div class="banner-content">
              <p class="banner-subtitle">
                <?php
                echo $row['banner_subtitle'];
                ?>
              </p>

              <h2 class="banner-title">
                <?php
                echo $row['banner_title'];
                ?>
              </h2>

              <p class="banner-text">starting at &dollar;
                <b><?php echo $row['banner_items_price']; ?></b>.00
              </p>

              <a href="#" class="banner-btn">Shop now</a>
            </div>
          </div>

        <?php
        }
        ?>
        <!--  -->
      </div>
    </div>
  </div>

<?php require_once './includes/dealoftheday.php' ?>


        <!--
            - PRODUCT GRID
          -->

        <div class="product-main">
          <h2 class="title">New Products</h2>

          <div class="product-grid">

            <!-- display data from table new products -->

            <?php
//this will dynamically fetch data from a database and show all the post from mysql
//and this will auto create product div as per no of post available in database
/* define how much data to show in a page from database*/
$limit = 8;
if(isset($_GET['page'])){
  $page = $_GET['page'];
}else{
  $page = 1;
}
//define from which row to start extracting data from database
$offset = ($page - 1) * $limit;

$product_left = array();


            $new_product_counter = 1;

            $new_products_result = get_new_products($offset, $limit);
    if($new_products_result->num_rows > 0){

            while ($row = mysqli_fetch_assoc($new_products_result)) {

            ?>


              <div class="showcase">
                <div class="showcase-banner">
                  <img src="./admin/upload/<?php
                                                      echo $row['product_img']
                                                      ?>" alt="Mens Winter Leathers Jackets" width="300" class="product-img default" />
                  <img src="./admin/upload/<?php
                                                      echo $row['product_img']
                                                      ?>" alt="Mens Winter Leathers Jackets" width="300" class="product-img hover" />
                  <!-- Applying coditions on dicount and sale tags  -->
                  <!--  -->
                  <?php>

	</div>
             

                <div class="showcase-content">
                  <a href="./viewdetail.php?id=<?php
                                                echo $row['product_id']
                                                ?>&category=<?php
                                                            echo $row['category_id']
                                                            ?>" class="showcase-category">
                    <?php echo $row['product_title'] ?>
                  </a>

                  <a href="./viewdetail.php?id=<?php
                                                echo $row['product_id']
                                                ?>&category=<?php
                                                            echo $row['category_id']
                                                            ?>">
                    <h3 class="showcase-title">
                      <?php echo $row['product_desc'] ?>
                    </h3>
                  </a>

                  <div class="price-box">
                    <p class="price">
                      $<?php echo $row['discounted_price'] ?>
                    </p>
                    <del>
                      $<?php echo $row['product_price'] ?>
                    </del>
                  </div>
                </div>
              </div>

            <?php
              $new_product_counter = $new_product_counter + 1;
            }
    }else { 
      echo "No Results Found"; }
             $conn->close(); 

            ?>
            <!--  -->
          </div>
        </div>
        <!-- pagination start -->
        <!--Pagination-->
<?php
    include "includes/config.php"; 
    // Pagination btn using php with active effects 

    $sql1 = "SELECT * FROM products";
    $result1 = mysqli_query($conn, $sql1) or die("Query Failed.");

    if(mysqli_num_rows($result1) > 0){
        $total_products = mysqli_num_rows($result1);
        $total_page = ceil($total_products / $limit);

?>
    <nav class="main-pagination" style="margin-left: 10px;">
      <ul class="pagination-ul">


        <?php 
            for($i=1; $i<=$total_page; $i++){
                //important this is for active effects that denote in which page you are in current position
                if ($page==$i) {
                    $active = "page-active";
                } else {
                    $active = "";
                }
        ?>
        <li class="page-item-number <?php echo $active; // page number ?>">
            <a class="page-number-link " href="index.php?page=<?php echo $i; // page number ?>">
            <?php echo $i; // page number ?>
            </a>
        </li>
        <?php }} ?>

      </ul>
    </nav>
  <!-- pagination end -->
      </div>
    </div>
  </div>
</main>

<?php require_once './includes/footer.php'; ?>