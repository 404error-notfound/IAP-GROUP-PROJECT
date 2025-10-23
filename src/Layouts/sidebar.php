<aside class="sidebar">
  <?php if ($_SESSION['role'] === 'client'): ?>
    <ul>
      <li><a href="/public/client/dashboard.php">Dashboard</a></li>
      <li><a href="/public/client/favourites.php">My Favourites</a></li>
      <li><a href="/public/client/adoptions.php">Adoption Requests</a></li>
      <li><a href="/public/client/bookings.php">My Bookings</a></li>
      <li><a href="/public/client/profile.php">Profile</a></li>
    </ul>

  <?php elseif ($_SESSION['role'] === 'rehomer'): ?>
    <ul>
      <li><a href="/public/rehomer/dashboard.php">Dashboard</a></li>
      <li><a href="/public/rehomer/add-dog.php">Add Dog</a></li>
      <li><a href="/public/rehomer/manage-dogs.php">Manage Dogs</a></li>
      <li><a href="/public/rehomer/adoption-requests.php">Adoption Requests</a></li>
      <li><a href="/public/rehomer/inquiries.php">Inquiries</a></li>
    </ul>

  <?php elseif ($_SESSION['role'] === 'admin'): ?>
    <ul>
      <li><a href="/public/admin/dashboard.php">Dashboard</a></li>
      <li><a href="/public/admin/manage-users.php">Users</a></li>
      <li><a href="/public/admin/manage-breeds.php">Breeds</a></li>
      <li><a href="/public/admin/verify-licenses.php">Licenses</a></li>
      <li><a href="/public/admin/payments.php">Payments</a></li>
    </ul>
  <?php endif; ?>
</aside>
