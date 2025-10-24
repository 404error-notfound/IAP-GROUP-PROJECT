<?php

require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Layouts\PuppyLayout;


require_once __DIR__ . '/../src/Layouts/PuppyLayout.php'; // adjust if your class file has a different name
require_once __DIR__ . '/../src/Controllers/AuthController.php';


$layout = new PuppyLayout();

$conf = [
    "isOwner" => true,
    "isLoggedIn" => false,
    "message" => "Welcome to Puppy Adoption!"
];

$layout->header();
$layout->nav($conf);
$layout->content($conf);
$layout->footer($conf);
?>