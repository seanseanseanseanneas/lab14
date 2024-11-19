<?php
require 'vendor/autoload.php'; 


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \Stripe\Stripe::setApiKey($stripeSecretKey); 

    try {
        
        $customer = \Stripe\Customer::create([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'address' => [
                'line1' => $_POST['address_line1'],
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'postal_code' => $_POST['postal_code'],
                'country' => 'US'
            ],
            'phone' => $_POST['phone'],
        ]);
        echo "Customer created successfully. Customer ID: {$customer->id}";
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<form method="POST" action="">
    <input type="text" name="name" placeholder="Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="text" name="address_line1" placeholder="Address" required />
    <input type="text" name="city" placeholder="City" required />
    <input type="text" name="state" placeholder="State" required />
    <input type="text" name="postal_code" placeholder="Postal Code" required />
    <input type="text" name="phone" placeholder="Phone" required />
    <button type="submit">Create Customer</button>
</form>
