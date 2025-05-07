
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo htmlspecialchars($_SESSION['web-name'] ?? 'Store Admin'); ?></title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
      integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Basic styles for admin nav, can be moved to a CSS file */
        body { 
            padding-top: 56px; /* Adjust if navbar is fixed-top, to prevent content overlap */
            background-color: #f4f6f9; /* Consistent admin background */
        }
        .navbar-brand h2 {
            color: black !important;
            margin-bottom: 0;
        }
        .navbar-nav .nav-link h6 {
            margin-bottom: 0; /* Removes extra space below h6 */
            color: #333; /* Default link color */
        }
        .navbar-nav .nav-link.active h6,
        .navbar-nav .nav-link:hover h6 {
            color: black !important; /* Active/hover color */
            font-weight: bold; /* Make active link bold */
        }
        /* You might have other styles here or in an external CSS file */
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top" style="text-transform: uppercase; background-color: #FFE4E1;">
      <div class="container-fluid">
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#adminNavbarNavDropdown"
          aria-controls="adminNavbarNavDropdown"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbarNavDropdown">
          <ul class="navbar-nav w-100 d-flex align-items-center">
              <div class="d-flex me-auto"> 
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''); ?>" href="dashboard.php">
                        <h6>Dashboard</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'post.php' || basename($_SERVER['PHP_SELF']) == 'add-post.php' || basename($_SERVER['PHP_SELF']) == 'update-post.php' ? 'active' : ''); ?>" href="post.php">
                        <h6>Products</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'update-user.php' ? 'active' : ''); ?>" href="users.php">
                        <h6>Users</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_orders.php' || basename($_SERVER['PHP_SELF']) == 'view_order_details.php' ? 'active' : ''); ?>" href="manage_orders.php">
                        <h6>Orders</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_reviews.php' ? 'active' : ''); ?>" href="manage_reviews.php">
                        <h6>Reviews</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_deals.php' || basename($_SERVER['PHP_SELF']) == 'add_edit_deal.php' ? 'active' : ''); ?>" href="manage_deals.php">
                        <h6>Deals</h6>
                    </a>
                </li>
              </div>

              <li class="nav-item mx-auto">
                <a class="navbar-brand" href="../index.php" >
                  <h2 style="font-weight:bold;">SABRINA CAPENTER</h2>
                </a>
              </li>

              <div class="d-flex ms-auto"> 
                 <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''); ?>" href="settings.php">
                        <h6>Settings</h6>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"> 
                        <h6>Logout</h6>
                    </a>
                </li>
              </div>
          </ul>
        </div>
      </div>
    </nav>

    