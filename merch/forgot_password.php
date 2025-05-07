<?php include_once('./includes/headerNav.php'); // Use your standard header ?>

<div class="container" style="max-width: 500px; margin-top: 50px;">
    <h2 class="text-center">Forgot Password</h2>
    <p class="text-center text-muted">Enter your email address and we'll send you a link to reset your password.</p>

    <?php
    // Display messages if redirected here (from handle_forgot.php)
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo '<div class="alert alert-success">If an account exists for that email, a password reset link has been sent. Please check your inbox (and spam folder).</div>';
    } elseif (isset($_GET['status']) && $_GET['status'] == 'error') {
        echo '<div class="alert alert-danger">Could not process request. Please try again later.</div>';
    } elseif (isset($_GET['status']) && $_GET['status'] == 'invalid_email') {
         echo '<div class="alert alert-danger">Please enter a valid email address.</div>';
    }
    ?>

    <form action="handle_forgot_password.php" method="POST" class="mt-4">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn" style="background-color: pink;">Send Reset Link</button>
        </div>
         <div class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </form>
</div>

<?php require_once './includes/footer.php'; // Use your standard footer ?>