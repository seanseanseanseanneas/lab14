<?php
require 'vendor/autoload.php'; // Include Composer autoloader

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set your Stripe secret key
$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

// Fetch all customers and products
$customers = $stripe->customers->all(['limit' => 10]); // Fetch first 10 customers
$products = $stripe->products->all(); // Fetch all products

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Retrieve selected customer ID
        $customerId = $_POST['customer_id'];

        // Create a new invoice
        $invoice = $stripe->invoices->create([
            'customer' => $customerId,
        ]);

        // Add selected products as line items
        if (!empty($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $productId) {
                $prices = $stripe->prices->all(['product' => $productId]);
                foreach ($prices->data as $price) {
                    if ($price->type === 'one_time') {
                        $stripe->invoiceItems->create([
                            'customer' => $customerId,
                            'price' => $price->id,
                            'invoice' => $invoice->id,
                        ]);
                    }
                }
            }
        }

        
        $stripe->invoices->finalizeInvoice($invoice->id);

       
        $invoice = $stripe->invoices->retrieve($invoice->id);

        
        echo "<p>Invoice created successfully!</p>";
        echo "<a href='{$invoice->hosted_invoice_url}' target='_blank'>Pay Invoice</a><br>";
        echo "<a href='{$invoice->invoice_pdf}' target='_blank'>Download PDF</a>";
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
</head>
<body>
    <h1>Create Invoice</h1>
    <form method="POST" action="">
        <label for="customer">Select Customer:</label>
        <select name="customer_id" id="customer" required>
            <?php foreach ($customers->data as $customer): ?>
                <option value="<?= htmlspecialchars($customer->id) ?>">
                    <?= htmlspecialchars($customer->name) ?: $customer->email ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Select Products:</label><br>
        <?php foreach ($products->data as $product): ?>
            <input type="checkbox" name="product_ids[]" value="<?= htmlspecialchars($product->id) ?>">
            <?= htmlspecialchars($product->name) ?><br>
        <?php endforeach; ?>
        <br>

        <button type="submit">Generate Invoice</button>
    </form>
</body>
</html>
