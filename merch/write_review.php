<?php
// FILE: write_review.php

// Include the standard header (handles session_start(), db connection, etc.)
include_once('./includes/headerNav.php');

// --- Security Check: Ensure user is logged in ---
if (!isset($_SESSION['customer_email']) || !isset($_SESSION['id'])) {
    header("Location: login.php?error=login_required_review");
    exit;
}

// Get the logged-in user's ID
$customer_id = $_SESSION['id'];

// --- Get Product ID and Order ID from URL ---
if (!isset($_GET['product_id']) || !isset($_GET['order_id']) || !is_numeric($_GET['product_id']) || !is_numeric($_GET['order_id'])) {
    // Redirect or show error if IDs are missing or invalid
    echo "<div class='container'><p class='alert alert-danger'>Invalid request. Product or Order ID missing.</p></div>";
    include_once('./includes/footer.php'); // Show footer
    exit;
}

$product_id = (int)$_GET['product_id'];
$order_id = (int)$_GET['order_id'];

// --- Verify Purchase ---
// Check if this customer actually bought this product in this order
$sql_verify = "SELECT oi.order_item_id
               FROM order_items oi
               JOIN orders o ON oi.order_id = o.order_id
               WHERE oi.product_id = ?
                 AND oi.order_id = ?
                 AND o.customer_email = ?"; // Verify against email just in case session ID changes

$stmt_verify = $conn->prepare($sql_verify);
$purchase_verified = false;
$product_details = null;

if ($stmt_verify) {
    $stmt_verify->bind_param("iis", $product_id, $order_id, $_SESSION['customer_email']);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();
    if ($result_verify->num_rows > 0) {
        $purchase_verified = true;
    }
    $stmt_verify->close();
} else {
    error_log("Error preparing purchase verification statement: " . $conn->error);
    // Handle error - maybe show a generic message
}

// --- Fetch Product Details (only if purchase is verified) ---
if ($purchase_verified) {
    $sql_product = "SELECT product_title, product_img FROM products WHERE product_id = ?";
    $stmt_product = $conn->prepare($sql_product);
    if ($stmt_product) {
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        if ($result_product->num_rows > 0) {
            $product_details = $result_product->fetch_assoc();
        }
        $stmt_product->close();
    } else {
         error_log("Error preparing product details statement: " . $conn->error);
    }
}

// --- Check if already reviewed (optional but good) ---
// You might want to prevent submitting a new review if one exists for this order/product/user
$already_reviewed = false;
$sql_check_review = "SELECT review_id FROM reviews WHERE customer_id = ? AND product_id = ? AND order_id = ?";
$stmt_check_review = $conn->prepare($sql_check_review);
if ($stmt_check_review) {
    $stmt_check_review->bind_param("iii", $customer_id, $product_id, $order_id);
    $stmt_check_review->execute();
    $result_review_check = $stmt_check_review->get_result();
    if ($result_review_check->num_rows > 0) {
        $already_reviewed = true;
    }
    $stmt_check_review->close();
}


// Close connection only if no longer needed (footer might need it)
// $conn->close(); // Avoid closing if header/footer use $conn

?>

<div class="overlay" data-overlay></div>
<header>
    <?php require_once './includes/desktopnav.php'; ?>
    <?php require_once './includes/mobilenav.php'; ?>
    <style>
        .review-form-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .review-form-container h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }
        .product-info {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .product-info img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .product-info h4 {
            margin: 0;
            font-size: 1.1em;
            color: #444;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse; /* To select stars from right to left */
            justify-content: center; /* Center the stars */
            margin-bottom: 20px;
        }
        .star-rating input[type="radio"] {
            display: none; /* Hide the actual radio buttons */
        }
        .star-rating label {
            font-size: 2.5em; /* Size of the stars */
            color: #ddd; /* Color of unselected stars */
            cursor: pointer;
            transition: color 0.2s;
            padding: 0 5px; /* Spacing between stars */
        }
        /* Styling for selected stars and hover effect */
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #f5b301; /* Gold color for selected/hovered stars */
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .form-control { /* Style for textarea */
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-height: 120px; /* Good starting height */
            resize: vertical; /* Allow vertical resize */
            font-size: 1em;
        }
        .submit-button-container {
            text-align: center;
            margin-top: 25px;
        }
        .submit-button {
            padding: 10px 25px;
            font-size: 1.1em;
            color: white;
            background-color: #89375F; /* Deep maroon */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .submit-button:hover {
            background-color: #CE5959; /* Lighter maroon */
        }
        .alert { /* Basic alert styling */
             padding: 15px;
             margin-bottom: 20px;
             border: 1px solid transparent;
             border-radius: 4px;
             text-align: center;
        }
        .alert-danger {
             color: #a94442;
             background-color: #f2dede;
             border-color: #ebccd1;
        }
         .alert-warning {
             color: #8a6d3b;
             background-color: #fcf8e3;
             border-color: #faebcc;
         }
    </style>
</header>

<main>
    <div class="review-form-container">

        <?php if ($purchase_verified && $product_details && !$already_reviewed): ?>
            <h2>Write a Review</h2>
            <div class="product-info">
                <img src="./admin/upload/<?php echo htmlspecialchars($product_details['product_img'] ?? 'placeholder.png'); ?>"
                     alt="<?php echo htmlspecialchars($product_details['product_title']); ?>"
                     onerror="this.src='https://placehold.co/60x60/cccccc/ffffff?text=Img';">
                <h4><?php echo htmlspecialchars($product_details['product_title']); ?></h4>
            </div>

            <?php
            if (isset($_GET['status']) && $_GET['status'] == 'error') {
                echo '<div class="alert alert-danger">Could not submit review. Please try again.</div>';
            } elseif (isset($_GET['status']) && $_GET['status'] == 'invalid_rating') {
                 echo '<div class="alert alert-danger">Please select a star rating.</div>';
            }
            ?>

            <form action="handle_review.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                <label class="form-label">Your Rating:</label>
                <div class="star-rating">
                    <input type="radio" id="5-stars" name="rating" value="5" required /><label for="5-stars">&#9733;</label>
                    <input type="radio" id="4-stars" name="rating" value="4" /><label for="4-stars">&#9733;</label>
                    <input type="radio" id="3-stars" name="rating" value="3" /><label for="3-stars">&#9733;</label>
                    <input type="radio" id="2-stars" name="rating" value="2" /><label for="2-stars">&#9733;</label>
                    <input type="radio" id="1-star" name="rating" value="1" /><label for="1-star">&#9733;</label>
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">Your Review (Optional):</label>
                    <textarea class="form-control" id="comment" name="comment" rows="5" placeholder="Tell others what you thought..."></textarea>
                </div>

                <div class="submit-button-container">
                    <button type="submit" class="submit-button">Submit Review</button>
                </div>
            </form>

        <?php elseif ($already_reviewed): ?>
             <div class="alert alert-warning">You have already submitted a review for this product from this order.</div>
             <div style="text-align:center; margin-top: 15px;">
                 <a href="order_history.php" class="btn btn-secondary">Back to Order History</a>
             </div>
        <?php elseif (!$purchase_verified): ?>
            <div class="alert alert-danger">You can only review products you have purchased. Purchase verification failed for this item from the specified order.</div>
             <div style="text-align:center; margin-top: 15px;">
                 <a href="order_history.php" class="btn btn-secondary">Back to Order History</a>
             </div>
        <?php else: // Product details couldn't be fetched ?>
             <div class="alert alert-danger">Could not load product details for review. Please try again later.</div>
             <div style="text-align:center; margin-top: 15px;">
                 <a href="order_history.php" class="btn btn-secondary">Back to Order History</a>
             </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once './includes/footer.php'; ?>
