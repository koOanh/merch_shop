<?php
session_start();
require_once 'includes/config.php';
require_once __DIR__ . '/vendor/autoload.php'; // Loads PHPMailer & other dependencies

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        header("Location: forgot_password.php?status=invalid_email");
        exit;
    }

    // Check if email exists
    $stmt_check = $conn->prepare("SELECT customer_id FROM customer WHERE customer_email = ?");
    if(!$stmt_check) {
        error_log("Prepare failed (check email): " . $conn->error);
        header("Location: forgot_password.php?status=error");
        exit;
    }
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $email_exists = $result_check->num_rows > 0;
    $stmt_check->close();

    if ($email_exists) {

        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);

        // Set expiry time
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Delete any existing tokens for this email
        $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        if ($stmt_delete) {
            $stmt_delete->bind_param("s", $email);
            if (!$stmt_delete->execute()) {
                // Log error if delete fails, but continue
                error_log("Failed to delete existing token for: " . $email . " Error: " . $stmt_delete->error);
            }
            $stmt_delete->close();
        } else {
            // Log error if prepare failed
            error_log("Failed to prepare delete statement for email: " . $email . " Error: " . $conn->error);
            // Continue, maybe the insert will still work if no old token exists
        }

        // Store the token HASH and expiry in the database
        $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        if(!$stmt_insert) {
             error_log("Prepare failed (insert token): " . $conn->error);
             header("Location: forgot_password.php?status=error");
             exit;
        }

        $stmt_insert->bind_param("sss", $email, $token_hash, $expires_at);

        // Execute the INSERT statement
        if ($stmt_insert->execute()) {
            // INSERT successful, now send email
            $stmt_insert->close(); // Close insert statement

            // --- Start PHPMailer ---
            $reset_link = "http://localhost/merch/reset_password.php?token=" . $token; // Use the ORIGINAL token
            $subject = "Password Reset Request";
            $message_body = "You requested a password reset.<br><br>";
            $message_body .= "Click the following link to reset your password:<br>";
            $message_body .= "<a href='" . $reset_link . "'>" . $reset_link . "</a><br><br>";
            $message_body .= "This link will expire in 1 hour.<br><br>";
            $message_body .= "If you did not request this, please ignore this email.";

            $mail = new PHPMailer(true);
            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output if needed
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';     // Replace if using a different provider
                $mail->SMTPAuth   = true;
                $mail->Username   = '149oanh@gmail.com'; // *** REPLACE THIS ***
                $mail->Password   = 'exts zwiz caba prmf';   // *** REPLACE THIS with your App Password ***
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS
                $mail->Port       = 587;                 // Port for STARTTLS

                //Recipients
                $mail->setFrom('no-reply@yourmerchstore.com', 'Your Merch Store Name'); // Customize sender
                $mail->addAddress($email);              // Send to the user's email

                // Content
                $mail->isHTML(true);                    // Set email format to HTML
                $mail->Subject = $subject;
                $mail->Body    = $message_body;
                $mail->AltBody = strip_tags($message_body); // Plain text version

                $mail->send();
                // Email sent successfully, redirect to success page
                header("Location: forgot_password.php?status=success");
                exit; // Stop script execution

            } catch (Exception $e) {
                // PHPMailer failed to send
                error_log("Mailer Error for " . $email . ": {$mail->ErrorInfo}");
                header("Location: forgot_password.php?status=error"); // Show generic error to user
                exit; // Stop script execution
            }
            // --- End PHPMailer ---

        } else {
            // Database insert failed
             $stmt_insert->close(); // Close insert statement
            error_log("Failed to insert password reset token for: " . $email . " Error: " . $stmt_insert->error);
            header("Location: forgot_password.php?status=error&dberr=insert"); // Add extra param if needed
            exit;
        }

    } else {
        // Email doesn't exist in the customer table
        // Still redirect to success to prevent email enumeration
        header("Location: forgot_password.php?status=success");
        exit;
    }
    $conn->close(); // Close connection if script reaches here (e.g., if email didn't exist)

} else {
    // Redirect if not POST or email not set
    header("Location: forgot_password.php");
    exit;
}
?>