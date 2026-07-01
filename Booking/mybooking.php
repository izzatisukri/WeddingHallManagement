<?php
session_start();

if (isset($_SESSION['booking_success']) && $_SESSION['booking_success'] === true) {
    $popup_message = $_SESSION['booking_message'] ?? 'Your booking has been confirmed!';
    echo "<script>
        window.onload = function() {
            alert('🎉 " . addslashes($popup_message) . "\\n\\nA confirmation email has been sent to your email address.');
        };
    </script>";
    unset($_SESSION['booking_success']);
    unset($_SESSION['booking_message']);
}

if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'User';
}
$username = htmlspecialchars($_SESSION['user_name']);
$has_booking = isset($_SESSION['last_booking']) && isset($_SESSION['booked_hall']);
$booking = null;
$booked_hall = null;

if ($has_booking) {
    $booking = $_SESSION['last_booking'];
    $booked_hall = $_SESSION['booked_hall'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Wedding Hall Management</title>
    <style>
        .bookings-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
        }
        
        .bookings-header {
            background: linear-gradient(135deg, #8B3A3A, #6b2a2a);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .bookings-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .no-bookings {
            text-align: center;
            padding: 3rem;
        }
        
        .no-bookings h3 {
            color: #8B3A3A;
            margin: 1rem 0;
        }
        
        .empty-icon {
            font-size: 4rem;
        }
        
        /* Active Booking Card Styles */
        .active-booking {
            padding: 2rem;
        }
        
        .booking-card {
            background: #faf6f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid #4caf50;
            position: relative;
            overflow: hidden;
        }
        
        .booking-card::before {
            content: "✓ ACTIVE";
            position: absolute;
            top: 10px;
            right: -30px;
            background: #4caf50;
            color: white;
            padding: 0.3rem 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            transform: rotate(45deg);
            width: 120px;
            text-align: center;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .booking-header h2 {
            color: #8B3A3A;
            font-size: 1.5rem;
        }
        
        .booking-status {
            background: #4caf50;
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .booking-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .detail-item {
            background: white;
            padding: 1rem;
            border-radius: 0.8rem;
            border: 1px solid #e8dccc;
        }
        
        .detail-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #8B3A3A;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #2c2418;
            margin-top: 0.3rem;
        }
        
        .message-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 1rem;
            border-radius: 0.8rem;
            margin: 1.5rem 0;
        }
        
        .message-box p {
            color: #2e7d32;
            margin: 0;
            font-weight: 500;
        }
        
        .booking-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        
        .btn-cancel {
            background: #ff6b6b;
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-cancel:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }
        
        .btn-view {
            background: #8B3A3A;
            color: white;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-view:hover {
            background: #6b2a2a;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: #8B3A3A;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #6b2a2a;
            transform: translateY(-2px);
        }
        
        .clear-booking {
            text-align: center;
            margin-top: 1rem;
        }
        
        .clear-link {
            color: #999;
            font-size: 0.8rem;
            text-decoration: none;
        }
        
        .clear-link:hover {
            color: #8B3A3A;
        }
        
        @media (max-width: 768px) {
            .booking-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .booking-details-grid {
                grid-template-columns: 1fr;
            }
            
            .active-booking {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <div class="bookings-container">
            <div class="bookings-header">
                <h1>📋 My Wedding Bookings</h1>
                <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
            </div>
            
            <?php if ($has_booking && $booking): ?>
                <div class="active-booking">
                    <div class="booking-card">
                        <div class="booking-header">
                            <h2>🎊 <?php echo htmlspecialchars($booked_hall); ?></h2>
                            <span class="booking-status">✓ CONFIRMED</span>
                        </div>
                        
                        <div class="booking-details-grid">
                            <div class="detail-item">
                                <div class="detail-label">📅 Event Date</div>
                                <div class="detail-value"><?php echo date('l, F j, Y', strtotime($booking['event_date'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">👥 Guest Count</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking['num_guests']); ?> guests</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">👤 Name</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking['name']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">📧 Email</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">📞 Phone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">💰 Total Price</div>
                                <div class="detail-value">RM 12,000 (Inclusive all taxes)</div>
                            </div>
                        </div>
                        
                        <div class="message-box">
                            <p>✨ <strong>Direct Message:</strong> Your booking has been successfully confirmed! A confirmation email has been sent to <?php echo htmlspecialchars($booking['email']); ?>. Our wedding coordinator will contact you within 24 hours to discuss further details.</p>
                        </div>
                        
                        <div class="booking-actions">
                            <a href="index.php" class="btn-view">🏠 Browse More Halls</a>
                            <button onclick="showCancelConfirm()" class="btn-cancel">❌ Cancel Booking</button>
                        </div>
                        
                        <div class="clear-booking">
                            <a href="clear-booking.php" class="clear-link" onclick="return confirm('Are you sure you want to clear this booking? This action cannot be undone.');">Clear booking from view</a>
                        </div>
                    </div>
                </div>
                
                <script>
                    function showCancelConfirm() {
                        if (confirm("⚠️ Are you sure you want to cancel your booking?\n\nThis action will cancel your reservation at <?php echo htmlspecialchars($booked_hall); ?>.\n\nPlease contact our support if this was a mistake.")) {
                            window.location.href = "clear-booking.php?cancel=true";
                        }
                    }
                </script>
                
            <?php else: ?>
                <div class="no-bookings">
                    <div class="empty-icon">📅</div>
                    <h3>No Active Bookings Yet</h3>
                    <p>Click <strong>"Book Now"</strong> to book your dream venue!</p>
                    <a href="index.php" class="btn-primary">🏠 Browse Halls</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>