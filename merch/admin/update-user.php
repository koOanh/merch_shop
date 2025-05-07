<?php
    // --- PHP Includes & Data Fetching (Original PHP - with notes) ---
    include_once('./includes/headerNav.php'); // Assumes standard setup
    include_once('./includes/restriction.php');

    // NOTE: Using $_SESSION to pass temporary form data is unconventional.
    // NOTE: The SQL query below is VULNERABLE to SQL Injection. Use Prepared Statements.
    include "includes/config.php";
    if (isset($_GET['id']) && is_numeric($_GET['id'])) { // Basic validation
        $user_id = $_GET['id'];
        // **VULNERABLE SQL:** Replace with Prepared Statement
        $sql = "SELECT customer_fname, customer_phone, customer_address, customer_role FROM customer WHERE customer_id={$user_id}";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Store in session (original logic)
            $_SESSION['previous_name'] = $row['customer_fname'];
            $_SESSION['previous_phone'] = $row['customer_phone'];
            $_SESSION['previous_address'] = $row['customer_address'];
            $_SESSION['previous_role'] = $row['customer_role'];
        } else {
            echo "<div class='container'><div class='alert alert-danger'>Error: User not found or invalid ID.</div></div>";
            // include_once('./includes/footer.php'); // Optional
            exit;
        }
        $result->free();
    } else {
        echo "<div class='container'><div class='alert alert-danger'>Error: Invalid or missing user ID.</div></div>";
        // include_once('./includes/footer.php'); // Optional
        exit;
    }
    $conn->close();
    // --- End PHP Includes & Data Fetching ---
?>
<!-- Assume CSS moved to external file -->

<!-- Wrapper for centering -->
<div class="container main-content-wrapper py-4 " style="display: flex; justify-content: center; align-items: center;">
    <div class="card shadow-sm" style="max-width: 700px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <h1 class="card-title text-center mb-4">Update User Details</h1>

            <!-- Form -->
            <!-- Changed action to "", added validation -->
            <form action="" method="POST" class="row g-3 needs-validation" novalidate>
                 <!-- Optional: Hidden field for ID -->
                 <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                <!-- Name -->
                <div class="col-md-6">
                    <label for="userName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="userName" name="name" value="<?php echo htmlspecialchars($_SESSION['previous_name'] ?? ''); ?>" required>
                    <div class="invalid-feedback">Please enter a name.</div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <label for="userPhone" class="form-label">Phone</label>
                    <!-- Changed type to tel -->
                    <input type="tel" class="form-control" id="userPhone" name="phone" value="<?php echo htmlspecialchars($_SESSION['previous_phone'] ?? ''); ?>" required>
                     <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>

                <!-- Address -->
                <div class="col-12">
                    <label for="userAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="userAddress" name="address" placeholder="1234 Main St, City" value="<?php echo htmlspecialchars($_SESSION['previous_address'] ?? ''); ?>" required>
                     <div class="invalid-feedback">Please enter an address.</div>
                </div>

                <!-- Role -->
                <div class="col-md-6"> <!-- Adjusted column width -->
                    <label for="userRole" class="form-label">Role</label>
                    <!-- Changed ID to match label -->
                    <select id="userRole" name="role" class="form-select" required>
                        <option value="" disabled>Choose...</option>
                         <!-- Kept original PHP logic for pre-selection -->
                        <?php
                            $current_role = $_SESSION['previous_role'] ?? 'normal'; // Default if not set
                            if($current_role == 'admin'){
                        ?>
                                <option value="admin" selected>Admin</option>
                                <option value="normal">Normal</option>
                        <?php } else { ?>
                                <option value="admin">Admin</option>
                                <option value="normal" selected>Normal</option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">Please select a role.</div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-lg" style="background-color:pink;" name="update">Update User</button>
                     <a href="users.php" class="btn btn-secondary btn-lg ms-2">Cancel</a> <!-- Link to user list -->
                </div>
            </form>
            <!--/Form -->
        </div> <!-- /.card-body -->
    </div> <!-- /.card -->
</div> <!-- /.container / wrapper -->


<?php
    // --- PHP Update Logic (Original PHP - with notes) ---
    if(isset($_POST['update'])){
        // NOTE: Connection was closed, re-including config is necessary.
        // NOTE: The SQL query below is VULNERABLE to SQL Injection. Use Prepared Statements.
        // --- START: PHP Logic (Unchanged as requested, but VULNERABLE) ---
        include "includes/config.php";

        // **VULNERABLE SQL:** Replace with Prepared Statement
        $sql1 = "UPDATE customer
                 SET  customer_fname= '{$_POST['name']}' ,
                      customer_phone= '{$_POST['phone']}' ,
                      customer_address= '{$_POST['address']}' ,
                      customer_role= '{$_POST['role']}'
                 WHERE customer_id={$_GET['id']} "; // Using $_GET['id'] is vulnerable

        if ($conn->query($sql1)) { // Check success
             $conn->close();
             // NOTE: Hardcoded localhost URL. Redirect target goes back to update form - consider redirecting to user list instead.
             // NOTE: Missing exit; after header() call.
             // Redirect to users list might be better: header("Location: users.php?update=success");
             header("Location: users.php?succesfullyUpdated=" . urlencode($_GET['id'] ?? '')); // Redirect to user list, indicate success
             exit; // **CRITICAL: Add exit here**
        } else {
             // Handle query error
             echo "<div class='container'><div class='alert alert-danger mt-3'>Error updating user: " . htmlspecialchars($conn->error) . "</div></div>";
             $conn->close();
        }
        // --- END: PHP Logic (Unchanged as requested) ---
    }
?>

<!-- Include Bootstrap JS and validation script if not already done -->
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