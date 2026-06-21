<?php
session_start();


if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'User';
}
$username = htmlspecialchars($_SESSION['user_name']);

$hall_id = isset($_GET['hall_id']) ? (int)$_GET['hall_id'] : 1;
$hall_name = isset($_GET['hall_name']) ? urldecode($_GET['hall_name']) : 'Dewan Serbaguna Teluk Mas';

$hall_details = [
    'name' => $hall_name,
    'location' => 'Teluk Mas, Melaka',
    'capacity' => 700,
    'price_per_event' => 12000,
    'facilities' => ['Air Conditioning', 'Sound System', 'Stage', 'Parking', 'Catering Kitchen', 'Bridal Room']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = isset($_SESSION['user_name']) && $_SESSION['user_name'] != 'User' 
        ? $_SESSION['user_name'] 
        : $_POST['name'] ?? 'User';
    
    $user_email = isset($_SESSION['user_email']) 
        ? $_SESSION['user_email'] 
        : $_POST['email'] ?? 'user@example.com';
    
    $user_phone = $_POST['phone'] ?? 'Not provided';
    
    $booking_data = [
        'name' => $user_name,
        'email' => $user_email,
        'phone' => $user_phone,
        'package' => $_POST['package'],
        'event_date' => $_POST['event_date'],
        'num_guests' => $_POST['guest_count'],
        'hall_name' => $hall_name,
        'booking_date' => date('Y-m-d H:i:s')
    ];

    $_SESSION['last_booking'] = $booking_data;
    $_SESSION['booked_hall'] = $hall_name;
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_message'] = "✅ Your booking for $hall_name has been successfully confirmed!";
    
    header("Location: mybooking.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($hall_name); ?> | Wedding Hall Management</title>
    <style>
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
        }
        
        .booking-header {
            background: linear-gradient(135deg, #8B3A3A, #6b2a2a);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .booking-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .booking-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }
        
        .hall-details {
            background: #faf6f0;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e8dccc;
        }
        
        .hall-details h3 {
            color: #8B3A3A;
            margin-bottom: 1rem;
        }
        
        .price-large {
            font-size: 2rem;
            font-weight: 800;
            color: #8B3A3A;
            margin: 1rem 0;
        }
        
        .info-row {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e8dccc;
        }
        
        .facilities-list {
            list-style: none;
            padding: 0;
            margin-top: 1rem;
        }
        
        .facilities-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .special-box {
            background: #f5ede4;
            padding: 1rem;
            border-radius: 0.8rem;
            margin-top: 1.5rem;
            text-align: center;
            border: 1px solid #e8dccc;
        }
        
        .special-box strong {
            color: #8B3A3A;
        }
        
        .booking-form {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 2px solid #e8dccc;
        }
        
        .booking-form h3 {
            color: #8B3A3A;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a3729;
            font-size: 0.85rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1.5px solid #e8dccc;
            border-radius: 0.8rem;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8B3A3A;
            box-shadow: 0 0 0 2px rgba(139, 58, 58, 0.1);
        }
        
        .submit-btn {
            background: #8B3A3A;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 2rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            background: #6b2a2a;
            transform: translateY(-2px);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #8B3A3A;
            text-decoration: none;
            text-align: center;
            width: 100%;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        @media (max-width: 768px) {
            .booking-content {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <div class="booking-container">
            <div class="booking-header">
                <h1>📅 Book Your Dream Wedding</h1>
                <p><?php echo htmlspecialchars($hall_name); ?> - Teluk Mas, Melaka</p>
            </div>
            
            <div class="booking-content">
                <div class="hall-details">
                    <h3>✨ Venue Details</h3>
                    <div class="price-large">RM <?php echo number_format($hall_details['price_per_event']); ?></div>
                    <div class="info-row"><strong>👥 Capacity:</strong> Up to <?php echo number_format($hall_details['capacity']); ?> guests</div>
                    <div class="info-row"><strong>📍 Location:</strong> <?php echo $hall_details['location']; ?></div>
                    <div class="info-row"><strong>⏰ Duration:</strong> Full day (8am - 12am)</div>
                    
                    <h3 style="margin-top: 1.5rem;">🎯 Facilities:</h3>
                    <ul class="facilities-list">
                        <?php foreach ($hall_details['facilities'] as $facility): ?>
                            <li>✓ <?php echo $facility; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="special-box">
                        <strong>💝 Special Offer:</strong><br>
                        ✓ Free decoration (RM 2,000)<br>
                        ✓ Complimentary bridal suite<br>
                        ✓ Free wedding cake
                    </div>
                </div>
                
                <div class="booking-form">
                    <h3>📝 Complete Your Booking</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Choose Package *</label>
                            <select name="package" required>
                                <option value="">Select</option>
                                <option value="100-200">Basic Package-RM12,000</option>
                                <option value="200-400">Silver Package-RM15,000</option>
                                <option value="400-600">Gold Package-RM25,000</option>
                                <option value="600-700">Premium Package-RM32,000</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Wedding Date *</label>
                            <input type="date" name="event_date" required min="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Number of Guests (est.) *</label>
                            <input type="number" name="guest_count" placeholder="e.g, 200">
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" required id="terms">
                            <label for="terms">I agree to the terms</label>
                        </div>
                        
                        <button type="submit" class="submit-btn">✅ Confirm Booking</button>
                    </form>
                    <a href="index.php" class="back-link">← Back to Halls</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>