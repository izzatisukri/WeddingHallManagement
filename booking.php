<?php
// =========================================================
// 1. SAMBUNGAN DATABASE TERUS (Mengikut Setting Port 3307 & DB Awak)
// =========================================================
$host = "localhost:3307"; // Mengikut setting port XAMPP laptop awak
$user = "root";
$pass = "";
$db_name = "wedding_db"; 

$conn = mysqli_connect($host, $user, $pass, $db_name); 

// Jika database gagal bersambung
if (!$conn) {
    die("<span style='color:red; font-weight:bold;'>Gagal sambung ke database! Pastikan MySQL di XAMPP telah di-START. Error: " . mysqli_connect_error() . "</span>");
}

// PROSES PADAM DATA DARIPADA DATABASE (Bila klik OK dekat Modal)
if (isset($_POST['confirm_delete_booking'])) {
    $del_id = $_POST['delete_booking_id'];
    
    // Menggunakan nama table 'booking' dan primary key 'booking_id'
    $delete_query = "DELETE FROM booking WHERE booking_id = '$del_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        // Refresh semula ke page booking dengan status deleted untuk trigger toast
        header("Location: booking.php?status=deleted");
        exit();
    }
}

// Semak status untuk paparkan Toast Notification
$show_toast = false;
if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $show_toast = true;
}

// Query mengambil data tempahan dengan JOIN untuk dapatkan nama dewan & pakej secara live
$query = "SELECT b.*, v.venue_name, p.package_name, p.package_price 
          FROM booking b 
          JOIN venue v ON b.venue_id = v.venue_id 
          LEFT JOIN package p ON b.package_id = p.package_id"; 

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Hall Management - All Bookings</title>
    <style>
        /* --- CSS RESET & FONTS (Kekal Sebiji) --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Georgia', serif;
        }

        body {
            background-color: #DCDCDC; /* Latar belakang kelabu cair bahagian luar */
            color: #333;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            padding-bottom: 50px;
        }

        /* --- NAVIGATION BAR (PUTIH MEMANJANG) --- */
        .header {
            background-color: #FFFFFF; 
            text-align: center;
            padding: 25px 10px;
            border-bottom: 1px solid #ddd;
            width: 100%;
        }

        .logo-area {
            font-size: 26px;
            margin-bottom: 20px;
            color: #111;
        }

        .nav-links a {
            text-decoration: none;
            color: #111;
            margin: 0 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .nav-links a.logout {
            color: #ff3333;
        }

        /* --- WELCOME BANNER (UNGU MANGGIS) --- */
        .welcome-banner {
            background-color: #710349; 
            color: white;
            text-align: center;
            margin: 40px auto;
            padding: 35px;
            border-radius: 25px;
            width: 90%;
            max-width: 1200px; 
        }

        .welcome-banner h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            font-size: 15px;
            opacity: 0.9;
        }

        /* --- SECTION ALL BOOKINGS --- */
        .bookings-section {
            background-color: #710349; 
            color: white;
            margin: 0 auto;
            padding: 35px;
            border-radius: 25px;
            width: 90%;
            max-width: 1200px; 
            position: relative;
            min-height: 250px; 
        }

        .bookings-section h3 {
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: normal;
        }

        /* --- JADUAL/TABLE --- */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 16px;
        }

        th, td {
            padding: 16px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); 
        }

        th {
            font-weight: normal;
        }

        th:last-child, td:last-child {
            text-align: center;
        }

        /* --- BUTANG DELETE --- */
        .btn-delete {
            background-color: #ff0000;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-delete:hover {
            background-color: #cc0000;
        }

        /* --- MODAL CONFIRMATION POPUP --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); 
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none; 
        }

        .modal-box {
            background-color: #C2B7B7; 
            width: 90%;
            max-width: 500px;
            padding: 40px 20px;
            text-align: center;
            border-radius: 12px;
        }

        .modal-box p {    
            font-size: 18px;
            color: #000000;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-around;
            max-width: 350px;
            margin: 0 auto;
        }

        .modal-btn {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #999;
            padding: 10px 40px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            width: 40%;
        }

        /* --- TOAST NOTIFICATION --- */
        .toast-notification {
            position: absolute;
            bottom: 15px;
            right: 20px;
            background-color: #35C95D; 
            color: white;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 14px;
            display: none; 
            z-index: 10;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <header class="header">
            <div class="logo-area">
                🌸 Wedding Hall Management - Admin Panel
            </div>
            <nav class="nav-links">
                <a href="#">Dashboard</a>
                <a href="admin.php">All venues</a>
                <a href="#">All Users</a>
                <a href="booking.php">All Bookings</a>
                <a href="#" class="logout">Log out</a>
            </nav>
        </header>

        <div class="welcome-banner">
            <h2>Welcome, Admin! 👑</h2>
            <p>Manage users, venues and generate reports.</p>
        </div>

        <div class="bookings-section">
            <h3>All Bookings</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Venue</th>
                        <th>Event Date</th>
                        <th>Package</th>
                        <th>Price (RM)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Memaparkan data tempahan dengan pembolehubah yang betul dari database
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            // Sediakan harga pakej atau tetapkan 0 jika tiada pakej digunapakai
                            $harga_pakej = isset($row['package_price']) ? $row['package_price'] : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['venue_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['package_name'] ? $row['package_name'] : 'No Package'); ?></td>
                            <td><?php echo htmlspecialchars(number_format($harga_pakej, 2)); ?></td>
                            <td><b style="color: #f39c12;"><?php echo htmlspecialchars($row['bookingstatus']); ?></b></td>
                            <td>
                                <button class="btn-delete" data-id="<?php echo $row['booking_id']; ?>" onclick="openModal(this)">Delete</button>
                            </td>
                        </tr>
                        <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 40px 0;'>No booking data found in the database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <div class="toast-notification" id="successToast">
                Booking Deleted Successfully!
            </div>
        </div>

    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal-box">
            <p>Are you sure you want to delete this booking?</p>
            <form method="POST" action="booking.php">
                <input type="hidden" name="delete_booking_id" id="modalDeleteId">
                <div class="modal-buttons">
                    <button type="submit" name="confirm_delete_booking" class="modal-btn" style="background-color: #710349; color: white; border: none;">OK</button> 
                    <button type="button" class="modal-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let idToDelete = null;

        function openModal(button) {
            idToDelete = button.getAttribute('data-id'); 
            // Masukkan ID ke dalam hidden input dalam modal form
            document.getElementById('modalDeleteId').value = idToDelete;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('modalDeleteId').value = '';
            idToDelete = null;
        }
    </script>

    <?php if ($show_toast): ?>
    <script>
        const toast = document.getElementById('successToast');
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
            // Bersihkan URL parameter status daripada browser bar
            window.history.replaceState({}, document.title, "booking.php");
        }, 3000);
    </script>
    <?php endif; ?>

</body>
</html>