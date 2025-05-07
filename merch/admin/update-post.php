<?php
    // --- PHP Includes & Data Fetching (Original PHP - with notes) ---
    include_once('./includes/headerNav.php'); // Assumes standard setup
    include_once('./includes/restriction.php');

    // NOTE: Using $_SESSION to pass temporary form data is unconventional. Fetching into variables is preferred.
    // --- START: PHP Logic (Unchanged as requested, but VULNERABLE) ---
    include "includes/config.php";

    // SECURITY WARNING: $_GET['id'] used directly in SQL is a major SQL Injection vulnerability. MUST use Prepared Statements.
    $sql = "SELECT * FROM products where product_id={$_GET['id']}"; // VULNERABLE SQL
    $result = $conn->query($sql);

    // Basic check if product was found
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Storing data in session variables (original logic)
        $_SESSION['previous_title'] = $row['product_title'];
        $_SESSION['previous_desc'] = $row['product_desc'];
        $_SESSION['previous_catag'] = $row['product_catag'];
        $_SESSION['previous_price'] = $row['product_price'];
        $_SESSION['previous_discount'] = $row['discounted_price']; // Check what this field actually contains (discount amount or final price?)
        $_SESSION['previous_no'] = $row['product_left'];
        $_SESSION['previous_img'] = $row['product_img'];
        // $_SESSION['previous_availability'] = $row['availability_status']; // Add this if you have an availability field
    } else {
         // Handle error: Product not found
         echo "<div class='container'><div class='alert alert-danger'>Error: Product with ID " . htmlspecialchars($_GET['id'] ?? 'UNKNOWN') . " not found.</div></div>";
         // Optionally include footer and exit if headerNav doesn't handle full page structure
         // include_once('./includes/footer.php');
         exit;
    }
    $conn->close(); // Connection closed here, will be reopened by include in update logic below.
    // --- END: PHP Logic (Unchanged as requested) ---
?>

<div class="container main-content-wrapper py-4" style="display: flex; justify-content: center; align-items: center;">
    <div class="card shadow-sm" style="max-width: 800px; width: 100%;"> <div class="card-body p-4 p-md-5"> <h2 class="card-title text-center mb-4">Edit Product</h2> <form action="" method="POST" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
                <div class="col-12">
                    <label for="prodTitle" class="form-label">Title</label>
                    <input type="text" class="form-control" id="prodTitle" name="title" value="<?php echo htmlspecialchars($_SESSION['previous_title'] ?? ''); // Added null coalesce ?>" required>
                    <div class="invalid-feedback">Please provide a product title.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodPrice" class="form-label">Price</label>
                    <div class="input-group">
                       <span class="input-group-text">$</span>
                       <input type="number" class="form-control" id="prodPrice" name="price" value="<?php echo htmlspecialchars($_SESSION['previous_price'] ?? '0'); ?>" step="0.01" min="0" required>
                       <div class="invalid-feedback">Please enter a valid price.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="prodDiscount" class="form-label">Discount</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="prodDiscount" name="discount" value="<?php echo htmlspecialchars($_SESSION['previous_discount'] ?? '0'); ?>" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                        <div class="invalid-feedback">Enter discount (0-100).</div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="prodDesc" class="form-label">Description</label>
                    <textarea class="form-control" id="prodDesc" name="desc" rows="4" required><?php echo htmlspecialchars($_SESSION['previous_desc'] ?? ''); ?></textarea>
                    <div class="invalid-feedback">Please enter a description.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodStock" class="form-label">No. of Items (Stock)</label>
                    <input type="number" class="form-control" id="prodStock" name="noofitem" value="<?php echo htmlspecialchars($_SESSION['previous_no'] ?? '0'); ?>" min="0" required>
                    <div class="invalid-feedback">Please enter the available quantity.</div>
                </div>

                <div class="col-md-6">
                    <label for="prodCategory" class="form-label">Category</label>
                    <select class="form-select" id="prodCategory" name="catag" required>
                        <option value="" disabled>Choose...</option>
                        <?php $current_catag = $_SESSION['previous_catag'] ?? ''; // Get current category ?>
                        <option value="all" <?php if ($current_catag === 'all') echo 'selected'; ?>>All</option>
                        <option value="men" <?php if ($current_catag === 'men') echo 'selected'; ?>>Men</option>
                        <option value="women" <?php if ($current_catag === 'women') echo 'selected'; ?>>Women</option>
                        </select>
                    <div class="invalid-feedback">Please select a category.</div>
                </div>

                <div class="col-12">
                    <label for="prodImg" class="form-label">Update Image (Optional)</label>
                    <?php if (!empty($_SESSION['previous_img'])): ?>
                        <p class="text-muted small mb-1">Current image: <?php echo htmlspecialchars($_SESSION['previous_img']); ?></p>
                         <?php endif; ?>
                    <input type="file" class="form-control" id="prodImg" name="newimg" accept="image/*">
                    <div class="form-text">Upload a new image only if you want to replace the current one.</div>
                </div>


                <div class="col-12 text-center mt-4">
                    <button type="submit" name="update" class="btn btn-lg" style="background-color:pink;">Update Product</button>
                    <a href="post.php" class="btn btn-secondary btn-lg ms-2">Cancel</a> </div>

            </form>
            </div> </div> </div> <?php
    // --- PHP Update Logic (Original PHP - with notes) ---
    if(isset($_POST['update'])){
        // NOTE: Connection was closed, re-including config is necessary here.
        // --- START: PHP Logic (Unchanged as requested, but VULNERABLE) ---
        include "includes/config.php";

        // SECURITY WARNING: All $_POST/$_GET values used directly in SQL are major SQL Injection vulnerabilities. MUST use Prepared Statements.
        // SECURITY WARNING: File upload ('newimg') needs proper handling (validation, sanitization, move_uploaded_file). The SQL below doesn't handle the image update.
        // LOGIC NOTE: Check what 'discounted_price' column stores. If it's final price, calculate it based on $_POST['price'] and $_POST['discount']. If it's the discount %, then using $_POST['discount'] directly is likely correct (but clarify column purpose).

        // VULNERABLE SQL
        $sql1 = "UPDATE products
                 SET  product_title= '{$_POST['title']}' ,
                      product_catag= '{$_POST['catag']}' ,
                      product_price= '{$_POST['price']}' ,
                      discounted_price= '{$_POST['discount']}', /* Verify this mapping */
                      product_desc= '{$_POST['desc']}',
                      /* product_img= '{$_POST['newimg']}', <-- Image requires separate, secure handling */
                      product_left= '{$_POST['noofitem']}'
                 WHERE product_id={$_GET['id']} "; // Using $_GET['id'] is vulnerable; use validated ID.

        // Execute query - basic check for success
        if ($conn->query($sql1)) {
            // NOTE: Handle file upload HERE if a new image was submitted, before redirecting.
            $conn->close();
            // NOTE: Hardcoded localhost URL is bad practice. Use relative paths.
            // NOTE: Redirect MUST be followed by exit;
            header("Location: post.php?succesfullyUpdated"); // Use relative path
            exit; // CRITICAL: Add exit; here
        } else {
             // Handle query error
             echo "<div class='container'><div class='alert alert-danger mt-3'>Error updating product: " . htmlspecialchars($conn->error) . "</div></div>";
             $conn->close();
        }
        // --- END: PHP Logic (Unchanged as requested) ---
    }
?>

<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
      'use strict';
      window.addEventListener('load', function () {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function (form) {
          form.addEventListener('submit', function (event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();
</script>

<?php
    // Optional: Include footer
    // include_once('./includes/footer.php');
?>