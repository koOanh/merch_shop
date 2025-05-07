<?php
require_once 'includes/config.php';
$token_valid = false;
$error_message = '';
$token_from_url = '';

if (isset($_GET['token'])) {
    $token_from_url = $_GET['token'];
    $token_hash = hash('sha256', $token_from_url); // Hash the token from URL for DB lookup

    // Prepare statement to find the token hash
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $expires_at = strtotime($row['expires_at']);
        $current_time = time();

        if ($expires_at > $current_time) {
            // Token is valid and not expired
            $token_valid = true;
        } else {
            $error_message = "This password reset link has expired.";
            // Optionally delete the expired token
            $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt_delete->bind_param("s", $token_hash);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    } else {
        $error_message = "Invalid password reset link.";
    }
    $stmt->close();
} else {
    $error_message = "No reset token provided.";
}

?>

<?php include_once('./includes/headerNav.php'); ?>

<div class="container" style="max-width: 500px; margin-top: 50px;">
    <h2 class="text-center">Reset Password</h2>

    <?php if ($token_valid): ?>
        <p class="text-center text-muted">Enter your new password below.</p>

         <?php
        // Display messages if redirected here after submission attempt
        if (isset($_GET['status']) && $_GET['status'] == 'pwd_mismatch') {
            echo '<div class="alert alert-danger">Passwords do not match.</div>';
        } elseif (isset($_GET['status']) && $_GET['status'] == 'weak_pwd') {
            echo '<div class="alert alert-danger">Password should be at least 8 characters long.</div>';
        } elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
             echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        }
        ?>

        <form action="handle_reset_password.php" method="POST" class="mt-4">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_from_url); ?>">

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                <div class="form-text">Must be at least 8 characters long.</div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn" style="background-color: pink;">Reset Password</button>
            </div>
        </form>

    <?php else: ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <div class="text-center mt-3">
            <a href="forgot_password.php">Request a new reset link</a> |
            <a href="login.php">Back to Login</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once './includes/footer.php'; ?>