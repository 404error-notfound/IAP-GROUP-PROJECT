<?php
require_once __DIR__ . '/../../bootstrap.php';

use Angel\IapGroupProject\Database;

// Check if user is logged in as client
$userRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? null;
if (!isset($_SESSION['user_id']) || $userRole !== 'client') {
    header("Location: ../login.php");
    exit();
}

$client_id = $_SESSION['client_id'] ?? null;

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all available dogs with their details
    $stmt = $db->prepare("
        SELECT 
            d.dog_id,
            d.name,
            d.age,
            d.adoption_fee,
            d.image_url,
            d.description,
            d.location,
            d.created_at,
            b.breed_name,
            dg.gender_name as dog_gender,
            u.full_name as rehomer_name,
            r.location as rehomer_location,
            r.contact_email as rehomer_email,
            CASE WHEN f.favourite_id IS NOT NULL THEN 1 ELSE 0 END as is_favourite
        FROM dogs d
        INNER JOIN breeds b ON d.breed_id = b.breed_id
        LEFT JOIN dog_gender dg ON d.dog_gender_id = dg.dog_gender_id
        INNER JOIN rehomers r ON d.rehomer_id = r.rehomer_id
        INNER JOIN users u ON r.user_id = u.user_id
        LEFT JOIN favourites f ON d.dog_id = f.dog_id AND f.client_id = ?
        WHERE d.status = 'Available'
        ORDER BY d.created_at DESC
    ");
    
    $stmt->execute([$client_id]);
    $dogs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching dogs: " . $e->getMessage());
    $dogs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Dogs - GoPuppyGo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/client-dashboard.css">
    <style>
        .browse-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .browse-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .browse-header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        
        .search-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-filter input,
        .search-filter select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .dogs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .dog-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .dog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .dog-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dog-content {
            padding: 20px;
        }
        
        .dog-name {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .dog-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 0.9em;
            color: #666;
        }
        
        .info-badge i {
            color: #667eea;
        }
        
        .dog-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            max-height: 60px;
            overflow: hidden;
        }
        
        .rehomer-info {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 0.9em;
            color: #666;
        }
        
        .rehomer-info i {
            color: #667eea;
            margin-right: 5px;
        }
        
        .dog-actions {
            display: flex;
            gap: 10px;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-favourite {
            background: #fff;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 8px;
            flex: 0 0 auto;
        }
        
        .btn-favourite.active {
            background: #667eea;
            color: white;
        }
        
        .btn-favourite:hover {
            background: #667eea;
            color: white;
        }
        
        .favourite-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .favourite-icon:hover {
            transform: scale(1.1);
        }
        
        .favourite-icon.active i {
            color: #e74c3c;
        }
        
        .no-dogs {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-dogs i {
            font-size: 4em;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="browse-container">
        <a href="client-dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
        
        <div class="browse-header">
            <h1><i class="fas fa-paw"></i> Browse Available Dogs</h1>
            <p>Find your perfect companion</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="search-filter">
            <input type="text" id="searchInput" placeholder="Search by name or breed..." style="width: 300px;">
            <select id="genderFilter">
                <option value="">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <select id="sortBy">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
            </select>
        </div>
        
        <?php if (empty($dogs)): ?>
            <div class="no-dogs">
                <i class="fas fa-dog"></i>
                <h2>No Dogs Available</h2>
                <p>Check back later for new dogs looking for homes!</p>
            </div>
        <?php else: ?>
            <div class="dogs-grid">
                <?php foreach ($dogs as $dog): ?>
                    <div class="dog-card" data-name="<?php echo strtolower($dog['name']); ?>" 
                         data-breed="<?php echo strtolower($dog['breed_name']); ?>"
                         data-gender="<?php echo $dog['dog_gender']; ?>"
                         data-price="<?php echo $dog['adoption_fee']; ?>"
                         data-date="<?php echo strtotime($dog['created_at']); ?>">
                        
                        <div class="favourite-icon <?php echo $dog['is_favourite'] ? 'active' : ''; ?>" 
                             onclick="toggleFavourite(<?php echo $dog['dog_id']; ?>, this)">
                            <i class="<?php echo $dog['is_favourite'] ? 'fas' : 'far'; ?> fa-heart"></i>
                        </div>
                        
                        <?php if ($dog['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($dog['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($dog['name']); ?>" 
                                 class="dog-image">
                        <?php else: ?>
                            <div class="dog-image" style="display: flex; align-items: center; justify-content: center; color: white; font-size: 4em;">
                                <i class="fas fa-dog"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="dog-content">
                            <h2 class="dog-name"><?php echo htmlspecialchars($dog['name']); ?></h2>
                            
                            <div class="dog-info">
                                <span class="info-badge">
                                    <i class="fas fa-paw"></i>
                                    <?php echo htmlspecialchars($dog['breed_name']); ?>
                                </span>
                                <?php if ($dog['dog_gender']): ?>
                                    <span class="info-badge">
                                        <i class="fas fa-<?php echo $dog['dog_gender'] === 'Male' ? 'mars' : 'venus'; ?>"></i>
                                        <?php echo htmlspecialchars($dog['dog_gender']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="info-badge">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo $dog['age']; ?> months
                                </span>
                                <span class="info-badge">
                                    <i class="fas fa-tag"></i>
                                    ₵<?php echo number_format($dog['adoption_fee'], 2); ?>
                                </span>
                            </div>
                            
                            <?php if ($dog['description']): ?>
                                <p class="dog-description"><?php echo htmlspecialchars($dog['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="rehomer-info">
                            <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($dog['rehomer_name']); ?></div>
                            <?php if ($dog['rehomer_location']): ?>
                                <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($dog['rehomer_location']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="dog-actions">
                            <a href="#" onclick="requestAdoption(<?php echo $dog['dog_id']; ?>); return false;" class="btn btn-primary">
                                <i class="fas fa-heart"></i>
                                Request Adoption
                            </a>
                            <a href="#" onclick="bookVisit(<?php echo $dog['dog_id']; ?>); return false;" class="btn btn-primary" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                                <i class="fas fa-calendar-check"></i>
                                Book Visit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', filterDogs);
        document.getElementById('genderFilter').addEventListener('change', filterDogs);
        document.getElementById('sortBy').addEventListener('change', sortDogs);
        
        function filterDogs() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const genderFilter = document.getElementById('genderFilter').value;
            const cards = document.querySelectorAll('.dog-card');
            
            cards.forEach(card => {
                const name = card.dataset.name;
                const breed = card.dataset.breed;
                const gender = card.dataset.gender;
                
                const matchesSearch = name.includes(searchTerm) || breed.includes(searchTerm);
                const matchesGender = !genderFilter || gender === genderFilter;
                
                card.style.display = matchesSearch && matchesGender ? 'block' : 'none';
            });
        }
        
        function sortDogs() {
            const sortBy = document.getElementById('sortBy').value;
            const grid = document.querySelector('.dogs-grid');
            const cards = Array.from(document.querySelectorAll('.dog-card'));
            
            cards.sort((a, b) => {
                switch(sortBy) {
                    case 'newest':
                        return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                    case 'oldest':
                        return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                    case 'price-low':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'price-high':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    default:
                        return 0;
                }
            });
            
            cards.forEach(card => grid.appendChild(card));
        }
        
        function toggleFavourite(dogId, element) {
            fetch('toggle-favourite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ dog_id: dogId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.classList.toggle('active');
                    const icon = element.querySelector('i');
                    icon.className = data.is_favourite ? 'fas fa-heart' : 'far fa-heart';
                } else {
                    alert(data.message || 'Error toggling favourite');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
        
        function requestAdoption(dogId) {
            if (confirm('Are you sure you want to request adoption for this dog?')) {
                window.location.href = 'request-adoption.php?dog_id=' + dogId;
            }
        }
        
        function bookVisit(dogId) {
            window.location.href = 'book-visit.php?dog_id=' + dogId;
        }
    </script>
</body>
</html>
