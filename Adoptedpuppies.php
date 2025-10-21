<?php
require_once 'Database.php';
require_once 'vendor/autoload.php';

$db = Database::getInstance();
$conn = $db->getConnection();

// Fetch all breeds from the database
$stmt = $conn->prepare("SELECT id, fullname, email, phone, created_at FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registered Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #737AE8;
        }

        .container {
            margin-top: 60px;
        }

        h2 {
            color: #dbd5e3ff;
            margin-bottom: 20px;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Adopted puppies</h2>

        <table id="userTable" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>AdoptionID</th>
                    <th>BreedName</th>
                    <th>Age</th>
                    <th>Available</th>
                    <th>Adoption fee</th>

                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['adoption_id']); ?></td>
                            <td><?= htmlspecialchars($user['breed_Name']); ?></td>
                            <td><?= htmlspecialchars($user['age']); ?></td>
                            <td><?= htmlspecialchars($user['available']); ?></td>
                            <td><?= htmlspecialchars($user['adoption_fee']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-edit">Edit</button>
                                <button class="btn btn-sm btn-delete">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No registered puppies found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


    </div>

</body>
<footer>
    <p>Copyright © 2025 PRO Community - All Rights Reserved</p>
</footer>

</html>