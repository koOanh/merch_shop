<?php
// FILE: admin/manage_deals.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in
if (!isset($_SESSION['logged-in'])) {
    header("Location: login.php?unauthorizedAccess_manage_deals");
    exit;
}

// Include database configuration
include_once "./includes/config.php";

// --- Handle Actions (Delete, Toggle Status) ---
$action_message = '';
$action_type = ''; // 'success' or 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete
    if (isset($_POST['delete_deal']) && isset($_POST['deal_id']) && is_numeric($_POST['deal_id'])) {
        $deal_id_to_delete = (int)$_POST['deal_id'];
        // Optional: Delete the image file from server as well
        $stmt_img = $conn->prepare("SELECT deal_image FROM deal_of_the_day WHERE deal_id = ?");
        if ($stmt_img) {
            $stmt_img->bind_param("i", $deal_id_to_delete);
            $stmt_img->execute();
            $result_img = $stmt_img->get_result();
            if ($img_row = $result_img->fetch_assoc()) {
                if (!empty($img_row['deal_image']) && file_exists("./upload/" . $img_row['deal_image'])) {
                    unlink("./upload/" . $img_row['deal_image']);
                }
            }
            $stmt_img->close();
        }

        $stmt_delete = $conn->prepare("DELETE FROM deal_of_the_day WHERE deal_id = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("i", $deal_id_to_delete);
            if ($stmt_delete->execute()) {
                $action_message = "Deal ID " . $deal_id_to_delete . " deleted successfully.";
                $action_type = "success";
            } else {
                $action_message = "Error deleting deal: " . $stmt_delete->error;
                $action_type = "danger";
                error_log("Error deleting deal ID $deal_id_to_delete: " . $stmt_delete->error);
            }
            $stmt_delete->close();
        } else {
            $action_message = "Error preparing delete statement: " . $conn->error;
            $action_type = "danger";
            error_log("Error preparing delete statement for deal: " . $conn->error);
        }
    }

    // Handle Toggle Status
    if (isset($_POST['toggle_status']) && isset($_POST['deal_id']) && is_numeric($_POST['deal_id'])) {
        $deal_id_to_toggle = (int)$_POST['deal_id'];
        // First, get the current status
        $current_status = 0; // Default to inactive
        $stmt_get_status = $conn->prepare("SELECT deal_status FROM deal_of_the_day WHERE deal_id = ?");
        if ($stmt_get_status) {
            $stmt_get_status->bind_param("i", $deal_id_to_toggle);
            $stmt_get_status->execute();
            $result_status = $stmt_get_status->get_result();
            if ($row_status = $result_status->fetch_assoc()) {
                $current_status = (int)$row_status['deal_status'];
            }
            $stmt_get_status->close();
        }

        $new_status = ($current_status == 1) ? 0 : 1; // Toggle
        $stmt_toggle = $conn->prepare("UPDATE deal_of_the_day SET deal_status = ? WHERE deal_id = ?");
        if ($stmt_toggle) {
            $stmt_toggle->bind_param("ii", $new_status, $deal_id_to_toggle);
            if ($stmt_toggle->execute()) {
                $action_message = "Deal ID " . $deal_id_to_toggle . " status updated to " . ($new_status == 1 ? 'Active' : 'Inactive') . ".";
                $action_type = "success";
            } else {
                $action_message = "Error updating deal status: " . $stmt_toggle->error;
                $action_type = "danger";
                error_log("Error toggling status for deal ID $deal_id_to_toggle: " . $stmt_toggle->error);
            }
            $stmt_toggle->close();
        } else {
            $action_message = "Error preparing status toggle statement: " . $conn->error;
            $action_type = "danger";
            error_log("Error preparing status toggle statement for deal: " . $conn->error);
        }
    }
}

// --- Pagination Configuration ---
$results_per_page = 10;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}
if ($current_page < 1) $current_page = 1;
$start_from = ($current_page - 1) * $results_per_page;

// --- Fetch Deals ---
$sql_deals = "SELECT deal_id, deal_title, deal_image, deal_net_price, deal_discounted_price, available_deal, sold_deal, deal_status
              FROM deal_of_the_day
              ORDER BY deal_id DESC
              LIMIT ?, ?";
$stmt_deals = $conn->prepare($sql_deals);
$deals_data = [];

if ($stmt_deals) {
    $stmt_deals->bind_param("ii", $start_from, $results_per_page);
    $stmt_deals->execute();
    $result_deals = $stmt_deals->get_result();
    while ($row = $result_deals->fetch_assoc()) {
        $deals_data[] = $row;
    }
    $stmt_deals->close();
} else {
    error_log("Error preparing deals statement in manage_deals.php: " . $conn->error);
    $action_message = "Error fetching deals: " . $conn->error;
    $action_type = "danger";
}

// --- Get Total Number of Deals (for pagination) ---
$sql_total = "SELECT COUNT(deal_id) as total FROM deal_of_the_day";
$result_total_deals = $conn->query($sql_total);
$total_deals = 0;
if ($result_total_deals) {
    $row_total = $result_total_deals->fetch_assoc();
    $total_deals = $row_total['total'] ?? 0;
}
$total_pages = ceil($total_deals / $results_per_page);

?>

<head>
    <style>
        .table-actions form { display: inline-block; margin-right: 5px; }
        .table-actions .btn-sm i { font-size: 0.9rem; } /* Slightly smaller icons */
        .deal-image-thumbnail {
            max-width: 60px;
            height: auto;
            border-radius: 3px;
        }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Manage "Deal of the Day"</h1>
        <a href="add_edit_deal.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Deal
        </a>
    </div>

    <?php if ($action_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($action_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($action_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($deals_data)): ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Image</th>
                        <th scope="col">Title</th>
                        <th scope="col" class="text-end">Net Price</th>
                        <th scope="col" class="text-end">Discounted Price</th>
                        <th scope="col" class="text-center">Available</th>
                        <th scope="col" class="text-center">Sold</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $row_number = $start_from + 1;
                    foreach ($deals_data as $deal): ?>
                    <tr>
                        <th scope="row"><?php echo $row_number++; ?></th>
                        <td>
                            <img src="../admin/upload/<?php echo htmlspecialchars($deal['deal_image'] ?? 'placeholder.png'); ?>"
                                 alt="<?php echo htmlspecialchars($deal['deal_title']); ?>" class="deal-image-thumbnail"
                                 onerror="this.src='https://placehold.co/60x60/cccccc/ffffff?text=NoImg';">
                        </td>
                        <td><?php echo htmlspecialchars($deal['deal_title']); ?></td>
                        <td class="text-end">$<?php echo number_format($deal['deal_net_price'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($deal['deal_discounted_price'], 2); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($deal['available_deal']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($deal['sold_deal']); ?></td>
                        <td class="text-center <?php echo ($deal['deal_status'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ($deal['deal_status'] == 1) ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td class="text-center table-actions">
                            <a href="add_edit_deal.php?deal_id=<?php echo $deal['deal_id']; ?>" class="btn btn-sm btn-warning" title="Edit Deal">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="manage_deals.php?page=<?php echo $current_page; ?>" method="POST" class="d-inline">
                                <input type="hidden" name="deal_id" value="<?php echo $deal['deal_id']; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm <?php echo ($deal['deal_status'] == 1) ? 'btn-secondary' : 'btn-success'; ?>" 
                                        title="<?php echo ($deal['deal_status'] == 1) ? 'Set Inactive' : 'Set Active'; ?>">
                                    <i class="fas <?php echo ($deal['deal_status'] == 1) ? 'fa-toggle-off' : 'fa-toggle-on'; ?>"></i>
                                </button>
                            </form>
                            <form action="manage_deals.php?page=<?php echo $current_page; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this deal? This will also delete its image.');">
                                <input type="hidden" name="deal_id" value="<?php echo $deal['deal_id']; ?>">
                                <button type="submit" name="delete_deal" class="btn btn-sm btn-danger" title="Delete Deal">
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
        <nav aria-label="Deals navigation" class="mt-4">
            <ul class="pagination justify-content-center pagination-sm">
                <?php if ($current_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php elseif (!$action_message): ?>
        <div class="alert alert-info mt-3">No deals found. <a href="add_edit_deal.php">Add a new deal now!</a></div>
    <?php endif; ?>
</div>

<?php
// include_once('./includes/footer.php');
?>
