<?php
    include_once('./includes/headerNav.php'); // Assuming this includes <head>, CSS links, and starts <body>
    include_once('./includes/restriction.php');
    if(!(isset($_SESSION['logged-in']))){
      header("Location:login.php?unauthorizedAccess");
      exit; // Important: Stop script execution after redirection
    }
?>

<style>
  .icon-action {
    color: #E91E63; /* Example pink color */
    /* Or use Bootstrap text color class like text-danger, text-warning if suitable */
  }
</style>

<div class="container mt-4 mb-5"> <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">USERS</h1>
        </div>

    <?php
        include "includes/config.php"; // Include database configuration

        /* Define how much data to show in a page from database */
        $limit = 4; // Items per page

        // --- START: PHP Logic (Unchanged as requested) ---
        if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0){
          $page = (int)$_GET['page'];
          // NOTE: This switch logic for $sn is unusual. Typically $sn is calculated based on $offset + loop index.
          switch($page){
            case 1: $sn = 0; break;
            case 2: $sn = 4;break;
            case 3: $sn = 8; break;
            case 4: $sn = 12; break;
            case 5: $sn = 16; break;
            case 6: $sn = 20; break;
            // Consider adding a default case or handling pages beyond 6 if necessary
            default: $sn = ($page - 1) * $limit; break; // Example fallback
          }
        }else{
          $page = 1;
          $sn = 0; // Reset sn for page 1 according to original logic
        }
        // Define from which row to start extracting data from database
        $offset = ($page - 1) * $limit;

        // SECURITY WARNING: This query is vulnerable to SQL Injection without prepared statements.
        $sql = "SELECT customer_id, customer_fname, customer_phone, customer_address, customer_role FROM customer LIMIT {$offset},{$limit}";
        // --- END: PHP Logic (Unchanged as requested) ---

        // Execute Query (Assuming $conn is a valid MySQLi connection from config.php)
        $result = $conn->query($sql);

        // Check if query was successful and returned rows
        if ($result && $result->num_rows > 0) {
    ?>
    <div class="table-responsive shadow-sm"> <table class="table table-striped table-hover table-bordered mb-0"> <thead class="table-light"> <tr>
                    <th scope="col" style="width: 5%;">#</th> <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Address</th>
                    <th scope="col" style="width: 10%;">Role</th>
                    <th scope="col" class="text-center" style="width: 8%;">Edit</th> <th scope="col" class="text-center" style="width: 8%;">Delete</th> </tr>
            </thead>
            <tbody>
                <?php
                    // Loop through results and display data
                    // --- START: PHP Logic (Unchanged as requested) ---
                    while($row = $result->fetch_assoc()) {
                        $sn = $sn + 1; // Increment serial number based on original logic
                    // --- END: PHP Logic (Unchanged as requested) ---
                ?>
                <tr>
                    <th scope="row"><?php echo $sn; ?></th>
                    <td><?php echo htmlspecialchars($row["customer_fname"]); // SECURITY: Sanitize output ?></td>
                    <td><?php echo htmlspecialchars($row["customer_phone"]); // SECURITY: Sanitize output ?></td>
                    <td><?php echo htmlspecialchars($row["customer_address"]); // SECURITY: Sanitize output ?></td>
                    <td><?php echo htmlspecialchars($row["customer_role"]); // SECURITY: Sanitize output ?></td>
                    <td class="text-center">
                        <a href="update-user.php?id=<?php echo $row["customer_id"]; ?>" class="btn btn-sm " title="Edit User">
                            <i class='fa fa-edit' style='color:pink'></i> </a>
                    </td>
                    <td class="text-center">
                        <a href="remove-user.php?id=<?php echo $row["customer_id"]; ?>" class="btn btn-sm" title="Delete User" onclick="return confirm('Are you sure you want to delete this user?');">
                            <i class='fa fa-trash' style='color:pink'></i> </a>
                    </td>
                </tr>
                <?php
                    } // End while loop
                ?>
            </tbody>
        </table>
    </div><?php
        } else {
            // Display message if no users found or if query failed
             if ($result && $result->num_rows == 0) {
                echo '<div class="alert alert-info mt-3">No users found.</div>';
             } elseif (!$result) {
                 echo '<div class="alert alert-danger mt-3">Error executing query: ' . htmlspecialchars($conn->error) . '</div>'; // Show error if query failed
             }
        }

        // Close the initial result set if it exists
        if ($result) {
            $result->free();
        }

        // --- Pagination ---
        // NOTE: Re-including config.php might be unnecessary or cause issues if not handled carefully.
        // Ensure $conn is still valid or re-establish connection if needed.
        // include "includes/config.php"; // This might re-declare $conn or cause errors. Check config.php behavior.

        // --- START: PHP Logic (Unchanged as requested) ---
        // EFFICIENCY WARNING: Selecting all columns (*) just to count is inefficient. Use SELECT COUNT(*) instead.
        $sql1 = "SELECT customer_id FROM customer"; // Select only one column for counting if not using COUNT(*)
        $result1 = mysqli_query($conn, $sql1); // Assuming mysqli_* functions are used alongside $conn->query()

        if ($result1 && mysqli_num_rows($result1) > 0) {
            $total_users = mysqli_num_rows($result1);
            $total_page = ceil($total_users / $limit);
         // --- END: PHP Logic (Unchanged as requested) ---

            // Display pagination only if there's more than one page
            if ($total_page > 1) {
    ?>
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="User navigation">
            <ul class="pagination pagination-sm">
                <?php
                   // Previous Page Link (Optional)
                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>'; // Use '?' if it's the same page
                    } else {
                         echo '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
                    }

                    // --- START: PHP Logic (Unchanged as requested) ---
                    for($i = 1; $i <= $total_page; $i++) {
                        if ($page == $i) {
                            $active = "active";
                        } else {
                            $active = "";
                        }
                    // --- END: PHP Logic (Unchanged as requested) ---
                ?>
                    <li class="page-item <?php echo $active; ?>">
                        <a class="page-link" href="?page=<?php echo $i; // Use '?' if it's the same page ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php
                    } // End for loop

                   // Next Page Link (Optional)
                   if ($page < $total_page) {
                       echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>'; // Use '?' if it's the same page
                    } else {
                       echo '<li class="page-item disabled"><span class="page-link">Next</span></li>';
                    }
                ?>
            </ul>
        </nav>
    </div>
    <?php
            } // End if total_page > 1
        } // End if $result1 check

        // Close the count result set if it exists
        if ($result1) {
            mysqli_free_result($result1);
        }
        // --- END: PHP Logic (Unchanged as requested) ---

        $conn->close(); // Close the database connection
    ?>

</div> <?php
    // Assuming you might have a footer include file for closing body/html tags and JS includes
    // include_once('./includes/footer.php');
?>