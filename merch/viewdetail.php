<!--  -->
<?php include_once('./includes/headerNav.php'); ?>
<?php require_once './includes/mobilenav.php'; ?>

<head>
<style>
/* Styles for Review Section */
.reviews-section {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
.reviews-section h3 {
    margin-bottom: 20px;
    font-size: 1.4em;
    color: #333;
}
.review {
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    background-color: #fdfdfd;
}
.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #eee;
    flex-wrap: wrap; /* Allow wrapping */
}
.review-author {
    font-weight: 600;
    color: #444;
    margin-right: 10px; /* Space between author and stars */
}
.review-date {
    font-size: 0.85em;
    color: #777;
    margin-left: auto; /* Push date to the right */
    white-space: nowrap; /* Prevent date wrapping */
    padding-left: 10px;
}
.review-rating .star {
    color: #f5b301; /* Gold color for stars */
    font-size: 1.1em; /* Adjust star size */
}
 .review-rating .star-empty {
    color: #ccc; /* Color for empty stars */
 }
.review-comment {
    color: #555;
    line-height: 1.5;
    font-size: 0.95em;
    margin-top: 5px; /* Space below header */
    white-space: pre-wrap; /* Preserve line breaks in comments */
}
.no-reviews {
    color: #777;
    font-style: italic;
}
.average-rating-summary {
     margin-bottom: 15px;
     font-size: 1.1em;
     font-weight: bold;
}
 .average-rating-summary .star {
     color: #f5b301;
     margin-left: 5px;
 }
</style>
</head>


<!-- get tables data from db -->

<header>
  <!-- desktop navigation -->
  <!-- inc/desktopnav.php -->
  <?php require_once './includes/desktopnav.php' ?>
  <!-- mobile nav in php -->
  <!-- inc/mobilenav.php -->
  <?php require_once './includes/mobilenav.php'; ?>

</header>

<!-- check for table and then get specific data from table -->
<?php
// get values from url
$product_ID = $_GET['id'];
$product_category = $_GET['category'];

$product_name = '';
$product_price = '';



if($product_category == "deal_of_day"){
  $item = get_deal_of_day_by_id($product_ID);

} else{
  // get specfic item from table
  $item = get_product($product_ID);
}
// get user reviews
// $user_reviews = get_user_reviews();
?>



<div class="overlay" data-overlay></div>

<!-- CATEGORY SIDE BAR MOBILE MENU -->

<!-- Category side bar  -->
<div class="product-container category-side-bar-container">
  <div class="container">

    <?php require_once 'includes/categorysidebar.php' ?>


    <?php
$row = mysqli_fetch_assoc($item);

// --- START: Add this block ---
if (!$row) {
    // Handle case where item is not found
    echo "<div class='container'><p>Error: Item not found.</p></div>";
    // Optionally include footer and exit
    // require_once './includes/footer.php';
    exit;
}

// Determine which data fields to use based on the category
if ($product_category == "deal_of_day") {
    $display_title = $row['deal_title'];
    $display_img_filename = $row['deal_image'];
    // Check if deal images are in a different folder maybe? Adjust path if needed.
    // Assuming they are also in admin/upload/ for now.
    $display_img_path = 'admin/upload/' . $row['deal_image'];
    $display_price = $row['deal_net_price']; // The current price
    $display_original_price = $row['deal_discounted_price']; // The 'slashed' price
    $display_desc = $row['deal_description'] ?? 'No description available.'; // Use deal description
    $item_id_for_cart = $row['deal_id']; // Use deal_id
} else {
    $display_title = $row['product_title'];
    $display_img_filename = $row['product_img'];
    $display_img_path = 'admin/upload/' . $row['product_img'];
    $display_price = $row['discounted_price']; // Use discounted_price
    $display_original_price = $row['product_price']; // Use product_price
    $display_desc = $row['product_desc'] ?? 'No description available.'; // Use product description
    $item_id_for_cart = $row['product_id']; // Use product_id
}
// Store values needed for the add-to-cart form
$product_name = $display_title; // Consistent name for form
$product_price = $display_price; // Consistent price for form

// --- Fetch Approved Reviews ---
$reviews = []; // Initialize reviews array
$average_rating = 0;
$total_reviews = 0;

// Only fetch reviews if it's a regular product (not a deal_of_day)
// and we have a valid product ID stored in $item_id_for_cart
if ($product_category != "deal_of_day" && isset($item_id_for_cart) && is_numeric($item_id_for_cart)) {
    $current_product_id = (int)$item_id_for_cart; // Use the correct product ID

    $sql_reviews = "SELECT r.rating, r.comment, r.review_date, c.customer_fname
                    FROM reviews r
                    JOIN customer c ON r.customer_id = c.customer_id
                    WHERE r.product_id = ? AND r.status = 'approved'
                    ORDER BY r.review_date DESC";

    $stmt_reviews = $conn->prepare($sql_reviews);

    if ($stmt_reviews) {
        $stmt_reviews->bind_param("i", $current_product_id);
        $stmt_reviews->execute();
        $result_reviews = $stmt_reviews->get_result();
        $rating_sum = 0;
        while ($review_row = $result_reviews->fetch_assoc()) {
            $reviews[] = $review_row; // Add review to array
            $rating_sum += $review_row['rating'];
        }
        $total_reviews = count($reviews);
        if ($total_reviews > 0) {
            $average_rating = round($rating_sum / $total_reviews, 1);
        }
        $stmt_reviews->close();
    } else {
        error_log("Error preparing reviews statement in viewdetail.php: " . $conn->error);
    }
}

?> 


      <!-- product card   -->
      <div class="content">
      <form action="manage_cart.php" method="post" class='view-form'>
        <!-- product details container -->
        <div class="product_deatail_container">


          <!-- image is kept hidden for submission -->
          <input type="hidden" name = "product_img" value="<?php echo htmlspecialchars($display_img_filename); ?>">

          <!-- getting image from here with magnify functionality -->
        <div class="product_image_box">
  <div class="img-magnifier-container" style="width: 18rem;">
      <img class="pimage" id='image-pr' src="<?php echo htmlspecialchars($display_img_path); ?>" alt="<?php echo htmlspecialchars($display_title); ?>">
  </div>
</div>
<script> magnify("image-pr", 3); </script>

          <div class="product_detail_box">
            <h3 class="product-detail-title">
              <!-- convert to upper  -->
              <?php echo strtoupper(htmlspecialchars($display_title)); ?>
            </h3>
     
            <div class="prouduct_information">
            
              <div class="product_description">
                <div class="product_title"><strong>Name:</strong></div>
                <div class="product_detail">
                  <!-- convert to sentence case -->
                

                  <?php echo ucfirst(htmlspecialchars($product_name)); ?>
<input type="hidden" name='product_name' id='product_name' value = "<?php echo htmlspecialchars($product_name); ?>">

                </div>
              </div>

              <div class="product_description">
                <div class="product_title"><strong>Price:</strong></div>
                <div class="product_detail">
                  <div class="price-box">
                    <p class="price">$<?php echo htmlspecialchars($display_price); ?></p>
<input type="hidden" name="product_price" value = "<?php echo htmlspecialchars($display_price); ?>">
<input type="hidden" id="product_identity" name="product_id" value ="<?php echo htmlspecialchars($item_id_for_cart); ?>">
<input type="hidden" name="product_category" value ="<?php echo htmlspecialchars($product_category); ?>"> <del>$<?php echo htmlspecialchars($display_original_price); ?></del>
                  </div>
                </div>
              </div>
              <div class="product_description">
                <div class="product_detail">
                  
                </div>
              </div>
            </div>
         
            <div class="product_counter_box">


              <!-- 
              -
              -
              form send detail to cart page
              -
              -
             -->

                <!-- product counter buttons -->
                <div class="product_counter_btn_box">
                  <button type="button" class="btn_product_increment">+</button>

                  <input class="input_product_quantity" type="number" style="width: 50px" max="7" min="1" value="1" name="product_qty"  id="p_qty"/>

                  <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item_id_for_cart); ?>" />

                  <button type="button" class="btn_product_decrement">-</button>
                  
                </div>
                <!-- submit -->
                <div class="buy-and-cart-btn">

               
                
                <button type="submit" name="add_to_cart"   class="btn_product_cart" >
                  Add to Cart
                </button>

         
          



                </div>
          


              <!-- 
              form ends
             -->


            </div>
            
          </div>
        </div>

    

        <!-- reviews -->
        <!-- reviews -->
   

        </form>
        
<?php if ($product_category != "deal_of_day"): // Only show reviews for regular products ?>
<div class="reviews-section">
    <h3>Customer Reviews</h3>

    <?php if ($total_reviews > 0): ?>
        <div class="average-rating-summary">
            Average Rating: <?php echo $average_rating; ?> / 5
            <span class="star">&#9733;</span>
            (<?php echo $total_reviews; ?> review<?php echo ($total_reviews > 1) ? 's' : ''; ?>)
        </div>

        <?php foreach ($reviews as $review): ?>
            <div class="review">
                <div class="review-header">
                    <span class="review-author"><?php echo htmlspecialchars($review['customer_fname']); ?></span>
                    <span class="review-rating">
                        <?php
                        // Display stars based on rating
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<span class="star' . ($i > $review['rating'] ? '-empty' : '') . '">&#9733;</span>';
                        }
                        ?>
                    </span>
                     <span class="review-date"><?php echo htmlspecialchars(date("M d, Y", strtotime($review['review_date']))); ?></span>
                </div>
                <?php if (!empty($review['comment'])): ?>
                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); // nl2br converts newlines to <br> ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p class="no-reviews">Be the first to review this product!</p>
    <?php endif; ?>

</div>
<?php endif; // End check for product_category ?>
	
 
      
      </div>



<script>
  let btn_product_decrement = document.querySelector('.btn_product_decrement');
  let btn_product_increment = document.querySelector('.btn_product_increment');
  let change_qty = document.getElementById('p_qty');

  btn_product_decrement.addEventListener('click',function()
  {
    if( change_qty.value == 1)
    {
      change_qty.value = 1;
    }
    else{
      change_qty.value = (change_qty.value)-1 ;

    }
  });
  btn_product_increment.addEventListener('click',function()
  {
    change_qty.value = parseInt(change_qty.value)+1;
   

  });

</script>


</div>

<!--  -->

<!--  -->




<?php require_once './includes/footer.php'; ?>