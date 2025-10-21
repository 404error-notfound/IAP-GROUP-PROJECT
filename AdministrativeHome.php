<?php
require_once 'PuppyLayout.php';
require_once 'Database.php';
require_once 'vendor/autoload.php';

class AdministrativeHome extends PuppyLayout
{
    public function nav($conf)
    {
        ?>
        <nav class="navbar" aria-label="Fifth navbar example">

            <div class="navbar-left">

                <h2 style="color: #5a2ca0;">Go.Puppy.Go</h2>
                <a href="/home">Home</a>
                <a href="/about us ">About us</a>
                <a href="BrowsePuppy.php">Browse Puppies</a>
                <?php if ($conf['isOwner']): ?>
                    <a href="AddPuppy.php">Add Puppy</a>
                <?php endif; ?>
            </div>
            <div class="navbar-right">
                <?php if ($conf['isLoggedIn']): ?>
                    <a href="logout.php">Logout</a>


                <?php endif; ?>
            </div>
        </nav>
        <?php
    }
    public function content($conf)
    {

        ?>
        <div class="container py-4">
            <div class="row align-items-md-stretch">
                <div class="col-md-6">
                    <div class="background">
                        <h2>Find Peace.Find your BestFriend</h2>
                        <p>We can change the world together. Adopt a puppy today and give them a loving home.</p>
                        <button>
                            <a href="Registeredusers.php" type="button">Registered users</a>
                        </button>
                        <button>
                            <a href="Adoptedpuppies.php" type="button">Adopted puppies</a>
                        </button>
                        <p>
                            <?php echo htmlspecialchars($conf['message'] ?? ''); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function footer($conf)
    {
        parent::footer($conf);
    }
}

$page = new AdministrativeHome();
$page->header();
$page->nav(['isOwner' => true, 'isLoggedIn' => false]);
$page->content(['message' => 'Welcome to Go.Puppy.Go!']);
$page->footer([]);





