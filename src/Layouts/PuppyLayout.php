<?php

namespace Angel\IapGroupProject\Layouts;

echo "PuppyLayout loaded<br>";
error_log("PuppyLayout reached line X");
class PuppyLayout
{
    public function header()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <link rel="icon" href="/IAP-GROUP-PROJECT/public/images/gopuppygo-logo.svg" type="image/svg+xml">
            <title>Puppy Adoption</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
                integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

            <style>
                body {
                    margin: 0;
                    font-family: Arial, sans-serif;
                }

                .navbar {
                    background-color: #fff;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px 40px;
                    border-bottom: 1px solid #ddd;
                }

                .navbar-left {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }

                .navbar-left a {
                    text-decoration: none;
                    color: #5a2ca0;
                    font-weight: bold;
                }

                .navbar-right {
                    display: flex;
                    gap: 20px;
                    align-items: center;
                }

                .signin-btn {
                    background-color: #5a2ca0;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 20px;
                    text-decoration: none;
                }

                .background {
                    background: url('data:image/jpeg;base64,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') no-repeat center/cover;
                    height: 400px;
                    color: white;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    text-align: center;
                    position: relative;
                }

                .background-image::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.4);
                    z-index: 0;
                }

                .search-bar {
                    margin-top: 20px;
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                }

                .search-bar input {
                    padding: 10px;
                    border-radius: 5px;
                    border: none;
                    width: 250px;
                }

                .search-bar button {
                    background-color: #5a2ca0;
                    border: none;
                    padding: 10px 20px;
                    color: white;
                    border-radius: 5px;
                    cursor: pointer;
                }

                /* Category cards */
                .categories {
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    margin: 40px 0;
                }

                .card {
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                    width: 150px;
                    transition: transform 0.2s ease;
                }

                .card:hover {
                    transform: scale(1.05);
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                }

                .card img {
                    width: 50px;
                    margin-bottom: 10px;
                }

                .dropdown {
                    position: relative;
                    display: inline-block;
                    z-index: 10;
                    /* ensures it appears above the background */
                }

                .dropdown button {
                    background-color: #5a2ca0;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 25px;
                    cursor: pointer;
                    font-weight: bold;
                    font-size: 15px;
                    transition: background 0.3s ease;
                }

                .dropdown button:hover {
                    background-color: #7b44d2;
                }

                .dropdown-content {
                    display: none;
                    position: absolute;
                    background-color: white;
                    top: 110%;
                    /* dropdown appears below the button */
                    left: 0;
                    min-width: 230px;
                    padding: 15px;
                    border-radius: 10px;
                    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
                    z-index: 999;
                    /* stays above background image */
                }

                .dropdown-content form input {
                    width: 100%;
                    padding: 8px;
                    margin: 6px 0;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                }

                .dropdown-content input[type="submit"] {
                    background-color: #5a2ca0;
                    color: white;
                    border: none;
                    padding: 8px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                    width: 100%;
                }

                .dropdown:hover .dropdown-content {
                    display: block;
                }
            </style>
        </head>

        <body>
            <?php
    }
    public function nav($conf)
    {
        ?>
            <nav class="navbar" aria-label="Fifth navbar example">

                <div class="navbar-left">
                    <h2 style="color: #5a2ca0;">Go.Puppy.Go</h2>
                    <a href="/home">Home</a>
                    <a href="/puppy/BrowsePuppy.php">Browse Puppies</a>
                    <?php if ($conf['isOwner']): ?>
                        <a href="/puppy/AddPuppy.php">Add Puppy</a>
                    <?php endif; ?>
                </div>
                <div class="navbar-right">
                    <?php if ($conf['isLoggedIn']): ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">register</a>
                        <div class="dropdown">
                            <button>Administration Home</button>
                            <div class="dropdown-content">
                                <form action="admin/admin-dashboard.php" method="POST">
                                    <input type="text" name="username" placeholder="Username" required><br>
                                    <input type="password" name="password" placeholder="Password" required><br>
                                    <input type="submit" value="Login">
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                </div>

            </nav>
            <div class="container py-4">
                <?php


    }
    public function content($conf)
    {
        ?>
                <div class="row align-items-md-stretch">
                    <div class="col-md-6">
                        <div class="background">
                            <h2>Find Peace.Find your BestFriend</h2>
                            <p>We can change the world together. Adopt a puppy today and give them a loving home.</p>
                            <a href="/puppy/Aboutus.php">About us</a>
                            <p>
                                <?php echo htmlspecialchars($conf['message'] ?? ''); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php
    }
    public function footer($conf)
    {
        ?>
            </div>
            <footer class="pt-3 mt-4 text-muted border-top">
                <p>Copyrights &copy; <?php echo date("Y"); ?> My Web Page:-All Rights Reserved</p>

            </footer>
            <?php
    }
}
