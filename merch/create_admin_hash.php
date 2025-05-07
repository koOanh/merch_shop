    <?php
    // File: create_admin_hash.php

    // IMPORTANT: Delete this file from your server after you use it!

    // Set the plain text password you want for your admin account
    $plainPasswordForAdmin = 'kimo@gmail.com'; // Or choose a new, strong password

    // Hash the password
    $hashedPasswordForAdmin = password_hash($plainPasswordForAdmin, PASSWORD_DEFAULT);

    echo "<h1>Admin Password Update</h1>";
    echo "<p><strong>Admin Email:</strong> kimo@gmail.com</p>";
    echo "<p><strong>Plain Password (for your reference, this is what you'll type to log in):</strong> " . htmlspecialchars($plainPasswordForAdmin) . "</p>";
    echo "<p><strong>Hashed Password (COPY THIS ENTIRE STRING and update the database):</strong></p>";
    echo "<textarea rows='3' cols='70' readonly>" . htmlspecialchars($hashedPasswordForAdmin) . "</textarea>";
    echo "<p style='color:red; font-weight:bold;'>Remember to delete this file (create_admin_hash.php) after updating the database!</p>";

    ?>
    