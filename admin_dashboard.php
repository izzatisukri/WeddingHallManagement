<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_name'] = 'Admin';
}

$admin_name = htmlspecialchars($_SESSION['admin_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Wedding Hall Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Georgia', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f0e8;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header - same as index page */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #8B3A3A;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 24px;
            color: #333;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        /* Navigation Menu */
        .nav-menu {
            background: white;
            border-radius: 8px;
            padding: 15px 25px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .nav-menu a {
            color: #8B3A3A;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 5px;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-menu a:hover {
            background: #8B3A3A;
            color: white;
            border-radius: 5px;
        }

        .nav-menu a.active {
            background: #8B3A3A;
            color: white;
            border-radius: 5px;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #8B3A3A;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e0d0;
        }

        /* Filter Section */
        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: flex-end;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #8B3A3A;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
            cursor: pointer;
            background: white;
        }

        .btn-generate {
            background: #8B3A3A;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-generate:hover {
            background: #6b2a2a;
        }

        /* Booking Stats */
        .booking-stats {
            text-align: center;
            padding: 30px;
            background: #faf6f0;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .booking-number {
            font-size: 52px;
            font-weight: bold;
            color: #8B3A3A;
        }

        .booking-label {
            color: #666;
            margin-top: 8px;
            font-size: 14px;
        }

        /* Halls Grid*/
        .halls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .hall-card {
            background: #faf6f0;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e8dccc;
            transition: transform 0.3s;
        }

        .hall-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .hall-name {
            font-size: 18px;
            font-weight: 600;
            color: #8B3A3A;
            margin-bottom: 15px;
        }

        .hall-status {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .hall-status span {
            font-size: 14px;
            font-weight: normal;
            color: #666;
        }

        hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #e8dccc;
        }

        .hall-detail {
            font-size: 13px;
            color: #666;
        }

        /* Welcome box */
        .welcome-box {
            background: linear-gradient(135deg, #8B3A3A, #6b2a2a);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .welcome-box h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .welcome-box p {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1> Wedding Hall Management</h1>
        </div>

        <div class="nav-menu">
            <a href="#" class="active">Dashboard</a>
            <a href="#">All venues</a>
            <a href="#">All Users</a>
            <a href="#">All Bookings</a>
            <a href="#">Log out</a>
        </div>

        <div class="welcome-box">
            <h3>Welcome, <?php echo $admin_name; ?>🎉 </h3>
            <p>Manage users, venues and generate reports.</p>
        </div>

        <div class="card">
            <div class="card-title">Monthly Booking Report</div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <label>Month ▼</label>
                    <select id="month">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12" selected>December</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Year ▼</label>
                    <select id="year">
                        <option value="2023">2023</option>
                        <option value="2024" selected>2024</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
                
                <button class="btn-generate" onclick="generateReport()">Generate Report</button>
            </div>

            <div class="booking-stats">
                <div class="booking-number" id="bookingCount"></div>
                <div class="booking-label">Number of booking</div>
            </div>

            <div class="halls-grid">
                <div class="hall-card">
                    <div class="hall-name">Dewan Serbaguna Teluk Mas</div>
                    <div class="hall-status">0 <span>Bookings</span></div>
                    <hr>
                    <div class="hall-detail">No bookings yet</div>
                </div>
                
                <div class="hall-card">
                    <div class="hall-name">Dahlia Wedding Hall</div>
                    <div class="hall-status">0 <span>Bookings</span></div>
                    <hr>
                    <div class="hall-detail">Active venue</div>
                </div>

                    <div class="hall-card">
                    <div class="hall-name">Gangsa Grand Ballroom</div>
                    <div class="hall-status">0 <span>Bookings</span></div>
                    <hr>
                    <div class="hall-detail">Active venue</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generateReport() {
            var month = document.getElementById('month').value;
            var year = document.getElementById('year').value;
            
            // Sample data
            var bookingData = {
                '1': 5, '2': 8, '3': 12, '4': 15, '5': 18, '6': 22,
                '7': 25, '8': 28, '9': 24, '10': 30, '11': 35, '12': 28
            };
            
            var count = bookingData[month] || 0;
            document.getElementById('bookingCount').innerText = count;
            
            // Popup message
            var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                             'July', 'August', 'September', 'October', 'November', 'December'];
            var monthName = monthNames[parseInt(month) - 1];
            
            alert('Report generated for ' + monthName + ' ' + year + '\n\nTotal Bookings: ' + count);
        }
    </script>
</body>
</html>