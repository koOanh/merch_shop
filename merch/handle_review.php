<?php
// FILE: handle_review.php (Auto-Approve Version)

// Start session and include database config
session_start();
require_once 'includes/config.php'; // Use require_once to ensure it's included

// --- Security Check: Ensure user is logged in ---
if (!isset($_SESSION['id']) || !isset($_SESSION['customer_email'])) { // Check for both ID and email
    // If not logged in, redirect to login page
    header("Location: login.php?error=login_required_submit_review");
    exit;
}
$customer_id = $_SESSION['id'];
$customer_email_from_session = $_SESSION['customer_email']; // Get email from session

// --- Check if form was submitted via POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Validate incoming data ---
    if (!isset($_POST['product_id'], $_POST['order_id'], $_POST['rating']) ||
        !is_numeric($_POST['product_id']) ||
        !is_numeric($_POST['order_id']) ||
        !is_numeric($_POST['rating']))
    {
        // Essential data missing or invalid type
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=error_missing_data");
        exit;
    }

    $product_id = (int)$_POST['product_id'];
    $order_id = (int)$_POST['order_id'];
    $rating = (int)$_POST['rating'];
    // Sanitize comment: remove tags, trim whitespace. Allow basic punctuation.
    $comment = isset($_POST['comment']) ? trim(strip_tags($_POST['comment'])) : null;

    // Validate rating range
    if ($rating < 1 || $rating > 5) {
        header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=invalid_rating");
        exit;
    }

    // --- Verify Purchase Again (Server-Side Security) ---
    // Make sure this customer bought this product in this order using their email
    $sql_verify = "SELECT oi.order_item_id
                   FROM order_items oi
                   JOIN orders o ON oi.order_id = o.order_id
                   WHERE oi.product_id = ?
                     AND oi.order_id = ?
                     AND o.customer_email = ?"; // Use customer_email from orders table

    $stmt_verify = $conn->prepare($sql_verify);
    $purchase_verified = false;

    if ($stmt_verify) {
        $stmt_verify->bind_param("iis", $product_id, $order_id, $customer_email_from_session);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();
        if ($result_verify->num_rows > 0) {
            $purchase_verified = true;
        }
        $stmt_verify->close();
    } else {
        error_log("Error preparing purchase verification statement in handle_review.php: " . $conn->error);
        header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=error");
        exit;
    }

    if (!$purchase_verified) {
        // If purchase cannot be verified server-side, stop processing
        header("Location: order_history.php?error=purchase_not_verified"); // Redirect to order history
        exit;
    }

    // --- Check if Already Reviewed Again (Server-Side Security) ---
    $sql_check_review = "SELECT review_id FROM reviews WHERE customer_id = ? AND product_id = ? AND order_id = ?";
    $stmt_check_review = $conn->prepare($sql_check_review);
    $already_reviewed = false;
    if ($stmt_check_review) {
        $stmt_check_review->bind_param("iii", $customer_id, $product_id, $order_id);
        $stmt_check_review->execute();
        $result_review_check = $stmt_check_review->get_result();
        if ($result_review_check->num_rows > 0) {
            $already_reviewed = true;
        }
        $stmt_check_review->close();
    } else {
         error_log("Error preparing review check statement in handle_review.php: " . $conn->error);
         header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=error");
         exit;
    }

    if ($already_reviewed) {
        // If already reviewed, redirect back
        header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=already_reviewed");
        exit;
    }

    // --- Insert the Review into the Database ---
    // *** CHANGED status to 'approved' ***
    $status = 'approved'; // Review will be approved automatically

    $sql_insert = "INSERT INTO reviews (product_id, customer_id, order_id, rating, comment, status, review_date)
                   VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);

    if ($stmt_insert) {
        // Parameters: product_id (i), customer_id (i), order_id (i), rating (i), comment (s), status (s)
        $stmt_insert->bind_param("iiiiss", $product_id, $customer_id, $order_id, $rating, $comment, $status);

        if ($stmt_insert->execute()) {
            // Success! Redirect back to order history or product page with success message
            // *** CHANGED redirect message determination ***
            $redirect_message = "review_success"; // Always use success message now
            header("Location: order_history.php?status=$redirect_message");
            exit;
        } else {
            // Database insert failed
            error_log("Failed to insert review: " . $stmt_insert->error);
            header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=error");
            exit;
        }
        $stmt_insert->close();
    } else {
        // Statement preparation failed
        error_log("Error preparing review insert statement: " . $conn->error);
        header("Location: write_review.php?product_id=$product_id&order_id=$order_id&status=error");
        exit;
    }

    $conn->close();

} else {
    // Redirect if not a POST request
    header("Location: index.php"); // Redirect to homepage or login
    exit;
}
?>
