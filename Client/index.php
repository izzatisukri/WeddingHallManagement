<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'User';
}
$username = htmlspecialchars($_SESSION['user_name']);

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$search_location = isset($_GET['search_location']) ? trim($_GET['search_location']) : '';
$search_budget = isset($_GET['max_budget']) ? trim($_GET['max_budget']) : '';

$show_success = false;
$success_message = '';
$booked_hall = '';

if (isset($_SESSION['booking_success']) && isset($_SESSION['booked_hall_name'])) {
    $show_success = true;
    $booked_hall = $_SESSION['booked_hall_name'];
    $success_message = "Booking Confirmed!";
    // Clear session after displaying
    unset($_SESSION['booking_success']);
    
}

$halls = [
    [
        'id' => 1,
        'name' => 'Dewan Serbaguna Teluk Mas',
        'area' => 'Teluk Mas',
        'location_full' => 'Teluk Mas, Melaka',
        'capacity' => 700,
        'price' => 12000,
        'price_text' => 'RM 12,000',
        'description' => 'Spacious multipurpose hall with full facilities, perfect for grand wedding receptions.',
        'special_offer' => '✓ Free decoration + Bridal suite included!'
    ],
    [
        'id' => 2,
        'name' => 'Dahlia Wedding Hall',
        'area' => 'Alor Gajah',
        'location_full' => 'Alor Gajah, Melaka',
        'capacity' => 1000,
        'price' => 12000,
        'price_text' => 'RM 12,000',
        'description' => 'Beautiful garden-themed hall with elegant decorations.',
        'special_offer' => '✓ Garden setup included'
    ],
    [
        'id' => 3,
        'name' => 'Gangsa Grand Ballroom',
        'area' => 'Gangsa',
        'location_full' => 'Gangsa, Melaka',
        'capacity' => 1000,
        'price' => 18000,
        'price_text' => 'RM 18,000',
        'description' => 'Luxurious ballroom with premium sound system and chandeliers.',
        'special_offer' => '✓ Premium sound + Lighting included'
    ]
];

$filtered_halls = array_filter($halls, function($hall) use ($search_location, $search_budget) {
    $locationMatch = true;
    if (!empty($search_location)) {
        $locLower = strtolower($search_location);
        $hallArea = strtolower($hall['area']);
        $hallFull = strtolower($hall['location_full']);
        if (strpos($hallArea, $locLower) === false && strpos($hallFull, $locLower) === false) {
            $locationMatch = false;
        }
    }
    
    $budgetMatch = true;
    if (!empty($search_budget) && is_numeric($search_budget)) {
        if ($hall['price'] > (int)$search_budget) {
            $budgetMatch = false;
        }
    }
    
    return $locationMatch && $budgetMatch;
});

$filtered_halls = array_values($filtered_halls);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Hall Management | Find Your Perfect Venue</title>
    <link rel="stylesheet" href="styles.css">

      <style>
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        .success-popup {
            background: white;
            border-radius: 1.5rem;
            max-width: 450px;
            width: 90%;
            text-align: center;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
            border-top: 8px solid #4caf50;
        }
        
        .success-popup .checkmark {
            font-size: 4rem;
            margin-bottom: 0.5rem;
        }
        
        .success-popup h2 {
            color: #4caf50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .success-popup p {
            color: #4a3729;
            margin: 0.5rem 0;
            line-height: 1.5;
        }
        
        .success-popup .hall-name {
            font-weight: 700;
            color: #8B3A3A;
            font-size: 1.2rem;
            margin: 0.5rem 0;
        }
        
        .close-success {
            background: #8B3A3A;
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        
        .close-success:hover {
            background: #6b2a2a;
            transform: scale(0.98);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
        }
        
        .leaflet-popup-content {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <div class="top-bar">
            <div class="logo-area">
                <h1>💒 Wedding Hall Management</h1>
            </div>
            <div class="nav-links">
                <a href="index.php" class="active-nav"> Home</a>
                <a href="mybooking.php" class="bookings-link"> My Bookings</a>
                <a href="?logout=1" class="logout-link"> Log out</a>
            </div>
        </div>

        <div class="welcome-row">
            <div class="welcome-text">
                <h2>Welcome, <span><?php echo $username; ?>!</span> 🎉</h2>
                <p>Find your perfect venue!</p>
            </div>
            
        </div>

        <div class="search-card">
            <form class="search-form" method="GET" action="index.php">
                <div class="input-group">
                    <label>📍 Search by Location Area</label>
                    <input type="text" name="search_location" placeholder="e.g. Teluk Mas, Alai, Ayer Keroh" 
                           value="<?php echo htmlspecialchars($search_location); ?>">
                </div>
                <div class="input-group">
                    <label>💰 Search by Budget (Max Price)</label>
                    <input type="number" name="max_budget" placeholder="e.g., 20000" step="1000" 
                           value="<?php echo htmlspecialchars($search_budget); ?>">
                </div>
                <div class="input-group">
                    <button type="submit" class="search-btn">🔍 Search Venues</button>
                </div>
            </form>
            <?php if (!empty($search_location) || !empty($search_budget)): ?>
                <div class="filter-info">
                    <a href="index.php" class="clear-filters">✖ Clear all filters</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="halls-section">
            <h3>✨ Available Wedding Halls </h3>
            
            <?php if (count($filtered_halls) > 0): ?>
                <div class="halls-grid">
                    <?php foreach ($filtered_halls as $hall): ?>
                        <div class="hall-card">
                          
                            <div class="hall-info">
                                <h4 class="hall-title"><?php echo htmlspecialchars($hall['name']); ?></h4>
                                <div class="location">
                                    <span>📍</span> <?php echo htmlspecialchars($hall['location_full']); ?>
                                </div>
                                <div class="capacity">
                                    👥 Capacity: <?php echo number_format($hall['capacity']); ?> guests
                                </div>
                                <div class="price">
                                    From <?php echo htmlspecialchars($hall['price_text']); ?>
                                </div>
                                <p class="hall-description"><?php echo htmlspecialchars($hall['description']); ?></p>
                                <div class="special-offer"><?php echo $hall['special_offer']; ?></div>
                                
                                <?php if ($hall['id'] == 1): ?>
                                    <a href="booking.php?hall_id=<?php echo $hall['id']; ?>&hall_name=<?php echo urlencode($hall['name']); ?>" 
                                       class="book-btn book-btn-active">
                                        📅 Book Now 
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="book-btn book-btn-disabled" 
                                       onclick="return false;">
                                        📅 Book Now (Coming Soon)
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    
                    <h4>No halls match your criteria</h4>
                    <p>Try adjusting your location or budget filters</p>
                    <a href="index.php" class="reset-link">View all halls →</a>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            <p>© 2026 Wedding Hall Management System — Making your dream wedding a reality in Melaka</p>
        </footer>
    </div>

    <?php if ($show_success): ?>
    <div class="success-overlay" id="successOverlay">
        <div class="success-popup">
            <div class="checkmark">✅🎉</div>
            <h2>Booking Confirmed!</h2>
            <p>Your booking for</p>
            <div class="hall-name">"<?php echo htmlspecialchars($booked_hall); ?>"</div>
            <p>has been successfully confirmed!</p>
            <p style="font-size: 0.85rem; color: #8B3A3A; margin-top: 0.5rem;">
                ✨ A confirmation email has been sent to your inbox ✨
            </p>
            <button class="close-success" onclick="closeSuccess()">Continue Browsing</button>
        </div>
    </div>
    
    <script>
        function closeSuccess() {
            document.getElementById('successOverlay').style.display = 'none';
        }
        document.getElementById('successOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>