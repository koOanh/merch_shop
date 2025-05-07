<?php
include_once('./includes/headerNav.php');

// --- Get Cart Total and Currency from Session ---
$cartTotal = 0.00;
// Check if cart_total is set and valid in the session *after* session_start()
if (isset($_SESSION['cart_total']) && is_numeric($_SESSION['cart_total']) && $_SESSION['cart_total'] > 0) {
    $cartTotal = (float)$_SESSION['cart_total'];
} else {
    // Log if cart total is missing or invalid at this stage
    error_log("Checkout Page Load - Warning: cart_total missing or invalid in session.");
    // Optional: Redirect back to cart if total is invalid or cart is empty
    // header('Location: cart.php');
    // exit;
    // Or display an error message on this page
    echo "<p style='color: red; text-align: center; padding: 20px;'>Error: Invalid cart total or cart is empty. Cannot proceed to checkout. Please return to <a href='cart.php'>your cart</a>.</p>";
    // You might want to hide the rest of the checkout form in this case by adding an exit; here
    // exit; // Uncomment this line if you want to stop rendering the form when cart is empty/invalid
}

// Format it to two decimal places, suitable for PayPal amount
$paypalAmount = number_format($cartTotal, 2, '.', '');

// Get the currency code from the session, default to USD if not set
$currencyCode = isset($_SESSION['currency_code']) ? htmlspecialchars($_SESSION['currency_code']) : 'USD';

// **IMPORTANT**: Use your ACTUAL PayPal Client ID
// Get this from your PayPal Developer Dashboard
$payPalClientID = "AYn-gDaUjFZxow-e1hYvzPZIO2VnHTnHlbvM8JYRBi_JTGi-KXpkHyjRinCNfs9Kqgs7rlGY4i35D8Tr"; // Your Sandbox Client ID

?>
<div class="overlay" data-overlay></div>
<header>
    <?php require_once './includes/topheadactions.php'; ?>
    <?php require_once './includes/mobilenav.php'; ?>

    <style>
        /* --- Keep all your existing CSS styles here --- */
        * {
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }
        :root {
            --main-maroon: #CE5959;
            --deep-maroon: #89375F;
        }
        .appointments-section { width: 80%; margin: 20px auto; }
        input { border: none; outline: none; }
        .appointment-heading { text-align: center; }
        .appointment-head { font-size: 40px; font-weight: 700; margin-bottom: 0; color: var(--main-maroon); }
        .appointment-line { width: 160px; height: 3px; border-radius: 10px; background-color: var(--main-maroon); display: inline-block; }
        .edit-detail-field .child-detail-inner { width: 100%; display: flex; margin-top: 10px; justify-content: space-between; margin-left: auto; margin-right: auto; }
        .Add-child-section { margin-top: 40px; }
        .Add-child-section .child-heading-t { font-size: 25px; font-weight: 700; color: var(--main-maroon); }
        .Add-child-section .child-fields, /* Generic class for common styles */
        .Add-child-section .child-fields1,
        .Add-child-section .child-fields3,
        .Add-child-section .child-fields4,
        .Add-child-section .child-fields5,
        .Add-child-section .child-fields6,
        .Add-child-section .child-fields7,
        .Add-child-section .child-fields8,
        .Add-child-section .child-fields9,
        .Add-child-section .Address-field {
            width: 49%; /* Default width for two-column fields */
            height: 55px;
            border: 1px solid var(--main-maroon);
            border-radius: 5px;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #FFFFFF;
            position: relative;
            box-shadow: 2px 2px 2px rgb(185, 184, 184);
        }
        .Add-child-section .Address-field {
             width: 100%; /* Full width for country field */
        }

        /* Style for input fields inside the styled divs */
        .Add-child-section .child-fields input,
        .Add-child-section .child-fields1 input,
        .Add-child-section .child-fields3 input,
        .Add-child-section .child-fields4 input,
        .Add-child-section .child-fields5 input,
        .Add-child-section .child-fields6 input,
        .Add-child-section .child-fields7 input,
        .Add-child-section .child-fields8 input,
        .Add-child-section .child-fields9 input,
        .Add-child-section .Address-field input {
            color: #000000;
            font-weight: 700;
            width: 100%;
            height: 100%; /* Ensure input fills the div */
            background-color: transparent;
            padding: 0; /* Remove default padding */
            margin: 0;  /* Remove default margin */
            border: none; /* Ensure no borders from input itself */
            outline: none; /* Ensure no outline */
        }
        /* Style for the floating labels using ::before */
        .Add-child-section .child-fields1::before { content: "First Name"; }
        .Add-child-section .child-fields3::before { content: "Last Name"; }
        .Add-child-section .child-fields4::before { content: "House Number or Name"; }
        .Add-child-section .child-fields5::before { content: "Street or Road"; }
        .Add-child-section .child-fields6::before { content: "Town or City"; }
        .Add-child-section .child-fields7::before { content: "Post Code"; }
        .Add-child-section .child-fields8::before { content: "Contact Number"; }
        .Add-child-section .child-fields9::before { content: "Email Address"; }
        .Add-child-section .Address-field::before { content: "Country Name"; }

        /* Common style for all ::before pseudo-elements */
        .Add-child-section .child-fields::before,
        .Add-child-section .child-fields1::before,
        .Add-child-section .child-fields3::before,
        .Add-child-section .child-fields4::before,
        .Add-child-section .child-fields5::before,
        .Add-child-section .child-fields6::before,
        .Add-child-section .child-fields7::before,
        .Add-child-section .child-fields8::before,
        .Add-child-section .child-fields9::before,
        .Add-child-section .Address-field::before {
            position: absolute;
            top: -10px;
            left: 10px; /* Position label consistently */
            background-image: linear-gradient(#FFFCF6, #FFFFFF); /* Or just background-color: #FFFFFF; */
            padding-left: 4px;
            padding-right: 4px;
            color: var(--main-maroon);
            font-weight: 600;
            font-size: 13px;
        }

        .child-register-btn { padding-top: 5px; padding-bottom: 30px; /* Add padding bottom */ }
        #proceed-btn { width: 550px; max-width: 100%; /* Responsive width */ height: 60px; background-color: var(--main-maroon); box-shadow: 0px 0px 4px #615f5f; line-height: 60px; color: #FFFFFF; margin-left: auto; border-radius: 8px; text-align: center; cursor: pointer; font-size: 19px; font-weight: 600; display: block; margin-right: auto; border: none; /* Ensure it looks like a button */ }
        #paypal-button-container { width: 550px; max-width: 100%; /* Responsive width */ margin-left: auto; margin-right: auto; margin-top: 10px; display: none; min-height: 50px; }
        #message-area { text-align: center; margin-top: 15px; font-weight: bold; min-height: 1.2em; /* Prevent layout shift */ }
        .success-message { color: green; }
        .error-message { color: var(--main-maroon); }
        .error-ms { color: var(--main-maroon); margin-bottom: 10px; display: block; text-align: center; min-height: 1em; }

        /* --- Responsive Styles --- */
        @media screen and (max-width: 794px) {
            .appointments-section { width: 90%; }
            #proceed-btn,
            #paypal-button-container { width: 100%; }
            .edit-detail-field .child-detail-inner { flex-direction: column; margin-top: 0; }
            .Add-child-section .child-fields,
            .Add-child-section .child-fields1,
            .Add-child-section .child-fields3,
            .Add-child-section .child-fields4,
            .Add-child-section .child-fields5,
            .Add-child-section .child-fields6,
            .Add-child-section .child-fields7,
            .Add-child-section .child-fields8,
            .Add-child-section .child-fields9,
            .Add-child-section .Address-field {
                 width: 100% !important; /* Full width on smaller screens */
                 margin-bottom: 20px; /* Adjust spacing */
            }
        }
         @media screen and (max-width: 629px) {
             .Add-child-section { width: 100%; } /* Use full width within appointments-section */
         }
        @media screen and (max-width: 409px) {
             .appointments-section { width: 95%; }
             .appointment-head { font-size: 30px; }
        }
    </style>
</header>

<body>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars($payPalClientID); ?>&currency=<?php echo htmlspecialchars($currencyCode); ?>"></script>

    <div class="appointments-section">

        <div class="appointment-heading">
            <p class="appointment-head">CheckOut</p>
            <span class="appointment-line"></span>
        </div>

        <div class="inner-appointment">

            <section class="edit-detail-field">
                <form id="checkout-form" onsubmit="return false;"> <div class="Add-child-section">

                        <div class="child-detail-inner">
                            <div class="child-fields1">
                                <input type="text" name="first_name" placeholder="First Name" required>
                            </div>
                            <div class="child-fields3">
                                <input type="text" name="last_name" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="child-detail-inner">
                            <div class="child-fields child-fields4">
                                <input type="text" name="house_number" placeholder="House Number or Name" required>
                            </div>
                            <div class="child-fields child-fields5 ">
                                <input type="text" name="street" placeholder="Street or Road" required>
                            </div>
                        </div>

                        <div class="child-detail-inner">
                            <div class="child-fields child-fields6">
                                <input type="text" name="city" placeholder="Town or City" required>
                            </div>
                            <div class="child-fields child-fields7 ">
                                <input type="text" name="post_code" placeholder="Post Code" required>
                            </div>
                        </div>

                        <div class="child-detail-inner">
                             <div class="child-fields Address-field">
                                <input type="text" name="country" placeholder="Country Name" required>
                            </div>
                        </div>

                        <div class="child-detail-inner">
                            <div class="child-fields child-fields8">
                                <input type="tel" name="contact_number" placeholder="Contact Number" required>
                            </div>
                            <div class="child-fields child-fields9">
                                <input type="email" name="email_address" placeholder="Email Address" required>
                            </div>
                        </div>

                        <div class="child-register-btn">
                            <span class="error-ms"></span> <?php if ($cartTotal > 0): ?>
                            <button type="button" id="proceed-btn" >Proceed To Pay <?php echo htmlspecialchars($currencyCode) . " " . $paypalAmount; ?></button>
                            <?php else: ?>
                            <p style="text-align:center; color: var(--main-maroon);">Cannot proceed without items in cart.</p>
                            <?php endif; ?>

                            <div id="paypal-button-container"></div> <div id="message-area"></div> </div>

                    </div>
                </form>
            </section>

        </div>
    </div>


    <script>
        // Get references to elements
        const checkoutForm = document.getElementById('checkout-form');
        const fields = checkoutForm.querySelectorAll('input[required]'); // Select only required inputs
        const errorSpan = document.querySelector('.error-ms');
        const proceedButton = document.getElementById('proceed-btn'); // Might be null if cart is empty
        const paypalContainer = document.getElementById('paypal-button-container');
        const messageArea = document.getElementById('message-area');

        // --- Get Amount and Currency from PHP ---
        // Ensure these are treated as strings for consistency
        const orderAmountFromPHP = '<?php echo $paypalAmount; ?>';
        const currencyCodeFromPHP = '<?php echo $currencyCode; ?>';

        // --- Form Validation Function ---
        function validateForm() {
            let isValid = true;
            errorSpan.textContent = ''; // Clear previous errors
            messageArea.textContent = ''; // Clear previous PayPal messages
            messageArea.className = '';

            fields.forEach(field => {
                field.style.borderColor = 'var(--main-maroon)'; // Reset border color
                if (field.value.trim() === '') {
                    isValid = false;
                    field.style.borderColor = 'red'; // Highlight empty field
                }
                // Add more specific validation if needed (e.g., email format)
                if (field.type === 'email' && !/\S+@\S+\.\S+/.test(field.value.trim())) {
                     isValid = false;
                     field.style.borderColor = 'red';
                }
                 // Basic phone number check (e.g., at least 7 digits) - adjust as needed
                if (field.type === 'tel' && !/^\+?[\d\s-]{7,}$/.test(field.value.trim())) {
                    isValid = false;
                    field.style.borderColor = 'red';
                }
            });

            if (!isValid) {
                errorSpan.textContent = "Please fill all required fields correctly.";
            }
            return isValid;
        }

        // --- Function to Validate and Show PayPal ---
        function validateAndShowPayPal() {
            // Ensure proceedButton exists before accessing its style
            if (!proceedButton) return;

            if (validateForm()) {
                // Hide the original button
                proceedButton.style.display = 'none';
                // Show the PayPal button container
                paypalContainer.style.display = 'block';
                // Render PayPal buttons if not already rendered
                if (!paypalContainer.hasChildNodes()) { // Prevent re-rendering
                    renderPayPalButtons();
                }
            } else {
                 // Ensure PayPal container is hidden if validation fails
                 paypalContainer.style.display = 'none';
                 proceedButton.style.display = 'block'; // Show proceed button again
            }
        }

        // --- Function to Render PayPal Buttons ---
        function renderPayPalButtons() {
            // Ensure proceedButton exists before accessing its style in error cases
            const showProceedButtonOnError = () => {
                if (proceedButton) proceedButton.style.display = 'block';
                paypalContainer.style.display = 'none';
            }

            // Clear previous messages
            messageArea.textContent = '';
            messageArea.className = '';

            // Check if PayPal SDK loaded correctly
            if (typeof paypal === 'undefined') {
                 console.error("PayPal SDK not loaded!");
                 messageArea.textContent = 'Error: Payment gateway failed to load. Please refresh.';
                 messageArea.className = 'error-message';
                 showProceedButtonOnError();
                 return;
            }


            paypal.Buttons({
                // --- Set up the transaction ---
                createOrder: function(data, actions) {
                    // Use the amount and currency passed from PHP
                    const orderAmount = orderAmountFromPHP;
                    const currencyCode = currencyCodeFromPHP;

                    // Basic validation for amount
                    if (isNaN(parseFloat(orderAmount)) || parseFloat(orderAmount) <= 0) {
                        console.error("Invalid order amount passed from server:", orderAmount);
                        messageArea.textContent = 'Error: Invalid order amount ('+orderAmount+'). Cannot create order.';
                        messageArea.className = 'error-message';
                        showProceedButtonOnError();
                        // Use Promise.reject for cleaner error handling within PayPal SDK flow
                        return Promise.reject(new Error("Invalid amount: " + orderAmount));
                    }

                    console.log("Creating order with amount:", orderAmount, " ", currencyCode); // Debug log

                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: orderAmount, // Use the dynamic amount
                                currency_code: currencyCode // Use the dynamic currency
                            }
                        }]
                    });
                },

                // --- Finalize the transaction (Client-Side Approval) ---
                onApprove: function(data, actions) {
                    // Show processing message
                    messageArea.textContent = 'Processing payment... Please wait.';
                    messageArea.className = ''; // Neutral message
                    paypalContainer.style.display = 'none'; // Hide buttons during processing

                    return actions.order.capture().then(function(details) {
                        // --- SUCCESS (Client-Side Confirmation) ---
                        console.log('Transaction completed by ' + details.payer.name.given_name);
                        console.log('Transaction Details:', details);

                        // **CRITICAL SERVER-SIDE STEP**: Send details to your server for verification
                        // Gather form data
                        const formData = new FormData(checkoutForm); // Use FormData for easy collection
                        const checkoutData = Object.fromEntries(formData.entries()); // Convert FormData to plain object

                        // Add PayPal details to the data sent to the server
                        checkoutData.orderID = data.orderID; // PayPal Order ID

                        messageArea.textContent = 'Payment captured. Verifying with server...'; // Update status

                        // Use fetch to send data to your server-side script
                        fetch('verify_paypal.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(checkoutData) // Send form data + PayPal orderID
                        })
                        .then(response => {
                            if (!response.ok) {
                                // Handle HTTP errors (e.g., 404, 500)
                                // Try to get text response for more details if JSON fails
                                return response.text().then(text => {
                                    throw new Error(`Server responded with status: ${response.status}. Response: ${text}`);
                                });
                            }
                            return response.json(); // Only parse as JSON if response.ok
                        })
                        .then(serverData => {
                            console.log('Server verification response:', serverData);
                            if (serverData.success) {
                                // Server confirmed payment! Show final success message.
                                messageArea.textContent = 'Payment Successful & Verified! Thank you, ' + (details.payer.name.given_name || 'Customer') + '. Your order is confirmed.';
                                messageArea.className = 'success-message';
                                // Hide form or redirect
                                checkoutForm.style.display = 'none'; // Hide the form
                                // Optional: Redirect to a thank you page after a delay
                                // setTimeout(() => { window.location.href = '/thank-you.php'; }, 3000);
                            } else {
                                // Server reported an issue (e.g., verification failed, amount mismatch)
                                messageArea.textContent = 'Payment captured, but verification failed. Please contact support. Reason: ' + (serverData.message || 'Unknown server error');
                                messageArea.className = 'error-message';
                                showProceedButtonOnError();
                            }
                        })
                        .catch(error => {
                            console.error('Error sending/receiving data from server:', error);
                            // Network error or issue with the server script itself (e.g., JSON parsing error)
                            messageArea.textContent = 'Payment captured, but could not communicate with server for verification. Please contact support with Order ID: ' + data.orderID + '. Error: ' + error.message;
                            messageArea.className = 'error-message';
                            showProceedButtonOnError();
                        });

                    }).catch(function(err) {
                        // Handle errors during PayPal capture itself
                        console.error("Error capturing order:", err);
                        messageArea.textContent = 'Error processing payment capture. Please try again.';
                        messageArea.className = 'error-message';
                        showProceedButtonOnError();
                    });
                },

                 // --- Handle cancellation ---
                onCancel: function (data) {
                    // User cancelled the payment popup
                    console.log("Payment cancelled by user. OrderID:", data.orderID);
                    messageArea.textContent = 'Payment cancelled.';
                    messageArea.className = 'error-message'; // Use error styling for cancellation message
                    showProceedButtonOnError();
                },

                // --- Handle errors ---
                onError: function(err) {
                    // Errors from PayPal SDK (e.g., configuration issues, network problems talking to PayPal)
                    console.error("PayPal Button Error:", err);
                    messageArea.textContent = 'An error occurred with the payment gateway. Please try again later or contact support.';
                    messageArea.className = 'error-message';
                    showProceedButtonOnError();
                }

            }).render('#paypal-button-container'); // Render buttons into the container
        }

        // Add event listener to the proceed button only if it exists
        if (proceedButton) {
            // ** REMOVED the onclick attribute from the button, using event listener instead **
            proceedButton.addEventListener('click', validateAndShowPayPal);
        } else {
            console.log("Proceed button not found (likely cart is empty).");
        }

    </script>

</body>

<?php require_once './includes/footer.php'; ?>
