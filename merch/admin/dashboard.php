<?php
// FILE: admin/dashboard.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in (restriction.php should handle redirection if not)
if (!isset($_SESSION['logged-in'])) {
    // This check is a fallback, restriction.php should ideally handle it
    header("Location: login.php?unauthorizedAccess_dashboard");
    exit;
}

// Include database configuration
include_once "./includes/config.php"; // $conn should be available from here

// --- Initialize variables for dashboard data ---
$totalSalesToday = 0;
$totalSalesThisWeek = 0;
$totalSalesThisMonth = 0;
$newOrdersToday = 0;
$newOrdersThisWeek = 0;
$totalCustomers = 0;
$lowStockProductsCount = 0;
$pendingReviewsCount = 0;

// --- Data Fetching Logic will go here ---
$todayDate = date("Y-m-d");
$currentMonth = date("Y-m");
$startOfWeek = date("Y-m-d", strtotime('monday this week'));
$endOfWeek = date("Y-m-d", strtotime('sunday this week'));

// --- 1. Total Sales ---
// Today
$stmtSalesToday = $conn->prepare("SELECT SUM(amount) as total FROM orders WHERE order_status = 'Paid' AND DATE(order_date) = ?");
if ($stmtSalesToday) {
    $stmtSalesToday->bind_param("s", $todayDate);
    $stmtSalesToday->execute();
    $result = $stmtSalesToday->get_result();
    $row = $result->fetch_assoc();
    $totalSalesToday = $row['total'] ?? 0;
    $stmtSalesToday->close();
} else {
    error_log("Error preparing sales today statement: " . $conn->error);
}

// This Week
$stmtSalesWeek = $conn->prepare("SELECT SUM(amount) as total FROM orders WHERE order_status = 'Paid' AND DATE(order_date) BETWEEN ? AND ?");
if ($stmtSalesWeek) {
    $stmtSalesWeek->bind_param("ss", $startOfWeek, $endOfWeek);
    $stmtSalesWeek->execute();
    $result = $stmtSalesWeek->get_result();
    $row = $result->fetch_assoc();
    $totalSalesThisWeek = $row['total'] ?? 0;
    $stmtSalesWeek->close();
} else {
    error_log("Error preparing sales week statement: " . $conn->error);
}

// This Month
$stmtSalesMonth = $conn->prepare("SELECT SUM(amount) as total FROM orders WHERE order_status = 'Paid' AND DATE_FORMAT(order_date, '%Y-%m') = ?");
if ($stmtSalesMonth) {
    $stmtSalesMonth->bind_param("s", $currentMonth);
    $stmtSalesMonth->execute();
    $result = $stmtSalesMonth->get_result();
    $row = $result->fetch_assoc();
    $totalSalesThisMonth = $row['total'] ?? 0;
    $stmtSalesMonth->close();
} else {
    error_log("Error preparing sales month statement: " . $conn->error);
}


// --- 2. Number of New Orders ---
// Today
$stmtOrdersToday = $conn->prepare("SELECT COUNT(order_id) as count FROM orders WHERE DATE(order_date) = ?");
if ($stmtOrdersToday) {
    $stmtOrdersToday->bind_param("s", $todayDate);
    $stmtOrdersToday->execute();
    $result = $stmtOrdersToday->get_result();
    $row = $result->fetch_assoc();
    $newOrdersToday = $row['count'] ?? 0;
    $stmtOrdersToday->close();
} else {
    error_log("Error preparing orders today statement: " . $conn->error);
}

// This Week
$stmtOrdersWeek = $conn->prepare("SELECT COUNT(order_id) as count FROM orders WHERE DATE(order_date) BETWEEN ? AND ?");
if ($stmtOrdersWeek) {
    $stmtOrdersWeek->bind_param("ss", $startOfWeek, $endOfWeek);
    $stmtOrdersWeek->execute();
    $result = $stmtOrdersWeek->get_result();
    $row = $result->fetch_assoc();
    $newOrdersThisWeek = $row['count'] ?? 0;
    $stmtOrdersWeek->close();
} else {
    error_log("Error preparing orders week statement: " . $conn->error);
}


// --- 3. Total Customers ---
// Note: Your customer table doesn't have a registration_date. We'll count total customers.
// To count new customers, you'd need to add a 'registration_date' column to your 'customer' table.
$stmtTotalCustomers = $conn->prepare("SELECT COUNT(customer_id) as count FROM customer");
if ($stmtTotalCustomers) {
    $stmtTotalCustomers->execute();
    $result = $stmtTotalCustomers->get_result();
    $row = $result->fetch_assoc();
    $totalCustomers = $row['count'] ?? 0;
    $stmtTotalCustomers->close();
} else {
    error_log("Error preparing total customers statement: " . $conn->error);
}


// --- 4. Low Stock Products ---
$lowStockThreshold = 10; // Define what you consider "low stock"
$stmtLowStock = $conn->prepare("SELECT COUNT(product_id) as count FROM products WHERE product_left < ?");
if ($stmtLowStock) {
    $stmtLowStock->bind_param("i", $lowStockThreshold);
    $stmtLowStock->execute();
    $result = $stmtLowStock->get_result();
    $row = $result->fetch_assoc();
    $lowStockProductsCount = $row['count'] ?? 0;
    $stmtLowStock->close();
} else {
    error_log("Error preparing low stock statement: " . $conn->error);
}


// --- 5. Pending Reviews ---
$stmtPendingReviews = $conn->prepare("SELECT COUNT(review_id) as count FROM reviews WHERE status = 'pending'");
if ($stmtPendingReviews) {
    $stmtPendingReviews->execute();
    $result = $stmtPendingReviews->get_result();
    $row = $result->fetch_assoc();
    $pendingReviewsCount = $row['count'] ?? 0;
    $stmtPendingReviews->close();
} else {
    error_log("Error preparing pending reviews statement: " . $conn->error);
}

// $conn->close(); // Close connection if not needed by footer or other includes
?>

<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Admin Panel'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9; /* Light grey background for admin areas */
        }
        .dashboard-container {
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .stat-card {
            background-color: #fff;
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff; /* Default text color, will be overridden by specific card bg */
        }
        .stat-card .inner {
            /* padding: 10px; */
        }
        .stat-card h3 {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 0 10px 0;
            padding: 0;
            white-space: nowrap;
        }
        .stat-card p {
            font-size: 1rem;
            margin-bottom: 0;
        }
        .stat-card .icon {
            color: rgba(0,0,0,.15);
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 70px;
            transition: all .3s linear;
        }
        .stat-card:hover .icon {
            font-size: 75px;
        }
        .stat-card .small-box-footer {
            background-color: rgba(0,0,0,.1);
            color: rgba(255,255,255,.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
            margin-top: 10px; /* Space above footer */
        }
        .stat-card .small-box-footer:hover {
            background-color: rgba(0,0,0,.15);
            color: #fff;
        }
        /* Specific card colors */
        .bg-info { background-color: #17a2b8 !important; }
        .bg-success { background-color: #28a745 !important; }
        .bg-warning { background-color: #ffc107 !important; color: #1f2d3d !important; } /* Darker text for yellow */
        .bg-danger { background-color: #dc3545 !important; }
        .bg-primary { background-color: #007bff !important; }
        .bg-secondary { background-color: #6c757d !important; }

        .quick-links-section .card-header, .reports-section .card-header {
            background-color: #e9ecef;
        }
        .quick-links-section .list-group-item {
            border-left: 0;
            border-right: 0;
        }
        .quick-links-section .list-group-item i {
            margin-right: 8px;
        }
    </style>
</head>
<body> <div class="container dashboard-container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Dashboard</h1>
        </div>

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-info">
                    <div class="inner">
                        <h3>$<?php echo number_format($totalSalesToday, 2); ?></h3>
                        <p>Sales Today</p>
                    </div>
                    
                    </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-success">
                    <div class="inner">
                        <h3>$<?php echo number_format($totalSalesThisWeek, 2); ?></h3>
                        <p>Sales This Week</p>
                    </div>
                    
                    </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-primary">
                    <div class="inner">
                        <h3>$<?php echo number_format($totalSalesThisMonth, 2); ?></h3>
                        <p>Sales This Month</p>
                    </div>
                    
                    </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-secondary">
                    <div class="inner">
                        <h3><?php echo $newOrdersToday; ?></h3>
                        <p>New Orders Today</p>
                    </div>
                    
                    </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-secondary">
                    <div class="inner">
                        <h3><?php echo $newOrdersThisWeek; ?></h3>
                        <p>New Orders This Week</p>
                    </div>
                    
                    </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-warning">
                    <div class="inner">
                        <h3><?php echo $totalCustomers; ?></h3>
                        <p>Total Customers</p>
                    </div>
                    
                    <a href="users.php" class="small-box-footer">View Users <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-danger">
                    <div class="inner">
                        <h3><?php echo $lowStockProductsCount; ?></h3>
                        <p>Low Stock Products (< <?php echo $lowStockThreshold; ?>)</p>
                    </div>
                    
                    <a href="post.php?filter=low_stock" class="small-box-footer">Manage Products <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="stat-card bg-info"> <div class="inner">
                        <h3><?php echo $pendingReviewsCount; ?></h3>
                        <p>Pending Reviews</p>
                    </div>
                    <a href="manage_reviews.php" class="small-box-footer">Manage Reviews <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

    </div> </body> </html> <?php
// Optional: Include a common admin footer if you have one
// include_once('./includes/footer.php');
?>
