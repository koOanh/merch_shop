<?php
// FILE: admin/add_edit_deal.php

// Include header (handles session_start()) and restriction check
include_once('./includes/headerNav.php');
include_once('./includes/restriction.php'); // Ensures only admin can access

// Check if admin is logged in
if (!isset($_SESSION['logged-in'])) {
    header("Location: login.php?unauthorizedAccess_add_edit_deal");
    exit;
}

// Include database configuration
include_once "./includes/config.php";

// Initialize variables
$deal_id_to_edit = null;
$edit_mode = false;
$page_title = "Add New Deal";

// Form field values
$deal_title = '';
$deal_description = '';
$deal_net_price = '';
$deal_discounted_price = '';
$available_deal = '';
$sold_deal = 0; // Default sold to 0 for new deals
$deal_status = 1; // Default to Active for new deals
$current_deal_image = ''; // For storing existing image filename during edit

// Messages
$error_message = '';
$success_message = '';
$upload_dir = "./upload/"; // Make sure this directory exists and is writable

// --- Check if we are in EDIT mode ---
if (isset($_GET['deal_id']) && is_numeric($_GET['deal_id'])) {
    $edit_mode = true;
    $deal_id_to_edit = (int)$_GET['deal_id'];
    $page_title = "Edit Deal (ID: " . $deal_id_to_edit . ")";

    // Fetch existing deal data
    $stmt_fetch = $conn->prepare("SELECT * FROM deal_of_the_day WHERE deal_id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $deal_id_to_edit);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($deal_data = $result_fetch->fetch_assoc()) {
            $deal_title = $deal_data['deal_title'];
            $deal_description = $deal_data['deal_description'];
            $deal_net_price = $deal_data['deal_net_price'];
            $deal_discounted_price = $deal_data['deal_discounted_price'];
            $available_deal = $deal_data['available_deal'];
            $sold_deal = $deal_data['sold_deal'];
            $deal_status = $deal_data['deal_status'];
            $current_deal_image = $deal_data['deal_image'];
        } else {
            $error_message = "Deal not found for editing.";
            $edit_mode = false; // Revert to add mode or disable form
        }
        $stmt_fetch->close();
    } else {
        $error_message = "Error preparing to fetch deal data: " . $conn->error;
        error_log("Error preparing fetch deal statement: " . $conn->error);
    }
}

// --- Handle Form Submission (Add or Edit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $deal_title = trim(strip_tags($_POST['deal_title']));
    $deal_description = trim(strip_tags($_POST['deal_description']));
    $deal_net_price = filter_var($_POST['deal_net_price'], FILTER_VALIDATE_FLOAT);
    $deal_discounted_price = filter_var($_POST['deal_discounted_price'], FILTER_VALIDATE_FLOAT);
    $available_deal = filter_var($_POST['available_deal'], FILTER_VALIDATE_INT);
    $sold_deal = filter_var($_POST['sold_deal'], FILTER_VALIDATE_INT);
    $deal_status = isset($_POST['deal_status']) ? 1 : 0; // Checkbox for status

    // Hidden field for deal_id in edit mode
    if (isset($_POST['deal_id']) && is_numeric($_POST['deal_id'])) {
        $deal_id_to_edit = (int)$_POST['deal_id'];
        $edit_mode = true; // Ensure edit mode is set if deal_id is POSTed
    }
     // Retrieve current image if editing and no new image is uploaded
    $current_deal_image = $_POST['current_deal_image'] ?? '';


    // Basic Validation
    if (empty($deal_title) || $deal_net_price === false || $deal_discounted_price === false || $available_deal === false || $sold_deal === false) {
        $error_message = "Please fill in all required fields with valid data (Title, Prices, Available Qty, Sold Qty).";
    } else {
        $new_image_filename = $current_deal_image; // Assume current image unless new one is uploaded

        // Handle Image Upload
        if (isset($_FILES['deal_image']) && $_FILES['deal_image']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['deal_image']['tmp_name'];
            $file_name = $_FILES['deal_image']['name'];
            $file_size = $_FILES['deal_image']['size'];
            $file_type = $_FILES['deal_image']['type'];
            $file_name_parts = explode(".", $file_name);
            $file_extension = strtolower(end($file_name_parts));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_extensions)) {
                if ($file_size < 5000000) { // Max 5MB
                    // Create a unique filename to prevent overwriting
                    $new_image_filename = uniqid('deal_', true) . '.' . $file_extension;
                    $dest_path = $upload_dir . $new_image_filename;

                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        // New image uploaded successfully. If editing and old image exists, delete it.
                        if ($edit_mode && !empty($current_deal_image) && $current_deal_image != $new_image_filename) {
                            if (file_exists($upload_dir . $current_deal_image)) {
                                unlink($upload_dir . $current_deal_image);
                            }
                        }
                        // $current_deal_image = $new_image_filename; // Update current image for the form
                    } else {
                        $error_message = "Error moving uploaded image to destination.";
                        $new_image_filename = $current_deal_image; // Revert to old image if move failed
                    }
                } else {
                    $error_message = "Image file is too large (Max 5MB).";
                    $new_image_filename = $current_deal_image;
                }
            } else {
                $error_message = "Invalid image file type. Allowed types: jpg, jpeg, png, gif.";
                $new_image_filename = $current_deal_image;
            }
        } elseif ($edit_mode) {
            // No new image uploaded during edit, keep the existing one
            $new_image_filename = $current_deal_image;
        } elseif (!$edit_mode && (!isset($_FILES['deal_image']) || $_FILES['deal_image']['error'] != UPLOAD_ERR_OK)) {
            // No image uploaded for a new deal
            $error_message = "An image is required for a new deal.";
        }


        if (empty($error_message)) { // Proceed if no validation or upload errors
            if ($edit_mode && $deal_id_to_edit) {
                // --- UPDATE Existing Deal ---
                $sql_update = "UPDATE deal_of_the_day SET 
                                deal_title = ?, deal_description = ?, deal_image = ?, 
                                deal_net_price = ?, deal_discounted_price = ?, 
                                available_deal = ?, sold_deal = ?, deal_status = ?
                               WHERE deal_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("sssddiiii", 
                        $deal_title, $deal_description, $new_image_filename,
                        $deal_net_price, $deal_discounted_price,
                        $available_deal, $sold_deal, $deal_status,
                        $deal_id_to_edit
                    );
                    if ($stmt_update->execute()) {
                        $success_message = "Deal updated successfully!";
                        // Refresh current_deal_image if it was updated
                        $current_deal_image = $new_image_filename;
                    } else {
                        $error_message = "Error updating deal: " . $stmt_update->error;
                        error_log("Error updating deal ID $deal_id_to_edit: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                } else {
                     $error_message = "Error preparing update statement: " . $conn->error;
                     error_log("Error preparing update deal statement: " . $conn->error);
                }
            } else {
                // --- INSERT New Deal ---
                $sql_insert = "INSERT INTO deal_of_the_day 
                               (deal_title, deal_description, deal_image, deal_net_price, deal_discounted_price, available_deal, sold_deal, deal_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("sssddiii", 
                        $deal_title, $deal_description, $new_image_filename,
                        $deal_net_price, $deal_discounted_price,
                        $available_deal, $sold_deal, $deal_status
                    );
                    if ($stmt_insert->execute()) {
                        $success_message = "New deal added successfully!";
                        $new_deal_id = $conn->insert_id;
                        // Optionally redirect or clear form
                        // header("Location: manage_deals.php?status=added"); exit;
                        // Or reset form fields for another entry
                        $deal_title = $deal_description = $deal_net_price = $deal_discounted_price = $available_deal = '';
                        $sold_deal = 0; $deal_status = 1; $current_deal_image = ''; $edit_mode = false;
                    } else {
                        $error_message = "Error adding new deal: " . $stmt_insert->error;
                        error_log("Error inserting new deal: " . $stmt_insert->error);
                    }
                    $stmt_insert->close();
                } else {
                    $error_message = "Error preparing insert statement: " . $conn->error;
                    error_log("Error preparing insert deal statement: " . $conn->error);
                }
            }
        }
    }
}

?>

<head>
    <style>
        .form-container {
            background-color: #fff;
            padding: 25px;
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125),0 1px 3px rgba(0,0,0,.2);
            margin-top: 20px;
        }
        .current-image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 3px;
            border-radius: 4px;
        }
    </style>
</head>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="manage_deals.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Deals List
        </a>
    </div>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form action="add_edit_deal.php<?php if ($edit_mode && $deal_id_to_edit) echo '?deal_id=' . $deal_id_to_edit; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?php if ($edit_mode && $deal_id_to_edit): ?>
                <input type="hidden" name="deal_id" value="<?php echo $deal_id_to_edit; ?>">
            <?php endif; ?>
            <input type="hidden" name="current_deal_image" value="<?php echo htmlspecialchars($current_deal_image); ?>">


            <div class="mb-3">
                <label for="deal_title" class="form-label">Deal Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="deal_title" name="deal_title" value="<?php echo htmlspecialchars($deal_title); ?>" required>
                <div class="invalid-feedback">Please provide a deal title.</div>
            </div>

            <div class="mb-3">
                <label for="deal_description" class="form-label">Description</label>
                <textarea class="form-control" id="deal_description" name="deal_description" rows="3"><?php echo htmlspecialchars($deal_description); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="deal_image" class="form-label">Deal Image <?php if (!$edit_mode) echo '<span class="text-danger">*</span>'; ?></label>
                <?php if ($edit_mode && !empty($current_deal_image)): ?>
                    <p><img src="<?php echo $upload_dir . htmlspecialchars($current_deal_image); ?>" alt="Current Deal Image" class="current-image-preview"></p>
                    <small class="form-text text-muted">Current image: <?php echo htmlspecialchars($current_deal_image); ?>. Upload a new image to replace it.</small>
                <?php endif; ?>
                <input type="file" class="form-control" id="deal_image" name="deal_image" accept="image/png, image/jpeg, image/gif" <?php if (!$edit_mode) echo 'required'; ?>>
                <div class="invalid-feedback">Please select an image file (jpg, jpeg, png, gif).</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="deal_net_price" class="form-label">Net Price (Actual Price) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="deal_net_price" name="deal_net_price" step="0.01" min="0" value="<?php echo htmlspecialchars($deal_net_price); ?>" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid net price.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="deal_discounted_price" class="form-label">Discounted Price (Slashed Price) <span class="text-danger">*</span></label>
                     <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="deal_discounted_price" name="deal_discounted_price" step="0.01" min="0" value="<?php echo htmlspecialchars($deal_discounted_price); ?>" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid discounted price.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="available_deal" class="form-label">Available Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="available_deal" name="available_deal" min="0" value="<?php echo htmlspecialchars($available_deal); ?>" required>
                    <div class="invalid-feedback">Please enter available quantity.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sold_deal" class="form-label">Sold Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="sold_deal" name="sold_deal" min="0" value="<?php echo htmlspecialchars($sold_deal); ?>" required>
                     <div class="invalid-feedback">Please enter sold quantity.</div>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="deal_status" name="deal_status" value="1" <?php if ($deal_status == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="deal_status">Active (Show on homepage)</label>
            </div>

            <button type="submit" class="btn <?php echo $edit_mode ? 'btn-warning' : 'btn-success'; ?>">
                <i class="fas <?php echo $edit_mode ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_mode ? 'Update Deal' : 'Add Deal'; ?>
            </button>
        </form>
    </div>
</div>

<script>
    // Bootstrap form validation
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
    })()
</script>

<?php
// include_once('./includes/footer.php');
?>
