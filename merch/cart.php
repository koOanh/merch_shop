<?php // FILE MUST START EXACTLY HERE - NO SPACES/LINES BEFORE
/**
 * cart.php
 * Displays the shopping cart and handles quantity updates.
 * Adjusted error reporting to suppress Notice output.
 */

// --- Adjust Error Reporting ---
// Report all errors except Deprecated and Notice level messages during development
// In production, you might set error_reporting(0) and rely solely on logs.
// This line MUST come before session_start if session_start itself might generate a notice.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 1); // Keep errors displayed for debugging (disable in production)
ini_set('log_errors', 1); // Ensure errors are logged

// **IMPORTANT: Start the session at the very beginning, before ANY output**
if (session_status() == PHP_SESSION_NONE) {
    // Attempt to start the session
    if (!session_start()) {
        // Handle error if session cannot be started (rare, but good practice)
        error_log("Cart Page - FAILED TO START SESSION!");
        // You might want to display an error message to the user here
        // die("Error: Could not start session. Please contact support.");
    }
}


// --- Handle Cart Update ---
// Check if the form was submitted via POST and the 'update_cart' button was clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    error_log("Cart Page - Update Cart POST received: " . print_r($_POST, true)); // Log POST data

    // Check if the 'quantities' array exists in the POST data
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        // Loop through each quantity submitted
        foreach ($_POST['quantities'] as $key => $new_quantity) {
            // IMPORTANT: Validate the key exists in the cart session *before* using it
            // Also check if $_SESSION['mycart'] itself is an array
            if (isset($_SESSION['mycart']) && is_array($_SESSION['mycart']) && isset($_SESSION['mycart'][$key])) {
                // Sanitize and validate the new quantity to ensure it's an integer >= 0
                $new_quantity = filter_var($new_quantity, FILTER_VALIDATE_INT);

                if ($new_quantity !== false && $new_quantity >= 0) {
                    if ($new_quantity == 0) {
                        // Remove item from cart if quantity is set to 0
                        unset($_SESSION['mycart'][$key]);
                        error_log("Cart Page - Item removed (key: {$key}) due to quantity 0.");
                    } else {
                        // Update the quantity for the item in the session
                        $_SESSION['mycart'][$key]['product_qty'] = $new_quantity;
                        error_log("Cart Page - Item quantity updated (key: {$key}) to {$new_quantity}.");
                    }
                } else {
                     // Log if an invalid quantity value was received
                     error_log("Cart Page - Invalid quantity value received for key {$key}: " . (isset($_POST['quantities'][$key]) ? $_POST['quantities'][$key] : 'N/A'));
                }
            } else {
                 // Log if an invalid key (not matching an item in the cart) was submitted
                 error_log("Cart Page - Invalid key received in quantities update: " . $key);
            }
        }

         // Re-index the $_SESSION['mycart'] array numerically.
         // This prevents issues if items were removed, leaving gaps in the array keys.
         // Check if $_SESSION['mycart'] is still an array before calling array_values
         if (isset($_SESSION['mycart']) && is_array($_SESSION['mycart'])) {
             $_SESSION['mycart'] = array_values($_SESSION['mycart']);
         } else {
             // If the cart became empty after updates, ensure it's an empty array
             $_SESSION['mycart'] = [];
         }


    } else {
         // Log if the 'quantities' array was missing from the POST data
         error_log("Cart Page - Update Cart POST received but 'quantities' array is missing or not an array.");
    }

    // Redirect back to the cart page using a GET request.
    // This prevents the user from accidentally resubmitting the form if they refresh the page.
    header('Location: cart.php');
    exit; // Important to stop script execution after redirect header
}
// --- End Handle Cart Update ---


// Include header navigation AFTER session start and potential redirects
include_once('./includes/headerNav.php');
?>

<div class="overlay" data-overlay></div>
<header>
    <?php require_once './includes/desktopnav.php' ?>
    <?php require_once './includes/mobilenav.php'; ?>

    <style>
        /* --- Keep all your existing CSS styles here --- */
        :root{
            --main-maroon: #CE5959;
            --deep-maroon: #89375F;
            --bittersweet: #ff6961;
        }
        table {
            width: 85%; margin: 20px auto; border-collapse: collapse; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd; padding: 12px 15px; text-align: center; vertical-align: middle;
        }
        th {
            background-color: var(--main-maroon); color: white; font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        td img.cart-product-image {
            max-width: 80px; height: auto; display: block; margin-left: auto; margin-right: auto; border-radius: 4px;
        }
        /* Style for quantity input */
        td input.quantity-input {
            width: 60px; /* Adjust width as needed */
            padding: 5px 8px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Removed .delete-icon styles as the element is gone */

        .cart-total-section {
            width: 85%; margin: 20px auto; text-align: right; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;
        }
        .cart-total-section p {
            font-size: 1.2em; font-weight: bold; color: var(--main-maroon); margin: 0;
        }
        .cart-actions { /* Container for update button */
             width: 85%;
             margin: 15px auto;
             text-align: right; /* Align button to the right */
        }
        .update-cart-btn {
            padding: 10px 20px;
            background-color: var(--main-maroon);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }
        .update-cart-btn:hover {
             background-color: #6a2a4a; /* Darker shade on hover */
        }

        .child-register-btn {
            margin-top: 20px; width: 85%; margin-left: auto; margin-right: auto; padding-bottom: 30px;
        }
        .child-register-btn p {
            width: 350px; max-width: 100%; height: 60px; background-color: var(--main-maroon); box-shadow: 0px 0px 4px #615f5f; line-height: 60px; border-radius: 8px; text-align: center; cursor: pointer; margin-left: auto; margin-right: 0;
        }
        .child-register-btn p a {
            display: block; width: 100%; height: 100%; color: #FFFFFF; text-decoration: none; font-size: 19px; font-weight: 600;
        }

        @media screen and (max-width: 794px) {
            .child-register-btn { margin-top: 30px; }
            .child-register-btn p { width: 100%; margin-left: auto; margin-right: auto; }
            table, .cart-total-section, .child-register-btn, .cart-actions { width: 95%; }
            th, td { padding: 8px 10px; }
            td img.cart-product-image { max-width: 60px; }
            td input.quantity-input { width: 50px; }
        }
         @media screen and (max-width: 480px) {
            th, td { font-size: 0.9em; }
            .child-register-btn p a { font-size: 16px; }
            .update-cart-btn { width: 100%; margin-top: 10px;} /* Full width update button */
            .cart-actions { text-align: center; } /* Center button on mobile */
        }
    </style>
</header>

<main>
    <div class="product-container">
        <div class="container">
            <form action="cart.php" method="post" id="update-cart-form">
                <table>
                    <thead> <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody> <?php
                        $grand_total = 0; // Initialize grand total
                        $currency_code = "USD"; // Define currency code

                        // Check if cart session variable exists and is an array
                        if (!empty($_SESSION['mycart']) && is_array($_SESSION['mycart'])) {
                            // Loop through each item in the cart
                            foreach ($_SESSION['mycart'] as $key => $value) {
                                // Validate item data before using it
                                $price = isset($value['price']) ? filter_var($value['price'], FILTER_VALIDATE_FLOAT) : false;
                                // Ensure quantity is at least 1 for calculation/display if price is valid
                                $quantity = (isset($value['product_qty']) && filter_var($value['product_qty'], FILTER_VALIDATE_INT) !== false && $value['product_qty'] >= 1)
                                            ? (int)$value['product_qty']
                                            : 1;
                                $name = isset($value['name']) ? htmlspecialchars($value['name']) : 'Unknown Item';
                                $img = isset($value['product_img']) ? htmlspecialchars($value['product_img']) : '';

                                // Only process/display the row if the price is valid
                                if ($price !== false) {
                                    $sub_total = $price * $quantity;
                                    $grand_total += $sub_total; // Add item subtotal to grand total
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($img)): ?>
                                            <img class="cart-product-image"
                                                 src="./admin/upload/<?php echo $img; ?>"
                                                 alt="<?php echo $name; ?>"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <span style="display:none;">Img N/A</span>
                                            <?php else: ?>
                                            <span>No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $name; ?></td>
                                        <td><?php echo "$" . number_format($price, 2); ?></td>
                                        <td>
                                            <input type="number"
                                                   class="quantity-input"
                                                   name="quantities[<?php echo $key; ?>]"
                                                   value="<?php echo $quantity; ?>"
                                                   min="0" required> </td>
                                        <td><?php echo "$" . number_format($sub_total, 2); ?></td>
                                        </tr>
                                    <?php
                                } else {
                                    // Display a message for items with invalid price data
                                    ?>
                                    <tr>
                                        <td colspan="5">Invalid item data (missing price) for '<?php echo $name; ?>'. This item was not included in total.</td> </tr>
                                    <?php
                                    error_log("Cart Page - Invalid item data (price missing): " . print_r($value, true));
                                }
                            } // end foreach

                            // --- Update Session Variables After Calculating Total ---
                            // Store the final calculated total and currency code in the session
                            // This happens AFTER the loop, so it reflects the current state displayed
                            $_SESSION['cart_total'] = $grand_total;
                            $_SESSION['currency_code'] = $currency_code;
                            // Optional: Log the final values being stored
                            // error_log("Cart Page - Final session values set: Grand Total = " . $_SESSION['cart_total'] . ", Currency = " . $_SESSION['currency_code']);

                        } else { // Cart session variable is empty or not an array
                            // Ensure cart is treated as empty
                            $_SESSION['mycart'] = []; // Initialize as empty array if not set or invalid
                            ?>
                            <tr>
                                <td colspan="5">No items available in cart</td> </tr> <?php
                               // Ensure total/currency session variables are unset if cart is empty
                               if (isset($_SESSION['cart_total'])) unset($_SESSION['cart_total']);
                               if (isset($_SESSION['currency_code'])) unset($_SESSION['currency_code']);
                               error_log("Cart Page - Cart was initially empty or invalid, session variables unset.");
                        }
                        ?>
                    </tbody>
                </table>

                <?php if (!empty($_SESSION['mycart'])): ?>
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="update-cart-btn">Update Cart</button>
                </div>
                <?php endif; ?>

            </form> <?php // Display total section only if cart total is valid in session
            // Use the session variable as it holds the definitive total calculated above
            if (isset($_SESSION['cart_total']) && $_SESSION['cart_total'] > 0): ?>
            <div class="cart-total-section">
                <p>Grand Total: <?php echo "$" . number_format($_SESSION['cart_total'], 2); ?></p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php // Display checkout button only if cart total is valid in session
    if (isset($_SESSION['cart_total']) && $_SESSION['cart_total'] > 0): ?>
        <div class="child-register-btn">
            <p> <a href="checkout.php">Proceed to Checkout</a> </p>
        </div>
    <?php endif; ?>

</main>

<?php require_once './includes/footer.php'; ?>