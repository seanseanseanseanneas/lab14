<?php
require 'vendor/autoload.php'; // Include Composer's autoloader

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get the Stripe Secret Key from the environment variables
$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];

// Set your Stripe API Key
\Stripe\Stripe::setApiKey($stripeSecretKey);

try {
    // Retrieve the list of products
    $products = \Stripe\Product::all();

    // Start building the HTML output
    echo "<h1 style='text-align: center; font-family: Arial, sans-serif;'>Available Products</h1>";
    echo "<div style='display: flex; flex-wrap: wrap; justify-content: center;'>";

    foreach ($products->data as $product) {
        echo "<div style='border: 1px solid #ccc; border-radius: 8px; padding: 20px; margin: 10px; width: 250px; text-align: center; font-family: Arial, sans-serif; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'>";
        echo "<h2 style='font-size: 18px; color: #333;'>" . htmlspecialchars($product->name) . "</h2>";
        echo "<p style='font-size: 14px; color: #666;'>" . htmlspecialchars($product->description) . "</p>";

        if (!empty($product->images)) {
            echo "<img src='" . htmlspecialchars($product->images[0]) . "' alt='" . htmlspecialchars($product->name) . "' style='width: 100%; height: auto; border-radius: 4px;'/>";
        } else {
            echo "<p style='font-size: 12px; color: #999;'>No image available</p>";
        }

        if (isset($product->metadata['price'])) {
            echo "<p style='font-size: 16px; color: #333; font-weight: bold;'>Price: " . htmlspecialchars($product->metadata['price']) . " USD</p>";
        } else {
            echo "<p style='font-size: 14px; color: #999;'>Price: Not available</p>";
        }

        echo "</div>";
    }

    echo "</div>";
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
