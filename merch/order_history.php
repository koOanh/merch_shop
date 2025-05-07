<?php
// FILE: order_history.php (Updated for Reviews)

// Include the standard header (handles session_start(), db connection, etc.)
include_once('./includes/headerNav.php');

// --- Security Check: Ensure user is logged in ---
// Use the session variable that is reliably set upon successful login
if (!isset($_SESSION['customer_email']) || !isset($_SESSION['id'])) { // Also check for customer ID
    // Redirect to login page if not logged in
    header("Location: login.php?error=login_required");
    exit; // Stop script execution
}

// Get the logged-in user's details
$customer_email = $_SESSION['customer_email'];
$customer_id = $_SESSION['id']; // Get customer ID from session

// --- Fetch Order History from Database ---
// Fetch main order details
$sql_orders = "SELECT order_id, paypal_order_id, amount, currency, order_status, order_date
               FROM orders
               WHERE customer_email = ?
               ORDER BY order_date DESC"; // Order by most recent first

$stmt_orders = $conn->prepare($sql_orders);
$orders = []; // Initialize array to hold orders

if ($stmt_orders) {
    $stmt_orders->bind_param("s", $customer_email);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row; // Add each order to the array
    }
    $stmt_orders->close();
} else {
    // Handle error if statement preparation fails
    error_log("Error preparing orders statement in order_history.php: " . $conn->error);
    // $orders remains empty, which will trigger the "Could not retrieve" message later
}

// --- Prepare statements for fetching items and checking reviews (prepare ONCE) ---
$sql_items = "SELECT oi.product_id, oi.quantity, oi.price_at_purchase, p.product_title, p.product_img
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
if (!$stmt_items) {
     error_log("Error preparing items statement in order_history.php: " . $conn->error);
}


$sql_check_review = "SELECT review_id FROM reviews WHERE customer_id = ? AND product_id = ? AND order_id = ?";
$stmt_check_review = $conn->prepare($sql_check_review);
if (!$stmt_check_review) {
     error_log("Error preparing review check statement in order_history.php: " . $conn->error);
}

?>

<div class="overlay" data-overlay></div>
<header>
    <?php require_once './includes/desktopnav.php'; ?>
    <?php require_once './includes/mobilenav.php'; ?>
    <style>
        .order-history-container {
            max-width: 950px; /* Slightly wider */
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .order-history-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-bottom: 25px;
            background-color: #fdfdfd;
        }
        .order-header {
            background-color: #f5f5f5;
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            font-size: 0.9em;
            color: #555;
        }
         .order-header span {
             margin-right: 15px; /* Spacing between header items */
             margin-bottom: 5px; /* Spacing if wrapped */
         }
         .order-header strong {
             color: #333;
         }
        .order-items-section {
            padding: 15px;
        }
        .order-items-section h4 {
             margin-bottom: 15px;
             font-size: 1.1em;
             color: #444;
             border-bottom: 1px solid #eee;
             padding-bottom: 5px;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
            font-size: 0.95em;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .order-item-details {
            flex-grow: 1;
            display: flex; /* Use flexbox for details layout */
            justify-content: space-between; /* Space out elements */
            align-items: center; /* Vertically align items */
            flex-wrap: wrap; /* Allow wrapping */
        }
         .item-name-qty {
            flex-basis: 60%; /* Adjust basis as needed */
            margin-right: 10px; /* Space before price/review button */
             margin-bottom: 5px; /* Space if wrapped */
         }
        .item-price-review {
             flex-basis: 35%; /* Adjust basis */
             text-align: right;
             margin-bottom: 5px; /* Space if wrapped */
        }
        .item-price {
            color: #666;
            margin-right: 15px; /* Space between price and button */
        }
        .review-link {
            display: inline-block;
            padding: 5px 10px;
            font-size: 0.85em;
            color: white !important; /* Ensure text is white */
            background-color: #89375F; /* Deep maroon */
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s ease;
            border: none; /* Remove default link border */
            cursor: pointer;
        }
        .review-link:hover {
            background-color: #CE5959; /* Lighter maroon on hover */
            color: white !important;
            text-decoration: none;
        }
        .review-link.disabled {
             background-color: #cccccc;
             color: #666666 !important;
             cursor: not-allowed;
             pointer-events: none; /* Disable clicks */
        }
        .no-orders {
            text-align: center;
            margin-top: 20px;
            color: #555;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px dashed #ddd;
            border-radius: 5px;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 600px) {
            .order-header {
                 font-size: 0.85em;
            }
            .order-item-details {
                 flex-direction: column; /* Stack details vertically */
                 align-items: flex-start; /* Align items to the start */
            }
            .item-name-qty, .item-price-review {
                 flex-basis: 100%; /* Full width */
                 text-align: left; /* Align text left */
                 margin-right: 0;
            }
             .item-price-review {
                 margin-top: 5px;
             }
             .item-price {
                 display: block; /* Put price on its own line */
                 margin-bottom: 5px;
                 margin-right: 0;
             }
        }

    </style>
</header>

<main>
    <div class="order-history-container">
<?php
// Display status messages from review submission
if (isset($_GET['status'])) {
    $status_message = '';
    $alert_type = 'alert-info'; // Default type

    if ($_GET['status'] == 'review_pending') {
        $status_message = 'Your review has been submitted and is awaiting approval.';
        $alert_type = 'alert-success';
    } elseif ($_GET['status'] == 'review_success') {
        $status_message = 'Thank you for your review!';
        $alert_type = 'alert-success';
    } elseif ($_GET['status'] == 'purchase_not_verified') {
        $status_message = 'Could not verify purchase for review submission.';
        $alert_type = 'alert-danger';
    }
    // Add more elseif blocks here for other statuses if needed

    if (!empty($status_message)) {
        // Basic alert styling - ensure you have corresponding CSS if needed
        echo '<div class="alert ' . $alert_type . '" role="alert" style="padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; text-align: center;">';
        echo htmlspecialchars($status_message);
        echo '</div>';
    }
}
?>

<h2>Your Order History</h2>

        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span>Order Placed: <strong><?php echo htmlspecialchars(date("M d, Y", strtotime($order['order_date']))); ?></strong></span>
                        <span>Total: <strong><?php echo htmlspecialchars($order['currency'] . ' ' . number_format($order['amount'], 2)); ?></strong></span>
                        <span>Order #: <strong><?php echo htmlspecialchars($order['paypal_order_id']); ?></strong></span>
                        <span>Status: <strong><?php echo htmlspecialchars($order['order_status']); ?></strong></span>
                    </div>

                    <div class="order-items-section">
                        <h4>Items in this Order</h4>
                        <?php
                        // Fetch items for this specific order
                        $order_items = [];
                        if ($stmt_items) {
                            $stmt_items->bind_param("i", $order['order_id']);
                            $stmt_items->execute();
                            $result_items = $stmt_items->get_result();
                            while ($item_row = $result_items->fetch_assoc()) {
                                $order_items[] = $item_row;
                            }
                            // $result_items->free(); // Free result set for items
                        } else {
                             echo "<p style='color: red;'>Error fetching items.</p>";
                        }

                        if (!empty($order_items)) {
                            foreach ($order_items as $item) {
                                // Check if a review already exists for this customer, product, and order
                                $has_reviewed = false;
                                if ($stmt_check_review) {
                                    $stmt_check_review->bind_param("iii", $customer_id, $item['product_id'], $order['order_id']);
                                    $stmt_check_review->execute();
                                    $result_review_check = $stmt_check_review->get_result();
                                    if ($result_review_check->num_rows > 0) {
                                        $has_reviewed = true;
                                    }
                                    // $result_review_check->free(); // Free result set for review check
                                }
                        ?>
                                <div class="order-item">
                                    <img src="./admin/upload/<?php echo htmlspecialchars($item['product_img'] ?? 'placeholder.png'); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                         onerror="this.src='https://placehold.co/50x50/cccccc/ffffff?text=Img';"> <div class="order-item-details">
                                        <div class="item-name-qty">
                                            <?php echo htmlspecialchars($item['product_title']); ?>
                                            (Qty: <?php echo htmlspecialchars($item['quantity']); ?>)
                                        </div>
                                        <div class="item-price-review">
                                            <span class="item-price">
                                                $<?php echo number_format($item['price_at_purchase'], 2); ?> each
                                            </span>
                                            <?php if ($has_reviewed): ?>
                                                <span class="review-link disabled">Reviewed</span> <?php else: ?>
                                                <a href="write_review.php?product_id=<?php echo $item['product_id']; ?>&order_id=<?php echo $order['order_id']; ?>" class="review-link">
                                                    Write Review
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // end foreach $order_items
                        } else {
                            echo "<p>No item details found for this order.</p>";
                        }
                        ?>
                    </div> </div> <?php endforeach; // end foreach $orders ?>

        <?php elseif (is_array($orders)): // Check if $orders is an array but empty ?>
            <p class="no-orders">You have not placed any orders yet.</p>
        <?php else: // Handle case where the initial orders query failed ?>
            <p class="no-orders" style="color: red;">Could not retrieve order history. Please try again later.</p>
        <?php endif; ?>

        <?php
            // Close the prepared statements if they were created
            if ($stmt_items) {
                $stmt_items->close();
            }
            if ($stmt_check_review) {
                $stmt_check_review->close();
            }
            // Connection will likely be closed by footer or automatically at script end
            // $conn->close();
        ?>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>
