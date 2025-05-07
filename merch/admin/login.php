<?php
    // DO NOT include restriction.php here.
    // Session should be started if you intend to set session variables upon successful login.
    // If your main login logic sets session variables, ensure session_start() is called there
    // or at the very beginning of this script if not handled by an include before form processing.
    // For now, we'll assume the login processing block below will handle session_start if needed.
    // If restriction.php was the only place starting the session, you might need to add session_start() here
    // if you access $_SESSION before the login processing block.
    // However, typically, session_start() is best at the very top of scripts that use sessions.
    // Let's add it here to be safe for the $_SESSION['logged-in'] part in the login success.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
      integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
      crossorigin="anonymous"
    />
    
    <title>Admin Login</title> <style>
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }
      body {
        display: flex;
        flex-direction: column;
        height: 100vh;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa; /* Light background */
      }
      form {
        border: 1px solid #dee2e6; /* Softer border */
        width: 400px;
        padding: 35px; /* Increased padding */
        border-radius: 10px;
        background-color: #fff; /* White background for form */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Subtle shadow */
      }
      .logo-box {
        padding-bottom: 20px; /* Space below logo */
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
      }
      .logo-box img { /* If you add a logo image */
          max-width: 150px;
          margin-bottom: 10px;
      }
      h4{
        text-align: center;
        margin-bottom: 25px; /* Increased margin */
        color: #495057; /* Darker grey for heading */
      }
      .btn-pink { /* Custom class for pink button */
          background-color: pink;
          border-color: pink;
          color: black; /* Ensure text is visible */
      }
      .btn-pink:hover {
          background-color: #ff85a2; /* Slightly darker pink on hover */
          border-color: #ff85a2;
          color: black;
      }
      .alert { /* Style for error messages */
          width: 400px; /* Match form width */
          margin-top: 15px;
          text-align: center;
      }
    </style>
  </head>
  <body>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method ="POST">
      <h4>Admin Login</h4>
      <div class="row mb-3">
        <div class="col-sm-12">
          <input
            id="inputEmail"
            name="userEmail"
            type="email"
            class="form-control"
            placeholder="Email"
            required
          />
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-sm-12">
          <input
            id="inputPassword"
            name="password"
            type="password"
            class="form-control"
            placeholder="Password"
            required
          />
        </div>
      </div>

      <div class="d-grid"> <button 
        type="submit" 
        name="login" 
        class="btn btn-pink" 
        >
            Sign in
        </button>
      </div>
    </form>

    <?php 
        if (isset($_POST['login'])) {
            // Ensure config.php is in the correct path relative to admin/login.php
            require_once "includes/config.php"; // Use require_once

            if (empty($_POST['userEmail']) || empty($_POST['password'])) {
                echo '<div class="alert alert-danger">All Fields must be entered.</div>';
                // die(); // Avoid die() if you want the page to render further
            } else {
                $email = mysqli_real_escape_string($conn, $_POST['userEmail']);
                $password = $_POST['password']; // Password will be verified with password_verify

                // Fetch user by email, including their role and hashed password
                $sql = "SELECT customer_id, customer_fname, customer_pwd, customer_role FROM customer WHERE customer_email = ?";
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        // Verify password and check if role is 'admin'
                        if (password_verify($password, $row['customer_pwd']) && $row['customer_role'] === 'admin') {
                            // Password is correct and user is an admin
                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            $_SESSION['logged-in'] = '1'; // General admin logged-in flag
                            $_SESSION['admin_id'] = $row['customer_id']; // Store admin specific ID if needed
                            $_SESSION['admin_name'] = $row['customer_fname'];
                            $_SESSION['customer_role'] = $row['customer_role']; // Set the role for restriction.php

                            header("Location: ./post.php"); // Redirect to admin dashboard
                            exit;
                        } else {
                            // Invalid password or not an admin
                            echo '<div class="alert alert-danger">Username, Password, or Role is incorrect.</div>';
                        }
                    } else {
                        // Email not found
                        echo '<div class="alert alert-danger">Username, Password, or Role is incorrect.</div>';
                    }
                    $stmt->close();
                } else {
                    // SQL statement preparation failed
                    error_log("Admin login SQL prepare failed: " . $conn->error);
                    echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                }
                $conn->close();
            }
        }
    ?>
  </body>
</html>
