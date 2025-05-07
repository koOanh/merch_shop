<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {

    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if ($new_password !== $confirm_password) {
        header("Location: reset_password.php?token=" . urlencode($token) . "&status=pwd_mismatch");
        exit;
    }
    if (strlen($new_password) < 8) { // Enforce minimum length
         header("Location: reset_password.php?token=" . urlencode($token) . "&status=weak_pwd");
        exit;
    }

    $token_hash = hash('sha256', $token);

    // Validate token again (exists and not expired)
    $stmt_check = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt_check->bind_param("s", $token_hash);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 1) {
        $row = $result_check->fetch_assoc();
        $expires_at = strtotime($row['expires_at']);
        $current_time = time();
        $email = $row['email'];

        if ($expires_at > $current_time) {
            // Token is valid, proceed to update password

            // ** Hash the new password **
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the customer table
            $stmt_update = $conn->prepare("UPDATE customer SET customer_pwd = ? WHERE customer_email = ?");
            $stmt_update->bind_param("ss", $hashed_password, $email);

            if ($stmt_update->execute()) {
                // Password updated successfully

                // Delete the used token
                $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                 // You could also delete by token hash for extra safety if email isn't unique across roles
                $stmt_delete->bind_param("s", $email);
                $stmt_delete->execute();
                $stmt_delete->close();

                // Redirect to login page with success message
                header("Location: login.php?status=reset_success");
                exit;

            } else {
                // Database update failed
                error_log("Failed to update password for: " . $email . " Error: " . $stmt_update->error);
                 header("Location: reset_password.php?token=" . urlencode($token) . "&status=error");
                exit;
            }
            $stmt_update->close();

        } else {
            // Token expired between loading the form and submitting
            header("Location: reset_password.php?token=" . urlencode($token) . "&status=expired");
            exit;
        }
    } else {
        // Token not found in DB
        header("Location: reset_password.php?token=" . urlencode($token) . "&status=invalid");
        exit;
    }
    $stmt_check->close();
    $conn->close();

} else {
    // Redirect if accessed improperly
    header("Location: login.php");
    exit;
}
?>