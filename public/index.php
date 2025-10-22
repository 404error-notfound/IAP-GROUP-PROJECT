<?php
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Layouts\PuppyLayout;

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