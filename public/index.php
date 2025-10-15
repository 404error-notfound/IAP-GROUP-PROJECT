<?php
require_once "PuppyLayout.php"; // adjust if your class file has a different name

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