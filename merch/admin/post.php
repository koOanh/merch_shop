<?php
    // Include header and navigation - Assuming this outputs <!DOCTYPE html>, <html>, <head>, and starts <body>
    include_once('./includes/headerNav.php');
    // Include session restriction logic
    include_once('./includes/restriction.php');

    // Check if user is logged in, redirect if not
    if(!(isset($_SESSION['logged-in']))){
      // Redirect to login page with an indicator for unauthorized access
      header("Location: login.php?unauthorizedAccess");
      exit; // Important: Stop script execution after redirection
    }
?>

<div class="container mt-4 mb-5"> <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">PRODUCTS</h1>
        <a href="add-post.php" class="btn" style='background-color:pink;'> <i class="fa fa-plus me-2"></i>ADD Product </a>
    </div>

    <?php
        include "includes/config.php"; // Include database configuration

        /* Define how much data to show per page */
        $limit = 10; // Number of items per page

        // Determine the current page number
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        /*
        * Calculate the offset for the SQL query
        * The original switch statement for $sn based on page number seems unusual.
        * Typically, the S.No (Serial Number) is calculated based on the offset and loop index.
        * Let's calculate S.No starting from $offset + 1 within the loop.
        */
        $offset = ($page - 1) * $limit;
        $sn = $offset; // Initialize serial number base

        // Prepare SQL query based on user role
        if (isset($_SESSION["customer_role"]) && $_SESSION["customer_role"] == 'admin') {
            /* Select query for admin user - Fetches all products */
            $sql = "SELECT product_id, product_title, product_desc, product_price
                    FROM products
                    ORDER BY product_id DESC
                    LIMIT ?, ?"; // Use prepared statement placeholders
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("ii", $offset, $limit);

        } elseif (isset($_SESSION["user_role"]) && $_SESSION["user_role"] == 'normal' && isset($_SESSION['customer_name'])) {
             /* Select query for normal user - Fetches only their products */
            $sql = "SELECT product_id, product_title, product_desc, product_price
                    FROM products
                    WHERE product_author = ?
                    ORDER BY product_id DESC
                    LIMIT ?, ?"; // Use prepared statement placeholders
             $stmt = $conn->prepare($sql);
             // Bind parameters: 's' for string (author), 'i' for integer (offset, limit)
             $stmt->bind_param("sii", $_SESSION['customer_name'], $offset, $limit);
        } else {
            // Handle cases where role/session data might be missing or invalid
            echo '<div class="alert alert-warning">User role not recognized or session data missing.</div>';
            $stmt = null; // Prevent further execution
        }

        // Execute the prepared statement if it was created
        if ($stmt) {
             $stmt->execute();
             $result = $stmt->get_result(); // Get result set from prepared statement

             if ($result->num_rows > 0) {
    ?>
    <div class="table-responsive shadow-sm"> <table class="table table-striped table-hover table-bordered mb-0"> <thead class="table-light"> <tr>
                    <th scope="col" style="width: 5%;">#</th> <th scope="col">Title</th>
                    <th scope="col">Desciption</th>
                    <th scope="col">Price</th>
                    <th scope="col" class="text-center" style="width: 8%;">Edit</th> <th scope="col" class="text-center" style="width: 8%;">Delete</th> </tr>
            </thead>
            <tbody>
                <?php
                    // Loop through results and display data
                    while ($row = $result->fetch_assoc()) {
                        $sn++; // Increment serial number
                ?>
                <tr>
                    <th scope="row"><?php echo $sn; ?></th>
                    <td><?php echo htmlspecialchars($row["product_title"]); ?></td>
                    <td><?php echo htmlspecialchars($row["product_desc"]); ?></td>
                    <td><?php echo htmlspecialchars($row["product_price"]); ?></td>
                    <td class="text-center">
                        <a href="update-post.php?id=<?php echo $row["product_id"]; ?>" class="btn btn-sm " title="Edit Product">
                            <i class='fa fa-edit' style='color:pink'></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="remove-post.php?id=<?php echo $row["product_id"]; ?>" class="btn btn-sm " title="Delete Product" onclick="return confirm('Are you sure you want to delete this product?');"> <i class='fa fa-trash'></i>
                        </a>
                    </td>
                </tr>
                <?php
                    } // End while loop
                ?>
            </tbody>
        </table>
    </div><?php
            } else {
                // Display message if no products found
                echo '<div class="alert alert-info mt-3">No products found.</div>';
            }
             $stmt->close(); // Close the prepared statement
        } // End if ($stmt)

        // --- Pagination ---
        // Recalculate total products based on the *same criteria* as the main query
        // Note: Re-running a count query might be more efficient than fetching all product IDs
        $count_sql = "";
        if (isset($_SESSION["customer_role"]) && $_SESSION["customer_role"] == 'admin') {
            $count_sql = "SELECT COUNT(*) as total FROM products";
            $count_stmt = $conn->prepare($count_sql);
        } elseif (isset($_SESSION["user_role"]) && $_SESSION["user_role"] == 'normal' && isset($_SESSION['customer_name'])) {
             $count_sql = "SELECT COUNT(*) as total FROM products WHERE product_author = ?";
             $count_stmt = $conn->prepare($count_sql);
             $count_stmt->bind_param("s", $_SESSION['customer_name']);
        } else {
             $count_stmt = null;
        }

        if ($count_stmt) {
             $count_stmt->execute();
             $count_result = $count_stmt->get_result();
             $total_products = $count_result->fetch_assoc()['total'];
             $count_stmt->close();

             if ($total_products > 0 && $limit > 0) {
                 $total_page = ceil($total_products / $limit);

                 // Display pagination only if there's more than one page
                 if ($total_page > 1) {
    ?>
    <div class="d-flex justify-content-center mt-4" >
        <nav aria-label="Product navigation">
            <ul class="pagination pagination-sm" style="background-color:pink;">
                <?php
                    // Previous Page Link (Optional but good UX)
                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="post.php?page=' . ($page - 1) . '" style="color:black;">Previous</a></li>';
                    } else {
                         echo '<li class="page-item disabled"><span class="page-link" style="color:black;"> Previous</span></li>';
                    }

                    // Page Number Links
                    for ($i = 1; $i <= $total_page; $i++) {
                        $active = ($page == $i) ? "active" : "";
                        echo '<li class="page-item ' . $active . '"><a class="page-link"  style="background-color:pink; color:black;" href="post.php?page=' . $i . '">' . $i . '</a></li>';
                    }

                     // Next Page Link (Optional but good UX)
                    if ($page < $total_page) {
                         echo '<li class="page-item"><a class="page-link" style="color:black;" href="post.php?page=' . ($page + 1) . '">Next</a></li>';
                    } else {
                         echo '<li class="page-item disabled"><span class="page-link" style="color:black;">Next</span></li>';
                    }
                ?>
            </ul>
        </nav>
    </div>
    <?php
                 } // End if total_page > 1
             } // End if total_products > 0
        } // End if ($count_stmt)

        $conn->close(); // Close the database connection
    ?>

</div> <?php
    // Assuming you might have a footer include file
    // include_once('./includes/footer.php');
?>