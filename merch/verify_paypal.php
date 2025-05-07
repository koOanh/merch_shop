<?php
/**
 * verify_paypal.php
 *
 * Server-side script to securely verify a PayPal transaction after client-side approval.
 * Compatible with PHP versions prior to 7.0 (removes null coalescing operator).
 * Updated to replace deprecated FILTER_SANITIZE_STRING.
 * Adjusted error reporting to suppress Deprecated/Notice output.
 */

// --- Composer Autoloader ---
require __DIR__ . '/vendor/autoload.php'; // Use __DIR__ for reliability

// --- Use PayPal SDK Namespaces ---
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;    // For testing
use PayPalCheckoutSdk\Core\ProductionEnvironment; // For live
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException; // Import HttpException

// --- Configuration ---
// Load your PayPal API Credentials securely
$clientId = getenv('PAYPAL_CLIENT_ID') ?: 'AYn-gDaUjFZxow-e1hYvzPZIO2VnHTnHlbvM8JYRBi_JTGi-KXpkHyjRinCNfs9Kqgs7rlGY4i35D8Tr';
$clientSecret = getenv('PAYPAL_CLIENT_SECRET') ?: 'EDHK4qpD7nKYc66qyw8_4V46CAWdc1zk4yyG6qP_qEfNG4-dwsLxkexryd8BH9_eeRh6bjtkV5HyY5Gp';

// --- Database Configuration ---
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'db_merchshop';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbCharset = 'utf8mb4';

// --- Determine PayPal Environment ---
$isProduction = false; // Set to true for live

if ($isProduction) {
    $environment = new ProductionEnvironment($clientId, $clientSecret);
    // In production, disable error display completely and rely on logs
    ini_set('display_errors', 0);
    error_reporting(0); // Or a level like E_ALL & ~E_DEPRECATED & ~E_STRICT
    ini_set('log_errors', 1); // Ensure errors are logged
    // ini_set('error_log', '/path/to/your/production_php_errors.log'); // Optional: Set specific log file
} else {
    // Use Sandbox environment and credentials for testing
    $environment = new SandboxEnvironment($clientId, $clientSecret);
    // Keep errors visible for debugging in sandbox, but hide Deprecated/Notices from output
    ini_set('display_errors', 1);
    // Report all errors except Deprecated and Notice level messages
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE); // <-- MODIFIED LINE
    ini_set('log_errors', 1); // Still log all errors, including deprecated/notices
}

$client = new PayPalHttpClient($environment);

// --- Start Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Session Debugging (Can be removed once working) ---
// error_log("Verify PayPal Script Start - Session Data: " . print_r($_SESSION, true));
// --- End Session Debugging ---


// --- Set Header ---
// **IMPORTANT**: Ensure this header is sent *before* any other output
header('Content-Type: application/json');

// --- Default Response ---
$response = ['success' => false, 'message' => 'Verification initialization failed.'];

// --- Get Data from Client ---
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody);

// Basic validation
if (!$data || !isset($data->orderID) || !isset($data->email_address)) {
    $response['message'] = 'Invalid or incomplete data received from client.';
    error_log("PayPal Verify Error: Invalid data received. Payload: " . $requestBody);
    echo json_encode($response);
    exit;
}

// Use recommended filter or FILTER_DEFAULT for basic sanitization
$orderID = filter_var($data->orderID, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Updated filter
$customerEmail = filter_var($data->email_address, FILTER_VALIDATE_EMAIL);

if (!$customerEmail) {
     $response['message'] = 'Invalid email address received from client.';
     error_log("PayPal Verify Error: Invalid email. Payload: " . $requestBody);
     echo json_encode($response);
     exit;
}

// --- Get Expected Amount/Currency from Session ---
$expectedAmount = null;
$expectedCurrency = 'USD';

// Log values just before checking them (Can be removed once working)
// error_log("Verify PayPal - Checking Session: cart_total = " . (isset($_SESSION['cart_total']) ? $_SESSION['cart_total'] : 'Not Set') . ", currency_code = " . (isset($_SESSION['currency_code']) ? $_SESSION['currency_code'] : 'Not Set'));


if (isset($_SESSION['cart_total']) && is_numeric($_SESSION['cart_total'])) {
    $expectedAmount = number_format((float)$_SESSION['cart_total'], 2, '.', '');
}
if (isset($_SESSION['currency_code']) && !empty($_SESSION['currency_code'])) {
    // Sanitize currency code as well
    $expectedCurrency = filter_var($_SESSION['currency_code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Updated filter
}

// This is the check that previously failed
if ($expectedAmount === null || $expectedAmount <= 0) {
     $response['message'] = 'Could not retrieve valid expected cart total from session.';
     // Log the error that gets reported
     error_log("PayPal Verify Error: Cart total missing or invalid in session for Order ID: " . $orderID . ". Expected Amount derived as: " . var_export($expectedAmount, true));
     echo json_encode($response);
     exit;
}

// --- Call PayPal API ---
$request = new OrdersGetRequest($orderID);

try {
    $apiResponse = $client->execute($request);
    $orderDetails = $apiResponse->result;

    $paymentStatus = $orderDetails->status;
    // Access amount/currency safely for older PHP
    $paidAmount = null;
    if (isset($orderDetails->purchase_units[0]->amount->value)) {
        $paidAmount = $orderDetails->purchase_units[0]->amount->value;
    }
    $paidCurrency = null;
    if (isset($orderDetails->purchase_units[0]->amount->currency_code)) {
         $paidCurrency = $orderDetails->purchase_units[0]->amount->currency_code;
    }

    // Get transaction ID safely for older PHP
    $transactionId = null;
    if (isset($orderDetails->purchase_units[0]->payments->captures[0]->id)) {
        $transactionId = $orderDetails->purchase_units[0]->payments->captures[0]->id;
    } else {
        error_log("PayPal Verify Warning: Capture ID not found in expected location for Order ID: " . $orderID);
        $transactionId = $orderDetails->id; // Fallback
    }

    // Log check results (Can be removed once working)
    // error_log("PayPal Verify Check: OrderID: {$orderID}, Status: {$paymentStatus}, Paid: {$paidAmount} {$paidCurrency}, Expected: {$expectedAmount} {$expectedCurrency}, TxID: {$transactionId}");

    // --- Verification Checks ---
    if ($paymentStatus == 'COMPLETED') {
         if ($paidAmount == $expectedAmount && $paidCurrency == $expectedCurrency) {
            // --- SUCCESS ---
            try {
                $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

                $sql = "INSERT INTO `orders` (
                            customer_email, customer_fname, customer_lname,
                            customer_phone, address_house, address_street,
                            address_city, address_postcode, address_country,
                            paypal_order_id, paypal_transaction_id,
                            amount, currency, order_status, order_date
                        ) VALUES (
                            :email, :fname, :lname,
                            :phone, :house, :street,
                            :city, :postcode, :country,
                            :paypal_order_id, :paypal_tx_id,
                            :amount, :currency, :status, NOW()
                        )";

                $stmt = $pdo->prepare($sql);

                // Bind parameters safely for older PHP using isset() and ternary
                // Use FILTER_SANITIZE_FULL_SPECIAL_CHARS or FILTER_DEFAULT for basic sanitization
                $stmt->execute([
                    ':email' => $customerEmail, // Already validated
                    ':fname' => filter_var(isset($data->first_name) ? $data->first_name : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':lname' => filter_var(isset($data->last_name) ? $data->last_name : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':phone' => filter_var(isset($data->contact_number) ? $data->contact_number : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':house' => filter_var(isset($data->house_number) ? $data->house_number : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':street' => filter_var(isset($data->street) ? $data->street : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':city' => filter_var(isset($data->city) ? $data->city : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':postcode' => filter_var(isset($data->post_code) ? $data->post_code : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':country' => filter_var(isset($data->country) ? $data->country : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS), // Updated filter
                    ':paypal_order_id' => $orderID, // Already sanitized
                    ':paypal_tx_id' => $transactionId, // From PayPal API
                    ':amount' => $paidAmount, // From PayPal API
                    ':currency' => $paidCurrency, // From PayPal API
                    ':status' => 'Paid'
                ]);

// ... after $stmt->execute([...]); for the 'orders' table ...

$last_order_id = $pdo->lastInsertId();
error_log("VerifyPayPal - Last Inserted Order ID: " . $last_order_id); // DEBUG 1

// Check if the cart exists in the session and is not empty
if ($last_order_id && isset($_SESSION['mycart']) && is_array($_SESSION['mycart']) && !empty($_SESSION['mycart'])) {
    error_log("VerifyPayPal - Cart Data: " . print_r($_SESSION['mycart'], true)); // DEBUG 2: See cart contents

    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                 VALUES (:order_id, :product_id, :quantity, :price)";
    $stmt_item = $pdo->prepare($sql_item);

    if (!$stmt_item) { // DEBUG 3: Check if prepare failed
        error_log("VerifyPayPal - ERROR preparing statement for order_items: " . print_r($pdo->errorInfo(), true));
    } else {
        foreach ($_SESSION['mycart'] as $key => $item) {
            error_log("VerifyPayPal - Processing cart item key {$key}: " . print_r($item, true)); // DEBUG 4: See each item

            if (isset($item['product_id'], $item['product_qty'], $item['price']) &&
                is_numeric($item['product_id']) &&
                is_numeric($item['product_qty']) && $item['product_qty'] > 0 &&
                is_numeric($item['price']))
            {
                try {
                    $execute_success = $stmt_item->execute([
                        ':order_id' => $last_order_id,
                        ':product_id' => (int)$item['product_id'],
                        ':quantity' => (int)$item['product_qty'],
                        ':price' => (float)$item['price']
                    ]);
                    if (!$execute_success) { // DEBUG 5: Check if execute failed
                        error_log("VerifyPayPal - ERROR executing insert for order_item (Product ID: {$item['product_id']}): " . print_r($stmt_item->errorInfo(), true));
                    } else {
                        error_log("VerifyPayPal - Successfully inserted order_item (Product ID: {$item['product_id']}) for Order ID: " . $last_order_id);
                    }
                } catch (PDOException $item_ex) { // DEBUG 6: Catch specific PDO exceptions for item insert
                     error_log("VerifyPayPal - PDOException during order_item insert (Product ID: {$item['product_id']}): " . $item_ex->getMessage());
                }
            } else {
                error_log("VerifyPayPal - Invalid item data in session cart for Order ID: " . $last_order_id . " Item Data: " . print_r($item, true));
            }
        }
    }
} elseif (!$last_order_id) {
     error_log("VerifyPayPal - Could not get last_order_id after inserting into orders table for PayPal Order ID: " . ($orderID ?? 'N/A'));
} else {
    error_log("VerifyPayPal - Session cart 'mycart' was empty or invalid when trying to insert order items for Order ID: " . $last_order_id . ". Cart content: " . print_r($_SESSION['mycart'] ?? 'Not set', true));
}

// Clear session AFTER saving order AND order items (or attempting to)
// For debugging, you might temporarily comment out the unset lines to inspect session after redirect
// unset($_SESSION['mycart']);
// unset($_SESSION['cart_total']);
// unset($_SESSION['currency_code']);
// error_log("PayPal Verify - Session variables unset after successful DB insert for OrderID: " . $orderID);

// ... rest of your success response ...

                // Clear session
                unset($_SESSION['mycart']);
                unset($_SESSION['cart_total']);
                unset($_SESSION['currency_code']);
                // error_log("PayPal Verify - Session variables unset after successful DB insert for OrderID: " . $orderID); // Log session unset

                $response['success'] = true;
                $response['message'] = 'Payment verified successfully and order saved.';
                $response['transaction_id'] = $transactionId;

            } catch (PDOException $e) {
                $response['message'] = 'Payment verified, but failed to save order to database.';
                error_log("PayPal Verify DB Error: OrderID {$orderID}, TxID {$transactionId}. Error: " . $e->getMessage());
                $response['success'] = false; // Ensure success is false on DB error
            }
         } else {
             $response['message'] = 'Payment amount or currency mismatch. Verification failed.';
             error_log("PayPal SECURITY ALERT: Amount/Currency mismatch for Order ID: {$orderID}. Paid: {$paidAmount} {$paidCurrency}, Expected: {$expectedAmount} {$expectedCurrency}");
         }
    } else {
        $response['message'] = 'Payment status is not COMPLETED (' . htmlspecialchars($paymentStatus) . '). Verification failed.';
         error_log("PayPal Verify Warning: Status not completed for Order ID: {$orderID}. Status: {$paymentStatus}");
    }

} catch (HttpException $ex) {
    $response['message'] = 'Error communicating with PayPal API to verify order.';
    // Use isset() and ternary operator for older PHP compatibility
    $debugId = 'N/A'; // Default
    // Check if headers exist and the specific header exists before accessing
    if (method_exists($ex, 'getHeaders') && is_array($ex->getHeaders()) && isset($ex->getHeaders()['PayPal-Debug-Id'][0])) {
         $debugId = $ex->getHeaders()['PayPal-Debug-Id'][0];
    }
    error_log("PayPal Verify API Exception: OrderID: {$orderID}. Status Code: {$ex->statusCode}. Message: {$ex->getMessage()}. Debug ID: {$debugId}");

} catch (Exception $e) {
     $response['message'] = 'An internal server error occurred during payment verification.';
     error_log("PayPal Verify Generic Exception: OrderID: {$orderID}. Error: {$e->getMessage()}");
}

// --- Final Response Logging (Can be removed once working) ---
// error_log("Verify PayPal Script End - Final Response: " . print_r($response, true));
// --- End Final Response Logging ---


// --- Send Final JSON Response ---
// Ensure only JSON is outputted
echo json_encode($response);
exit;

?>
