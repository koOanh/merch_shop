<?php
// FILE: admin/manage_orders.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in
if (!isset($_SESSION['logged-in'])) {
    header("Location: login.php?unauthorizedAccess_manage_orders");
    exit;
}

// Include database configuration
include_once "./includes/config.php";

// --- Pagination Configuration ---
$results_per_page = 15; // Number of orders to display per page

// Determine current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}
if ($current_page < 1) {
    $current_page = 1;
}
$start_from = ($current_page - 1) * $results_per_page;

// --- Filtering Logic ---
$filter_status = $_GET['status'] ?? ''; // Get status from URL, default to empty
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$search_term = $_GET['search'] ?? '';


$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($filter_status)) {
    $where_clauses[] = "o.order_status = ?";
    $params[] = $filter_status;
    $param_types .= 's';
}
if (!empty($filter_date_from)) {
    $where_clauses[] = "DATE(o.order_date) >= ?";
    $params[] = $filter_date_from;
    $param_types .= 's';
}
if (!empty($filter_date_to)) {
    $where_clauses[] = "DATE(o.order_date) <= ?";
    $params[] = $filter_date_to;
    $param_types .= 's';
}

if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_clauses[] = "(o.paypal_order_id LIKE ? OR o.customer_email LIKE ? OR o.customer_fname LIKE ? OR o.customer_lname LIKE ?)";
    for ($i = 0; $i < 4; $i++) { // Add search term for each LIKE clause
        $params[] = $search_like;
        $param_types .= 's';
    }
}


$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = " WHERE " . implode(" AND ", $where_clauses);
}

// --- Fetch Orders ---
// Base SQL query
$sql_orders_base = "FROM orders o "; // Customer name is already in orders table

// SQL for fetching orders for the current page
$sql_orders = "SELECT o.order_id, o.paypal_order_id, o.customer_fname, o.customer_lname, o.customer_email, o.order_date, o.amount, o.currency, o.order_status "
            . $sql_orders_base
            . $sql_where
            . " ORDER BY o.order_date DESC LIMIT ?, ?";

$stmt_orders = $conn->prepare($sql_orders);

if ($stmt_orders) {
    // Add limit and offset to params for the main query
    $current_params = $params; // Create a copy to not affect count query
    $current_params[] = $start_from;
    $current_params[] = $results_per_page;
    $current_param_types = $param_types . 'ii';

    if (!empty($current_param_types)) { // Check if there are any params before binding
         $stmt_orders->bind_param($current_param_types, ...$current_params);
    }
    // If no params (e.g. no filters), we still need to bind limit and offset
    // This case is handled by the structure above, but if $param_types was empty,
    // we'd need: $stmt_orders->bind_param("ii", $start_from, $results_per_page);

    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
} else {
    error_log("Error preparing orders statement in manage_orders.php: " . $conn->error);
    $result_orders = false;
}

// --- Get Total Number of Orders (for pagination, considering filters) ---
$sql_total = "SELECT COUNT(o.order_id) as total " . $sql_orders_base . $sql_where;
$stmt_total = $conn->prepare($sql_total);
$total_orders = 0;

if ($stmt_total) {
    if (!empty($param_types)) { // Bind params if filters are applied
        $stmt_total->bind_param($param_types, ...$params);
    }
    $stmt_total->execute();
    $result_total_orders = $stmt_total->get_result();
    $row_total = $result_total_orders->fetch_assoc();
    $total_orders = $row_total['total'] ?? 0;
    $stmt_total->close();
} else {
    error_log("Error preparing total orders statement in manage_orders.php: " . $conn->error);
}

$total_pages = ceil($total_orders / $results_per_page);

// --- Get distinct order statuses for the filter dropdown ---
$order_statuses = [];
$sql_statuses = "SELECT DISTINCT order_status FROM orders ORDER BY order_status ASC";
$result_statuses = $conn->query($sql_statuses);
if ($result_statuses && $result_statuses->num_rows > 0) {
    while($status_row = $result_statuses->fetch_assoc()) {
        $order_statuses[] = $status_row['order_status'];
    }
}

?>

<head>
    <style>
        .table-actions a {
            margin-right: 5px;
        }
        .filter-form .form-control, .filter-form .form-select {
            margin-right: 10px;
            margin-bottom: 10px; /* For stacking on small screens */
        }
        .filter-form .btn {
            margin-bottom: 10px; /* For stacking on small screens */
        }
        .badge-status-Paid { background-color: #28a745; color: white; }
        .badge-status-Pending { background-color: #ffc107; color: #212529; }
        .badge-status-Shipped { background-color: #17a2b8; color: white; }
        .badge-status-Delivered { background-color: #007bff; color: white; }
        .badge-status-Cancelled { background-color: #dc3545; color: white; }
        .badge-status-Refunded { background-color: #6c757d; color: white; }
        .badge {
            padding: 0.35em 0.65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
    </style>
</head>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Manage Orders</h1>
        </div>

    <form action="manage_orders.php" method="GET" class="filter-form mb-4 p-3 border rounded bg-light">
        <div class="row g-2 align-items-end">
            <div class="col-md">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="Order ID, Email, Name..." value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="col-md">
                <label for="status" class="form-label">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($order_statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php if ($filter_status == $status) echo 'selected'; ?>>
                            <?php echo htmlspecialchars(ucfirst($status)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
            </div>
            <div class="col-md">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="manage_orders.php" class="btn btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    <?php if ($result_orders && $result_orders->num_rows > 0): ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Order ID (PayPal)</th>
                        <th scope="col">Customer Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Order Date</th>
                        <th scope="col" class="text-end">Amount</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_number = $start_from + 1;
                    while ($order = $result_orders->fetch_assoc()): 
                        $customer_full_name = htmlspecialchars(trim($order['customer_fname'] . ' ' . $order['customer_lname']));
                        if (empty(trim($customer_full_name))) {
                            $customer_full_name = 'N/A';
                        }
                    ?>
                    <tr>
                        <th scope="row"><?php echo $row_number++; ?></th>
                        <td><?php echo htmlspecialchars($order['paypal_order_id']); ?></td>
                        <td><?php echo $customer_full_name; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars(date("M d, Y, g:i A", strtotime($order['order_date']))); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars($order['currency'] . ' ' . number_format($order['amount'], 2)); ?></td>
                        <td class="text-center">
                            <span class="badge badge-status-<?php echo htmlspecialchars(ucfirst(strtolower($order['order_status']))); ?>">
                                <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                            </span>
                        </td>
                        <td class="text-center table-actions">
                            <a href="view_order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="update_order_status.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-warning" title="Update Status">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Orders navigation" class="mt-4">
            <ul class="pagination justify-content-center pagination-sm">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo urlencode($filter_status); ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&search=<?php echo urlencode($search_term); ?>">Previous</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <li class="page-item active" aria-current="page"><span class="page-link"><?php echo $i; ?></span></li>
                    <?php else: ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filter_status); ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&search=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo urlencode($filter_status); ?>&date_from=<?php echo urlencode($filter_date_from); ?>&date_to=<?php echo urlencode($filter_date_to); ?>&search=<?php echo urlencode($search_term); ?>">Next</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php elseif ($result_orders): // Check if $result_orders is valid but has no rows ?>
        <div class="alert alert-info mt-3">No orders found matching your criteria.</div>
    <?php else: // Handle case where the database query failed ?>
        <div class="alert alert-danger mt-3">Could not retrieve orders. Please try again later or check error logs.</div>
    <?php endif; ?>

    <?php
        // Close the prepared statement if it was created
        if ($stmt_orders) {
            $stmt_orders->close();
        }
        // Connection will be closed by footer or automatically at script end
        // $conn->close();
    ?>
</div>

<?php
// Optional: Include a common admin footer if you have one
// include_once('./includes/footer.php');
?>
