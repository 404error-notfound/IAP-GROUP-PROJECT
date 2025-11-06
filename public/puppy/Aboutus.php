<?php
namespace Angel\IapGroupProject\Layouts;
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Layouts/PuppyLayout.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$layout = new PuppyLayout();
$layout->header();
$layout->nav(['isLoggedIn' => true, 'isOwner' => true]);
?>
<link rel="stylesheet" href="aboutus.css">


<div class="aboutus-slideshow-container">
    <div class="slide fade">
        <h1>About Us</h1>
        <p>Welcome to Go.Puppy.Go! We are passionate about connecting loving homes with adorable puppies in need of
            a
            family. Our mission is to make puppy adoption easy, accessible, and joyful for everyone involved.</p>
        <img src="" alt="Our Mission Image" style="width:100%; height:auto;">
    </div>
    <div class="slide fade">
        <h2>Our Team</h2>
        <p>We are a dedicated team of animal lovers committed to rescuing and rehoming puppies. Our team works
            tirelessly to
            ensure that every puppy finds a safe and loving home.</p>
        <img src="" alt="Our Team Photo" style="width:100%; height:auto;">
    </div>
    <div class="slide fade">
        <h2>Our Mission</h2>
        <p>At Go.Puppy.Go, we believe that every puppy deserves a chance at a happy life. Our mission is to
            facilitate
            the
            adoption process, educate potential pet owners, and promote responsible pet ownership.</p>
        <img src="" alt="Our Mission Image" style="width:100%; height:auto;">
    </div>
    <div class="slide fade">
        <h2>Contact Us</h2>
        <p>If you have any questions or would like to get involved, please don't hesitate to reach out to us
            at<strong>info@gopuppygo.com</strong>.</p>
        <button onclick="location.href='contactus.php'">Contact Us</button>
    </div>

    <div class="dots">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
    </div>

</div>

<script src="aboutus.js"></script>
?>