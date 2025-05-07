<?php
// Near the top of login.php
if (isset($_GET['status']) && $_GET['status'] == 'reset_success') {
    echo '<div class="container" style="margin-top: 20px;"><div class="alert alert-success text-center">Password has been reset successfully. You can now log in.</div></div>';
}
?>
<?php  session_start();
 include_once 'includes/config.php';
//  all functions
require_once 'functions/functions.php';

 //run whenever this file is used no need of isset or any condition to get website image footer etc
 $sql5 ="SELECT * FROM  settings;";
 $result5 = $conn->query($sql5);
 $row5 = $result5->fetch_assoc();
 $_SESSION['web-name'] = $row5['website_name'];
 $_SESSION['web-img'] = $row5['website_logo'];
 $_SESSION['web-footer'] = $row5['website_footer'];
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
    <title>Login(USER)</title>
    <style>
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
      }
      form {
        border: 1px solid red;
        width: 400px;
        padding: 25px;
        border-radius: 10px;
      }
      .logo-box {
        padding: 10px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
      }
      #signup-btn {
        text-decoration: none;
        color: white;
      }
    </style>
  </head>
  <body>

  	 <?php 
     if( !( isset( $_SESSION['id']))){
     ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?> " method="post">
      
<h4 style="text-align:center"> Login</h4>
      <div class="row mb-3">
        <!-- <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label> -->
        <div class="col-sm-12">
          <input
            id="inputEmail"
            name="email"
            type="email"
            class="form-control"
            placeholder="Email"
          />
        </div>
      </div>
      <div class="row mb-3">
        <!-- <label for="inputPassword3" class="col-sm-2 col-form-label"
          >Password</label
        > -->
        <div class="col-sm-12">
          <input
            id="inputPassword"
            name="pwd"
            type="password"
            class="form-control"
            placeholder="Password"
          />
        </div>
      </div>

      <div style="float: right">
        <button 
        type="submit" 
        class="btn"
style="background-color:pink;">
        <a href="./signup.php" id="signup-btn" style='color:black'>
             Sign up
		</a>
           
        </button>

        <button 
        type="submit" 
        class="btn "
        name="login"
	style="background-color:pink;">
            Sign in
        </button>
      </div>
<div class="text-center mt-3">
    <a href="forgot_password.php">Forgot Password?</a>
</div>
    </form>

	<?php }?>

    
	<?php
 //1st step(i.e connection) done through config file
if(isset($_POST['login'])){

    if(empty($_POST['email'])){
           echo "<h4 id='error_login'>Enter email</h4>";
    }

    if(empty($_POST['pwd'])){
        echo "<h4 id='error_login'>Enter password</h4>";
 }

$email = mysqli_real_escape_string($conn,$_POST['email']);
$password =mysqli_real_escape_string($conn,$_POST['pwd']);

$sql ="SELECT * FROM  customer WHERE customer_email='{$email}';";
$result = $conn->query($sql);

// Inside the login check, after fetching the user row
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    // Verify the submitted password against the stored hash
    if (password_verify($password, $row['customer_pwd']) && $row['customer_role'] === 'normal') { // Use password_verify
        // Passwords match! Create session.
        session_start();
        $_SESSION['id'] = $row['customer_id'];
        $_SESSION['customer_role'] = $row['customer_role'];
        // Store other details needed, like name
        $_SESSION['customer_name'] = $row['customer_fname'];
        $_SESSION['customer_email'] = $row['customer_email'];
        $_SESSION['customer_phone'] = $row['customer_phone'];
        $_SESSION['customer_address'] = $row['customer_address'];

        header("location:index.php?id={$_SESSION['id']}");
        exit; // Add exit
    } else {
        // Password doesn't match
        echo "<h4 id='error_login' style='color:red; text-align:center;'>Incorrect email or password.</h4>";
    }
} else {
    // User email not found
    echo "<h4 id='error_login' style='color:red; text-align:center;'>Incorrect email or password.</h4>";
}
}//end of 1st ifstatement

?>


  </body>
</html>
