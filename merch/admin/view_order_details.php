<?php
// FILE: admin/view_order_details.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in
if (!isset($_SESSION['logged-in'])) {
    header("Location: login.php?unauthorizedAccess_view_order");
    exit;
}

// Include database configuration
include_once "./includes/config.php";

$order_details = null;
$order_items = [];
$error_message = '';
$success_message = '';

// Get order_id from URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    $error_message = "Invalid or missing Order ID.";
} else {
    $order_id_to_view = (int)$_GET['order_id'];

    // --- Handle Status Update Submission ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        if (!isset($_POST['new_status']) || empty(trim($_POST['new_status']))) {
            $error_message = "Please select a new status.";
        } else {
            $new_status = trim(strip_tags($_POST['new_status']));
            // You might want to validate $new_status against a list of allowed statuses
            $allowed_statuses = ['Pending', 'Paid', 'Shipped', 'Delivered', 'Cancelled', 'Refunded']; // Example
            if (!in_array($new_status, $allowed_statuses)) {
                $error_message = "Invalid order status selected.";
            } else {
                $stmt_update_status = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
                if ($stmt_update_status) {
                    $stmt_update_status->bind_param("si", $new_status, $order_id_to_view);
                    if ($stmt_update_status->execute()) {
                        $success_message = "Order status updated successfully to '" . htmlspecialchars($new_status) . "'.";
                    } else {
                        $error_message = "Failed to update order status: " . $stmt_update_status->error;
                        error_log("Error updating order status for order_id $order_id_to_view: " . $stmt_update_status->error);
                    }
                    $stmt_update_status->close();
                } else {
                    $error_message = "Error preparing status update: " . $conn->error;
                    error_log("Error preparing status update statement for order_id $order_id_to_view: " . $conn->error);
                }
            }
        }
    }


    // --- Fetch Main Order Details ---
    // Fetches all columns from orders table. Adjust as needed.
    $sql_order = "SELECT * FROM orders WHERE order_id = ?";
    $stmt_order = $conn->prepare($sql_order);

    if ($stmt_order) {
        $stmt_order->bind_param("i", $order_id_to_view);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();

        if ($result_order->num_rows > 0) {
            $order_details = $result_order->fetch_assoc();

            // --- Fetch Order Items for this Order ---
            $sql_items = "SELECT oi.quantity, oi.price_at_purchase, p.product_id, p.product_title, p.product_img
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.product_id
                          WHERE oi.order_id = ?";
            $stmt_items = $conn->prepare($sql_items);
            if ($stmt_items) {
                $stmt_items->bind_param("i", $order_id_to_view);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();
                while ($item_row = $result_items->fetch_assoc()) {
                    $order_items[] = $item_row;
                }
                $stmt_items->close();
            } else {
                $error_message = "Error fetching order items: " . $conn->error;
                error_log("Error preparing order items statement for order_id $order_id_to_view: " . $conn->error);
            }
        } else {
            $error_message = "Order not found.";
        }
        $stmt_order->close();
    } else {
        $error_message = "Error fetching order details: " . $conn->error;
        error_log("Error preparing order details statement for order_id $order_id_to_view: " . $conn->error);
    }
}

// Get distinct order statuses for the update dropdown
$available_statuses = [];
$sql_statuses = "SELECT DISTINCT order_status FROM orders ORDER BY order_status ASC";
$result_statuses = $conn->query($sql_statuses);
if ($result_statuses && $result_statuses->num_rows > 0) {
    while($status_row = $result_statuses->fetch_assoc()) {
        $available_statuses[] = $status_row['order_status'];
    }
}

// $conn->close(); // Connection might be needed by footer
?>

<head>
    <style>
        .order-details-container {
            background-color: #fff;
            padding: 25px;
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
            margin-top: 20px;
        }
        .order-details-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .order-details-header h1 { margin-bottom: 5px; }
        .order-details-header p { margin-bottom: 3px; font-size: 0.9em; color: #6c757d; }

        .section-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .customer-details dl, .shipping-details dl { margin-bottom: 0; }
        .customer-details dt, .shipping-details dt { font-weight: 600; width: 150px; }
        .customer-details dd, .shipping-details dd { margin-left: 160px; }

        .order-items-table th, .order-items-table td { vertical-align: middle; }
        .order-items-table img {
            max-width: 50px;
            height: auto;
            border-radius: 3px;
        }
        .order-total-summary { font-size: 1.2em; font-weight: bold; }

        .status-update-form { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;}
        .status-update-form .form-select { max-width: 250px; display: inline-block; margin-right: 10px;}
    </style>
</head>

<div class="container mt-4 mb-5">
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <a href="manage_orders.php" class="btn btn-secondary mt-2">Back to Orders List</a>
    <?php elseif ($order_details): ?>
        <div class="order-details-container">
            <div class="order-details-header">
                <h1 class="h3">Order Details</h1>
                <p><strong>PayPal Order ID:</strong> <?php echo htmlspecialchars($order_details['paypal_order_id']); ?></p>
                <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order_details['order_date']))); ?></p>
                <p><strong>Current Status:</strong> <span class="badge badge-status-<?php echo htmlspecialchars(ucfirst(strtolower($order_details['order_status']))); ?>"><?php echo htmlspecialchars(ucfirst($order_details['order_status'])); ?></span></p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 customer-details">
                    <h2 class="section-title">Customer Information</h2>
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars(trim($order_details['customer_fname'] . ' ' . $order_details['customer_lname'])); ?></dd>
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($order_details['customer_email']); ?></dd>
                        <dt class="col-sm-4">Phone:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($order_details['customer_phone'] ?? 'N/A'); ?></dd>
                    </dl>
                </div>
                <div class="col-md-6 shipping-details">
                    <h2 class="section-title">Shipping Address</h2>
                    <dl class="row">
                        <dt class="col-sm-4">Address:</dt>
                        <dd class="col-sm-8">
                            <?php
                                echo htmlspecialchars($order_details['address_house'] ?? '') . ' ' . htmlspecialchars($order_details['address_street'] ?? '');
                            ?>
                        </dd>
                        <dt class="col-sm-4">City:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($order_details['address_city'] ?? ''); ?></dd>
                        <dt class="col-sm-4">Post Code:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($order_details['address_postcode'] ?? ''); ?></dd>
                        <dt class="col-sm-4">Country:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($order_details['address_country'] ?? ''); ?></dd>
                    </dl>
                </div>
            </div>

            <h2 class="section-title">Order Items</h2>
            <?php if (!empty($order_items)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered order-items-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Image</th>
                                <th scope="col">Product</th>
                                <th scope="col" class="text-center">Quantity</th>
                                <th scope="col" class="text-end">Price at Purchase</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $item_number = 1;
                            $calculated_total = 0;
                            foreach ($order_items as $item):
                                $item_subtotal = $item['price_at_purchase'] * $item['quantity'];
                                $calculated_total += $item_subtotal;
                            ?>
                            <tr>
                                <th scope="row"><?php echo $item_number++; ?></th>
                                <td>
                                    <img src="../admin/upload/<?php echo htmlspecialchars($item['product_img'] ?? 'placeholder.png'); ?>"
                                         alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                         onerror="this.src='https://placehold.co/50x50/cccccc/ffffff?text=ImgN/A';">
                                </td>
                                <td>
                                    <a href="../viewdetail.php?id=<?php echo $item['product_id']; ?>&category=product" target="_blank">
                                        <?php echo htmlspecialchars($item['product_title']); ?>
                                    </a>
                                    <small class="d-block text-muted">ID: <?php echo $item['product_id']; ?></small>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td class="text-end"><?php echo htmlspecialchars($order_details['currency'] . ' ' . number_format($item['price_at_purchase'], 2)); ?></td>
                                <td class="text-end"><?php echo htmlspecialchars($order_details['currency'] . ' ' . number_format($item_subtotal, 2)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end order-total-summary"><strong>Order Total:</strong></td>
                                <td class="text-end order-total-summary"><strong><?php echo htmlspecialchars($order_details['currency'] . ' ' . number_format($order_details['amount'], 2)); ?></strong></td>
                            </tr>
                            <?php if (abs($calculated_total - $order_details['amount']) > 0.01): // Check for discrepancy ?>
                            <tr>
                                <td colspan="5" class="text-end text-danger"><small>Calculated Item Total:</small></td>
                                <td class="text-end text-danger"><small><?php echo htmlspecialchars($order_details['currency'] . ' ' . number_format($calculated_total, 2)); ?></small></td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">No items found for this order. This might indicate an issue with data consistency.</p>
            <?php endif; ?>

            <div class="status-update-form">
                <h2 class="section-title">Update Order Status</h2>
                <form action="view_order_details.php?order_id=<?php echo $order_id_to_view; ?>" method="POST">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status:</label>
                        <select name="new_status" id="new_status" class="form-select form-select-sm" required>
                            <?php foreach ($available_statuses as $status_option): ?>
                                <option value="<?php echo htmlspecialchars($status_option); ?>" <?php if ($order_details['order_status'] == $status_option) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars(ucfirst($status_option)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-warning btn-sm">Update Status</button>
                    </div>
                </form>
            </div>

            <div class="mt-4">
                <a href="manage_orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders List</a>
                </div>
        </div> <?php endif; // End if $order_details ?>
</div> <?php
// Optional: Include a common admin footer if you have one
// include_once('./includes/footer.php');
?>
