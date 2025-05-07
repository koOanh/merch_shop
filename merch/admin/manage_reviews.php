<?php
// FILE: admin/manage_reviews.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in
if (!isset($_SESSION['logged-in'])) {
    header("Location: login.php?unauthorizedAccess_manage_reviews");
    exit;
}

// Include database configuration
include_once "./includes/config.php";

// --- Handle Actions (Delete/Update Status) ---
$action_message = '';
$action_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_review']) && isset($_POST['review_id']) && is_numeric($_POST['review_id'])) {
        $review_id_to_delete = (int)$_POST['review_id'];
        $stmt_delete = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $review_id_to_delete);
            if ($stmt_delete->execute()) {
                $action_message = "Review ID " . $review_id_to_delete . " deleted successfully.";
                $action_type = "success";
            } else {
                $action_message = "Error deleting review: " . $stmt_delete->error;
                $action_type = "danger";
                error_log("Error deleting review ID $review_id_to_delete: " . $stmt_delete->error);
            }
            $stmt_delete->close();
        } else {
            $action_message = "Error preparing delete statement: " . $conn->error;
            $action_type = "danger";
            error_log("Error preparing delete statement for review: " . $conn->error);
        }
    }
    // Add logic for status update here if you re-introduce moderation
}


// --- Pagination Configuration ---
$results_per_page = 15;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}
if ($current_page < 1) {
    $current_page = 1;
}
$start_from = ($current_page - 1) * $results_per_page;

// --- Filtering and Searching ---
$filter_product_id = $_GET['product_id'] ?? '';
$filter_rating = $_GET['rating'] ?? '';
$search_comment = $_GET['search_comment'] ?? '';

$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($filter_product_id) && is_numeric($filter_product_id)) {
    $where_clauses[] = "r.product_id = ?";
    $params[] = (int)$filter_product_id;
    $param_types .= 'i';
}
if (!empty($filter_rating) && is_numeric($filter_rating) && $filter_rating >= 1 && $filter_rating <= 5) {
    $where_clauses[] = "r.rating = ?";
    $params[] = (int)$filter_rating;
    $param_types .= 'i';
}
if (!empty($search_comment)) {
    $search_like = "%" . $search_comment . "%";
    $where_clauses[] = "r.comment LIKE ?";
    $params[] = $search_like;
    $param_types .= 's';
}

$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = " WHERE " . implode(" AND ", $where_clauses);
}


// --- Fetch Reviews ---
$sql_reviews_base = "FROM reviews r 
                     JOIN products p ON r.product_id = p.product_id
                     JOIN customer c ON r.customer_id = c.customer_id ";

// *** CORRECTED SQL: Removed c.customer_lname ***
$sql_reviews = "SELECT r.review_id, r.rating, r.comment, r.review_date, r.status, r.order_id,
                       p.product_title, p.product_id as pid,
                       c.customer_fname, c.customer_email " // c.customer_lname removed
               . $sql_reviews_base
               . $sql_where
               . " ORDER BY r.review_date DESC LIMIT ?, ?";

$stmt_reviews = $conn->prepare($sql_reviews); // This is approximately line 102
$reviews_data = [];

if ($stmt_reviews) {
    $current_params = $params; // Use a copy for this query
    $current_params[] = $start_from;
    $current_params[] = $results_per_page;
    $current_param_types = $param_types . 'ii';

    if (!empty($current_param_types) && count($current_params) > 0) {
        // Check if number of params matches placeholders in $current_param_types
        if (substr_count($sql_reviews, '?') == count($current_params)) {
             $stmt_reviews->bind_param($current_param_types, ...$current_params);
        } else {
            error_log("Mismatch in bind_param count for reviews query. Expected: " . substr_count($sql_reviews, '?') . ", Got: " . count($current_params));
            // Handle error, perhaps by not executing or setting a flag
        }
    }
    
    if ($stmt_reviews->execute()) { // Check if execute was successful before getting result
        $result_reviews = $stmt_reviews->get_result();
        while ($row = $result_reviews->fetch_assoc()) {
            $reviews_data[] = $row;
        }
    } else {
        error_log("Error executing reviews statement in manage_reviews.php: " . $stmt_reviews->error);
        $action_message = "Error fetching reviews (execute): " . $stmt_reviews->error;
        $action_type = "danger";
    }
} else {
    error_log("Error preparing reviews statement in manage_reviews.php: " . $conn->error);
    $action_message = "Error fetching reviews (prepare): " . $conn->error;
    $action_type = "danger";
}

// --- Get Total Number of Reviews (for pagination, considering filters) ---
$sql_total = "SELECT COUNT(r.review_id) as total " . $sql_reviews_base . $sql_where;
$stmt_total = $conn->prepare($sql_total);
$total_reviews = 0;

if ($stmt_total) {
    if (!empty($param_types) && count($params) > 0) { // Bind params if filters are applied
         // Check if number of params matches placeholders in $param_types
        if (substr_count($sql_total, '?') == count($params)) {
            $stmt_total->bind_param($param_types, ...$params);
        } else {
             error_log("Mismatch in bind_param count for total reviews query. Expected: " . substr_count($sql_total, '?') . ", Got: " . count($params));
        }
    }
    if ($stmt_total->execute()) {
        $result_total_reviews = $stmt_total->get_result();
        $row_total = $result_total_reviews->fetch_assoc();
        $total_reviews = $row_total['total'] ?? 0;
    } else {
        error_log("Error executing total reviews statement: " . $stmt_total->error);
    }
    $stmt_total->close();
} else {
    error_log("Error preparing total reviews statement in manage_reviews.php: " . $conn->error);
}
$total_pages = ceil($total_reviews / $results_per_page);

?>

<head>
    <style>
        .table-actions form { display: inline-block; margin-right: 5px; }
        .filter-form .form-control, .filter-form .form-select { margin-right: 10px; margin-bottom: 10px; }
        .filter-form .btn { margin-bottom: 10px; }
        .review-comment-short {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help; /* Indicate it's expandable or has more content */
        }
        .rating-stars .star { color: #f5b301; }
        .rating-stars .star-empty { color: #ccc; }
        .badge-status-Approved { background-color: #28a745; color: white; }
        .badge-status-Pending { background-color: #ffc107; color: #212529; }
        .badge-status-Rejected { background-color: #dc3545; color: white; }
         .badge {
            padding: 0.35em 0.65em; font-size: .75em; font-weight: 700;
            line-height: 1; text-align: center; white-space: nowrap;
            vertical-align: baseline; border-radius: .25rem;
        }
    </style>
</head>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Manage Reviews</h1>
    </div>

    <?php if ($action_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($action_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($action_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="manage_reviews.php" method="GET" class="filter-form mb-4 p-3 border rounded bg-light">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filter_product_id" class="form-label">Product ID</label>
                <input type="number" class="form-control form-control-sm" id="filter_product_id" name="product_id" placeholder="Enter Product ID" value="<?php echo htmlspecialchars($filter_product_id); ?>">
            </div>
            <div class="col-md-3">
                <label for="filter_rating" class="form-label">Rating</label>
                <select class="form-select form-select-sm" id="filter_rating" name="rating">
                    <option value="">All Ratings</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if ($filter_rating == $i) echo 'selected'; ?>>
                            <?php echo $i; ?> Star<?php echo ($i > 1) ? 's' : ''; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search_comment" class="form-label">Search Comment</label>
                <input type="text" class="form-control form-control-sm" id="search_comment" name="search_comment" placeholder="Keywords in comment..." value="<?php echo htmlspecialchars($search_comment); ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="manage_reviews.php" class="btn btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>


    <?php if (!empty($reviews_data)): ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Product</th>
                        <th scope="col">Customer</th>
                        <th scope="col" class="text-center">Rating</th>
                        <th scope="col">Comment (excerpt)</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_number = $start_from + 1;
                    foreach ($reviews_data as $review): 
                        // *** CORRECTED CUSTOMER NAME DISPLAY ***
                        $customer_display_name = htmlspecialchars(trim($review['customer_fname']));
                        if (empty($customer_display_name)) {
                            $customer_display_name = htmlspecialchars($review['customer_email']); // Fallback to email if fname is empty
                        }
                        $comment_excerpt = mb_strimwidth(htmlspecialchars($review['comment'] ?? ''), 0, 70, "...");
                    ?>
                    <tr>
                        <th scope="row"><?php echo $row_number++; ?></th>
                        <td>
                            <a href="../viewdetail.php?id=<?php echo $review['pid']; ?>&category=product" target="_blank">
                                <?php echo htmlspecialchars($review['product_title']); ?>
                            </a>
                            <small class="d-block text-muted">ID: <?php echo $review['pid']; ?></small>
                        </td>
                        <td><?php echo $customer_display_name; ?></td>
                        <td class="text-center rating-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star<?php echo ($i > $review['rating']) ? '-empty' : ''; ?>">&#9733;</span>
                            <?php endfor; ?>
                        </td>
                        <td class="review-comment-short" title="<?php echo htmlspecialchars($review['comment'] ?? 'No comment'); ?>">
                            <?php echo $comment_excerpt ?: '<em>No comment</em>'; ?>
                        </td>
                        <td><?php echo htmlspecialchars(date("M d, Y", strtotime($review['review_date']))); ?></td>
                        <td class="text-center">
                             <span class="badge badge-status-<?php echo htmlspecialchars(ucfirst(strtolower($review['status']))); ?>">
                                <?php echo htmlspecialchars(ucfirst($review['status'])); ?>
                            </span>
                        </td>
                        <td class="text-center table-actions">
                            <form action="manage_reviews.php?page=<?php echo $current_page; ?>&product_id=<?php echo urlencode($filter_product_id); ?>&rating=<?php echo urlencode($filter_rating); ?>&search_comment=<?php echo urlencode($search_comment); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                <button type="submit" name="delete_review" class="btn btn-sm btn-danger" title="Delete Review">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                             </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Reviews navigation" class="mt-4">
            <ul class="pagination justify-content-center pagination-sm">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&product_id=<?php echo urlencode($filter_product_id); ?>&rating=<?php echo urlencode($filter_rating); ?>&search_comment=<?php echo urlencode($search_comment); ?>">Previous</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&product_id=<?php echo urlencode($filter_product_id); ?>&rating=<?php echo urlencode($filter_rating); ?>&search_comment=<?php echo urlencode($search_comment); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&product_id=<?php echo urlencode($filter_product_id); ?>&rating=<?php echo urlencode($filter_rating); ?>&search_comment=<?php echo urlencode($search_comment); ?>">Next</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php elseif (!$action_message): // Only show "No reviews" if no action message is already displayed ?>
        <div class="alert alert-info mt-3">No reviews found matching your criteria.</div>
    <?php endif; ?>

    <?php
        if ($stmt_reviews) {
            $stmt_reviews->close();
        }
        // $conn->close(); // Connection might be needed by footer
    ?>
</div>

<?php
// include_once('./includes/footer.php'); // If you have a common admin footer
?>
