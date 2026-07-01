<?php
// Memulakan sesi dan memanggil fail sambungan database
session_start();
include('db_connection.php');

// Contoh semakan login klien (pastikan $_SESSION['client_id'] telah diisytiharkan semasa login)
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// 1. Ambil maklumat nama klien yang sedang log masuk
$client_name = "User";
$client_query = "SELECT client_name FROM client WHERE client_id = '$client_id'";
$client_result = mysqli_query($conn, $client_query);
if ($client_result && mysqli_num_rows($client_result) > 0) {
    $client_row = mysqli_fetch_assoc($client_result);
    $client_name = $client_row['client_name'];
}

// 2. Proses penghantaran borang tempahan (Booking Submission)
$booking_submitted = false;
$msg_toast = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_type']) && $_POST['action_type'] == 'submit_booking') {
    $venue_id = mysqli_real_escape_string($conn, $_POST['book_venue_id']);
    // Mencari package_id pertama yang sepadan dengan venue tersebut sebagai default pakej
    $pkg_query = "SELECT package_id FROM package WHERE venue_id = '$venue_id' LIMIT 1";
    $pkg_res = mysqli_query($conn, $pkg_query);
    $package_id = ($pkg_res && mysqli_num_rows($pkg_res) > 0) ? mysqli_fetch_assoc($pkg_res)['package_id'] : "NULL";

    $event_date = mysqli_real_escape_string($conn, $_POST['book_date']);
    $num_of_guests = mysqli_real_escape_string($conn, $_POST['book_guests']);
    $booking_date = date('Y-m-d');
    $bookingstatus = "Pending";

    // SEMAKAN KAPASITI VENUE DI PIHAK PHP (SERVER-SIDE)
    $capacity_query = "SELECT venue_capacity, venue_name FROM venue WHERE venue_id = '$venue_id'";
    $capacity_res = mysqli_query($conn, $capacity_query);
    $venue_data = mysqli_fetch_assoc($capacity_res);
    $max_capacity = isset($venue_data['venue_capacity']) ? (int)$venue_data['venue_capacity'] : 0;

    if ($num_of_guests > $max_capacity) {
        $booking_submitted = false;
        $msg_toast = "Error: Total guests exceeded the venue's maximum capacity of " . number_format($max_capacity) . " pax!";
    } 
    else {
        $check_duplicate_query = "SELECT * FROM booking 
                                  WHERE (venue_id = '$venue_id' AND event_date = '$event_date' AND bookingstatus != 'Rejected') 
                                  OR (client_id = '$client_id' AND event_date = '$event_date' AND bookingstatus != 'Rejected')";
        $check_duplicate_res = mysqli_query($conn, $check_duplicate_query);

        if ($check_duplicate_res && mysqli_num_rows($check_duplicate_res) > 0) {
            $booking_submitted = false;
            $msg_toast = "Error: The venue is already booked for this date, or you already have a booking on this date!";
        } else {
            $insert_query = "INSERT INTO booking (client_id, venue_id, package_id, booking_date, event_date, num_of_guests, bookingstatus) 
                             VALUES ('$client_id', '$venue_id', $package_id, '$booking_date', '$event_date', '$num_of_guests', '$bookingstatus')";
            if (mysqli_query($conn, $insert_query)) {
                $booking_submitted = true;
                $msg_toast = "Booking submitted! Waiting for owner approval.";
            } else {
                $msg_toast = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// 3. Ambil data senarai dewan (Venues) beserta pakej pertama untuk dipaparkan di web
// DI SINI KITA TAMBAH: WHERE v.venue_status = 'approved' supaya pending & rejected tidak akan appear
$venues_list = [];
$venue_sql = "SELECT v.*, p.package_name, p.package_price, p.package_id 
              FROM venue v 
              LEFT JOIN package p ON v.venue_id = p.venue_id 
              WHERE v.venue_status = 'approved'
              GROUP BY v.venue_id";
$venue_result = mysqli_query($conn, $venue_sql);
if ($venue_result) {
    while ($row = mysqli_fetch_assoc($venue_result)) {
        $venues_list[] = $row;
    }
}

// 4. Ambil semua maklumat pakej secara terperinci untuk kegunaan JavaScript modal preview
$packages_details = [];
$pkg_sql = "SELECT * FROM package";
$pkg_result = mysqli_query($conn, $pkg_sql);
if ($pkg_result) {
    while ($row = mysqli_fetch_assoc($pkg_result)) {
        $packages_details[$row['package_id']] = $row;
    }
}

// 5. Ambil data senarai tempahan milik klien ini (My Bookings)
$my_bookings = [];
$booking_sql = "SELECT b.*, v.venue_name, p.package_name 
                FROM booking b
                JOIN venue v ON b.venue_id = v.venue_id
                LEFT JOIN package p ON b.package_id = p.package_id
                WHERE b.client_id = '$client_id'
                ORDER BY b.booking_id DESC";
$booking_result = mysqli_query($conn, $booking_sql);
if ($booking_result) {
    while ($row = mysqli_fetch_assoc($booking_result)) {
        $my_bookings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Wedding Hall Management</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f4f5f7;
            color: #2d3748;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            padding-bottom: 60px;
        }

        .header {
            background-color: #ffffff;
            padding: 30px 5% 20px 5%;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .logo-area {
            font-size: 26px;
            font-weight: 700;
            color: #4a042e;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: #718096;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 6px;
            position: relative;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #710349;
        }

        .nav-links a.active {
            color: #710349;
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #710349;
            border-radius: 2px;
        }

        .nav-links a.logout {
            color: #e53e3e;
        }

        .nav-links a.logout:hover {
            color: #c53030;
        }

        .welcome-banner {
            background: #5c0632;
            /* Warna ungu/maroon pekat sepadan dengan Admin */
            margin: 40px auto 25px auto;
            padding: 40px 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            text-align: center;
            /* Teks disusun di tengah-tengah */
            color: #ffffff;
            /* Warna teks putih sepenuhnya */
        }

        .welcome-text h2 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-text p {
            font-size: 15px;
            color: #f1f1f1;
            opacity: 0.9;
        }

        /* 4. MAIN CONTENT AREA */
        .content-section {
            margin: 0 auto;
            width: 90%;
            max-width: 1200px;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .home-layout {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 768px) {
            .home-layout {
                grid-template-columns: 1fr;
            }
        }

        .search-filter-box,
        .map-box,
        .inner-card-section {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .map-box {
            height: 480px;
            padding: 10px;
        }

        .map-box iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 12px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #edf2f7;
        }

        .venue-list-wrapper {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .venue-row-card {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .venue-row-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .v-details h4 {
            font-size: 17px;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .v-details p {
            font-size: 14px;
            color: #718096;
            margin-bottom: 3px;
        }

        .v-price-tag {
            font-weight: 700;
            color: #710349;
            font-size: 15px;
            margin-top: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #4a5568;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #710349;
            box-shadow: 0 0 0 3px rgba(113, 3, 73, 0.15);
        }

        .btn-primary {
            background-color: #710349;
            color: white;
            border: none;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background-color: #520235;
        }

        .btn-cancel {
            background-color: #ffffff;
            color: #4a5568;
            border: 1px solid #cbd5e0;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background-color: #f7fafc;
        }

        .btn-close-purple {
            background-color: #710349;
            color: white;
            border: none;
            padding: 8px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: block;
            margin: 15px auto 0 auto;
        }

        .btn-close-purple:hover {
            background-color: #520235;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 32, 44, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .my-booking-details-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-top: 15px;
        }

        .booking-item-row {
            margin-bottom: 10px;
            font-size: 15px;
            color: #4a5568;
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-fade-in {
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-box-booking-new {
            background-color: #f8f9fa;
            width: 90%;
            max-width: 460px;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            animation: modalSlideUp 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .modal-main-title {
            font-family: 'Georgia', serif;
            font-size: 24px;
            font-weight: normal;
            color: #1a202c;
            margin-bottom: 18px;
            text-align: left;
        }

        .modal-venue-info {
            margin-bottom: 18px;
            text-align: left;
        }

        .modal-venue-info h4 {
            font-family: 'Georgia', serif;
            font-size: 16px;
            color: #1a202c;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .modal-venue-info p {
            font-size: 13.5px;
            color: #4a5568;
        }

        .form-group-new {
            margin-bottom: 14px;
            text-align: left;
        }

        .form-group-new label {
            display: block;
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group-new input,
        .form-group-new select,
        .form-group-new textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 13.5px;
            background-color: #ffffff;
            color: #2d3748;
            transition: border-color 0.2s;
        }

        .form-group-new input:focus,
        .form-group-new select:focus,
        .form-group-new textarea:focus {
            outline: none;
            border-color: #710349;
        }

        .form-actions-new {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-action {
            width: 100%;
            padding: 11px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            border: none;
            transition: background 0.2s;
        }

        .btn-book-now {
            background-color: #710349;
            color: #ffffff;
        }

        .btn-book-now:hover {
            background-color: #520235;
        }

        .btn-cancel-new {
            background-color: #a0aec0;
            color: #ffffff;
        }

        .btn-cancel-new:hover {
            background-color: #8a99ad;
        }

        .logout-box-container {
            background-color: #ffffff;
            width: 90%;
            max-width: 440px;
            border-radius: 16px;
            padding: 35px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            animation: modalSlideUp 0.25s ease-out;
            border: none;
        }

        .logout-title {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 500;
            padding: 0 5px;
        }

        .logout-buttons-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
        }

        .btn-logout-cancel {
            background-color: #ffffff;
            color: #4a5568;
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #cbd5e0;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-logout-cancel:hover {
            background-color: #f7fafc;
            border-color: #a0aec0;
        }

        .btn-logout-confirm {
            background-color: #e53e3e;
            color: #ffffff;
            border: none;
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-logout-confirm:hover {
            background-color: #cd3838;
        }

        @media (min-width: 400px) {
            .form-actions-new {
                flex-direction: row;
                justify-content: flex-start;
            }

            .btn-book-now {
                order: 1;
                width: auto;
                padding: 11px 24px;
            }

            .btn-cancel-new {
                order: 2;
                width: auto;
                padding: 11px 24px;
            }
        }

        .toast-notification {
            position: fixed;
            top: 30px;
            right: 30px;
            background-color: #ffffff;
            color: #4a5568;
            padding: 18px 28px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1010;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e2e8f0;
            line-height: 1.4;
            visibility: hidden;
            opacity: 0;
            transform: translateY(-15px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .toast-notification.show-toast {
            visibility: visible;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .toast-success-icon {
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .hidden-table-holder {
            display: none;
        }

        .view-pic-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #4a5568;
            text-decoration: none;
            font-size: 13.5px;
            margin-top: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s;
        }

        .view-pic-link:hover {
            color: #710349;
        }

        .gallery-modal-box {
            position: relative;
            max-width: 650px;
            width: 90%;
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            text-align: center;
            animation: modalSlideUp 0.3s ease-out;
        }

        .gallery-img-container {
            width: 100%;
            height: 380px;
            background-color: #edf2f7;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .gallery-img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(26, 32, 44, 0.6);
            color: #ffffff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, transform 0.1s;
            user-select: none;
            z-index: 10;
        }

        .nav-arrow:hover {
            background-color: rgba(113, 3, 73, 0.9);
        }

        .nav-arrow:active {
            transform: translateY(-50%) scale(0.9);
        }

        .arrow-left {
            left: 15px;
        }

        .arrow-right {
            right: 15px;
        }

        .gallery-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="logo-area">🌸 Wedding Hall Management</div>
            <div class="nav-links">
                <a id="nav-home" class="active" onclick="switchTab('home')">Home</a>
                <a id="nav-bookings" onclick="openMyBookingsModal()">My Bookings</a>
                <a class="logout" onclick="openLogoutModal()">Log out</a>
            </div>
        </div>

        <div class="welcome-banner">
            <div class="welcome-text">

                <h2>Welcome, <?php echo htmlspecialchars($client_name); ?>!
                    👰🏻‍♀️🤵🏻</h2>
                <p>Find your perfect wedding venues!</p>
            </div>
        </div>

        <div class="content-section">
            <div class="home-layout">

                <div class="search-filter-box">
                    <div class="section-title">Filter Options</div>

                    <form id="filter-form" onsubmit="event.preventDefault(); searchVenueMap();">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Location / Area</label>

                                <input type="text" id="filter-area" placeholder="e.g., Telok Mas, Alor Gajah, Melaka">
                            </div>
                            <div class="form-group">

                                <label>Max Budget (RM)</label>
                                <input type="number" id="filter-budget" placeholder="e.g., 20000">
                            </div>
                        </div>

                        <button type="button" class="btn-primary" style="width: 100%;"
                            onclick="searchVenueMap()">Search & Update Map</button>
                    </form>

                    <div class="venue-list-wrapper" id="venue-list-container">
                        <?php if (count($venues_list) > 0): ?>
                            <?php foreach ($venues_list as $venue): ?>

                                <div class="venue-row-card" data-area="<?php echo htmlspecialchars(strtolower($venue['venue_location'])); ?>" data-capacity="<?php echo $venue['venue_capacity']; ?>">
                                    <div class="v-details">
                                        <h4><?php echo htmlspecialchars($venue['venue_name']);
                                            ?></h4>
                                        <p>Full Address: <?php echo htmlspecialchars($venue['venue_location']);
                                                            ?></p>
                                        <p>Capacity: <?php echo htmlspecialchars(number_format($venue['venue_capacity']));
                                                        ?> pax</p>

                                        <div class="v-price-tag">

                                            <?php
                                            if (!empty($venue['package_name'])) {

                                                echo htmlspecialchars($venue['package_name']) . " (RM " . number_format($venue['package_price'], 2) . ")";
                                            } else {
                                                echo "Price: RM " .
                                                    number_format($venue['venue_price'], 2);
                                            }
                                            ?>
                                        </div>

                                        <a class="view-pic-link" onclick="openGallery('<?php echo htmlspecialchars(addslashes($venue['venue_name'])); ?>', '<?php echo htmlspecialchars(addslashes($venue['venue_image'])); ?>')">➔ view picture</a><br>


                                        <?php if (!empty($venue['package_id'])): ?>
                                            <a class="view-pic-link" onclick="previewPackageDetails('<?php echo $venue['package_id']; ?>')">➔ view package</a>

                                        <?php endif;
                                        ?>
                                    </div>
                                    <button class="btn-primary" onclick="openBookingModal('<?php echo htmlspecialchars(addslashes($venue['venue_name'])); ?>', '<?php echo $venue['venue_id']; ?>', '<?php echo htmlspecialchars(addslashes($venue['venue_location'])); ?>', '<?php echo $venue['venue_capacity']; ?>')">Book Venue</button>

                                </div>
                            <?php endforeach;
                            ?>
                        <?php else: ?>
                            <div style="text-align: center; color: #a0aec0; padding: 20px;">No venues available.</div>
                        <?php endif;
                        ?>
                    </div>

                </div>

                <div class="map-box">
                    <iframe
                        id="map-frame"

                        src="https://maps.google.com/maps?q=Malaysia&t=&z=6&ie=UTF8&iwloc=&output=embed"
                        allowfullscreen=""

                        loading="lazy">
                    </iframe>
                </div>

            </div>
        </div>
    </div>

    <div class="toast-notification" id="toast-global">
        <div class="toast-success-icon">

            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 0C4.48 0 0 4.48 0 
 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0;
 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z" fill="currentColor" />
            </svg>
        </div>
        <span>Booking confirmed!</span>
    </div>

    <div id="modal-customer-booking" class="modal-overlay">
        <div class="modal-box-booking-new">
            <h3 class="modal-main-title">Book Venue</h3>


            <div class="modal-venue-info">
                <h4 id="display-v-name">Dewan Serbaguna Telok Mas</h4>
                <p id="display-v-area">Telok Mas, Melaka</p>
                <p style="color: #710349; font-weight: 600; font-size: 13px;" id="display-v-capacity"></p>
            </div>


            <!-- DITAMBAH: onsubmit interceptor untuk jalankan validasi JS sebelum dihantar -->
            <form id="form-submit-booking" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validateGuestCapacity();">
                <input type="hidden" name="action_type" value="submit_booking">
                <input type="hidden" id="book-v-name">
                <input type="hidden" id="book-venue-id" name="book_venue_id">
                <!-- DITAMBAH: Input hidden untuk simpan nilai max capacity bagi semakan JS -->
                <input type="hidden" id="book-max-capacity">

                <div class="form-group-new">
                    <label>Full Name</label>
                    <input type="text" id="book-fullname" placeholder="Enter your full name" required>

                </div>

                <div class="form-group-new">
                    <label>Phone Number</label>
                    <input type="tel" id="book-phone" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group-new">

                    <label>Email Address</label>

                    <input type="email" id="book-email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group-new">
                    <label>Event Date</label>
                    <input type="date" id="book-date" name="book_date" required>

                </div>


                <div class="form-group-new">
                    <label>Number of Guest (est.)</label>
                    <input type="number" id="book-guests" name="book_guests" placeholder="Estimated guests" required>
                </div>

                <div class="form-actions-new">

                    <button type="submit" class="btn-action btn-book-now">Booking Now</button>

                    <button type="button" class="btn-action btn-cancel-new" onclick="closeModal('modal-customer-booking')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-my-bookings" class="modal-overlay">
        <div class="modal-box-booking modal-fade-in" style="max-width: 450px;
background-color: #ffffff; border-radius: 16px; padding: 35px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <h3 id="modal-booking-title" style="text-align: center;
font-size: 20px; margin-bottom: 20px; color: #1a202c;">My Booking</h3>

            <div class="my-booking-details-box">
                <div id="dynamic-booking-details">
                </div>
            </div>

            <button type="button" class="btn-close-purple" onclick="closeModal('modal-my-bookings')">Close</button>
        </div>
    </div>

    <div id="modal-logout-confirm" class="modal-overlay">
        <div class="logout-box-container">
            <div class="logout-title">


                Are you sure you want to log out? You will need to login again to access the panel.
            </div>
            <div class="logout-buttons-group">
                <button class="btn-logout-cancel" onclick="closeModal('modal-logout-confirm')">Cancel</button>
                <button class="btn-logout-confirm" onclick="executeLogout()">Log Out</button>
            </div>
        </div>
    </div>

    <div id="modal-image-gallery" class="modal-overlay">
        <div class="gallery-modal-box">
            <div class="gallery-img-container">


                <button class="nav-arrow arrow-left" onclick="changeImage(-1)">❮</button>
                <img id="gallery-current-img" src="" alt="Hall Image">
                <button class="nav-arrow arrow-right" onclick="changeImage(1)">❯</button>
            </div>
            <div id="gallery-display-title" class="gallery-title">Nama Dewan</div>
            <button type="button" class="btn-close-purple" onclick="closeModal('modal-image-gallery')">Close</button>
        </div>
    </div>

    <div class="hidden-table-holder">
        <table id="table-user-bookings">
            <tbody>

                <?php foreach ($my_bookings as $b_row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b_row['venue_name']);
                            ?></td>
                        <td><?php echo htmlspecialchars($b_row['package_name'] ?? 'N/A');
                            ?></td>
                        <td><?php echo htmlspecialchars($b_row['event_date']);
                            ?></td>
                        <td><?php echo htmlspecialchars($b_row['num_of_guests']);
                            ?></td>
                        <td><?php echo htmlspecialchars($b_row['bookingstatus']);
                            ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($b_row['booking_date'])));
                            ?></td>
                    </tr>
                <?php endforeach;
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Menyimpan data maklumat pakej daripada pangkalan data PHP ke format JSON JavaScript
        const dbPackages = <?php echo json_encode($packages_details);
                            ?>;

        function switchTab(tabType) {
            const homeNav = document.getElementById('nav-home');
            const bookingsNav = document.getElementById('nav-bookings');
            if (tabType === 'home') {
                homeNav.classList.add('active');
                bookingsNav.classList.remove('active');
            } else if (tabType === 'bookings') {
                bookingsNav.classList.add('active');
                homeNav.classList.remove('active');
            }
        }

        function searchVenueMap() {
            const searchInput = document.getElementById('filter-area').value.trim().toLowerCase();
            const mapFrame = document.getElementById('map-frame');

            if (searchInput === '') {
                alert('Please enter an area or venue name first!');
                return;
            }

            const formattedQuery = encodeURIComponent(searchInput);
            mapFrame.src = `https://maps.google.com/maps?q=${formattedQuery}%2C%20Malaysia&t=&z=14&ie=UTF8&iwloc=&output=embed`;

            const venueCards = document.querySelectorAll('.venue-row-card');
            let foundCount = 0;

            venueCards.forEach(card => {
                const venueArea = card.getAttribute('data-area').toLowerCase();
                const venueName = card.querySelector('h4').textContent.toLowerCase();

                // Semak sama ada input sepadan dengan kawasan ATAU nama dewan
                if (venueArea.includes(searchInput) || venueName.includes(searchInput)) {
                    card.style.display = 'flex';


                    foundCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            if (foundCount === 0) {
                triggerToast(`Map updated, but no venues found matching: ${document.getElementById('filter-area').value}`);
            } else {
                triggerToast(`Map updated & filtered: ${foundCount} venue(s) found!`);
            }
        }

        function openBookingModal(venueName, venueId, venueLocation, venueCapacity) {
            document.getElementById('modal-customer-booking').style.display = 'flex';
            document.getElementById('display-v-name').textContent = venueName;
            document.getElementById('book-v-name').value = venueName;
            document.getElementById('book-venue-id').value = venueId;
            document.getElementById('display-v-area').textContent = venueLocation;

            document.getElementById('book-max-capacity').value = venueCapacity;
            document.getElementById('display-v-capacity').textContent = `Maximum Capacity: ${parseInt(venueCapacity).toLocaleString()} pax`;

            document.getElementById('book-guests').setAttribute('max', venueCapacity);
        }

        function validateGuestCapacity() {
            const guestsInput = document.getElementById('book-guests');
            const maxCapacityInput = document.getElementById('book-max-capacity');

            const guests = parseInt(guestsInput.value);
            const maxCapacity = parseInt(maxCapacityInput.value);

            if (guests > maxCapacity) {
                alert(`Booking rejected! The maximum capacity allowed for this venue is ${maxCapacity.toLocaleString()} pax.`);
                guestsInput.focus();
                return false;
            }
            return true;
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'modal-my-bookings') {
                switchTab('home');
            }
        }

        function submitCustomerBooking() {
            const date = document.getElementById('book-date').value;
            const guests = document.getElementById('book-guests').value;

            if (date === '' || guests === '') {
                alert('Please fill in all required information!');
                return;
            }

            if (validateGuestCapacity()) {
                document.getElementById('form-submit-booking').submit();
            }
        }

        function openMyBookingsModal() {
            switchTab('bookings');
            document.getElementById('modal-booking-title').textContent = "My Booking";
            const rows = document.querySelectorAll('#table-user-bookings tbody tr');
            const targetDetailsContainer = document.getElementById('dynamic-booking-details');
            if (rows.length === 0) {
                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row" style="text-align: center; color: #a0aec0; margin-top: 10px;">
                    <em>No upcoming bookings found.</em>
                </div>
            `;
            } else {
                const latestRow = rows[rows.length - 1];
                const cells = latestRow.querySelectorAll('td');

                const statusText = cells[4].textContent;
                const statusColor = statusText === 'Pending' ? '#d97706' : '#db2777';
                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row"><strong>Event Date :</strong> ${cells[2].textContent}</div>
                <div class="booking-item-row"><strong>Package :</strong> ${cells[1].textContent}</div>
                <div class="booking-item-row"><strong>Guest :</strong> ${cells[3].textContent.replace(' pax', '')}</div>
                <div class="booking-item-row"><strong>Status :</strong> <span style="color: ${statusColor}; font-weight: bold;">${statusText}</span></div>
               
 
                <div class="booking-item-row"><strong>Booked On :</strong> ${cells[5].textContent}</div>
            `;
            }

            document.getElementById('modal-my-bookings').style.display = 'flex';
        }

        function previewPackageDetails(packageId) {
            switchTab('bookings');
            document.getElementById('modal-booking-title').textContent = "Package";
            const targetDetailsContainer = document.getElementById('dynamic-booking-details');

            const pkg = dbPackages[packageId];

            if (pkg) {
                let inclusionsHtml = '';
                if (pkg.package_inclusions) {
                    const inclusionsArray = pkg.package_inclusions.split(/[,\n]+/);
                    inclusionsArray.forEach(item => {
                        if (item.trim() !== "") {
                            inclusionsHtml += `<div class="booking-item-row" style="padding-left: 15px; margin-bottom: 2px;">- ${item.trim()}</div>`;
                        }
                    });
                }

                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row"><strong>Package Name:</strong> ${pkg.package_name}</div>
                <div class="booking-item-row"><strong>Price:</strong> RM ${parseFloat(pkg.package_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                <div class="booking-item-row" style="margin-top: 10px;"><strong>Package Inclusions:</strong></div>
                ${inclusionsHtml}
          
   `;
            } else {
                targetDetailsContainer.innerHTML = `<div class="booking-item-row">No package details found.</div>`;
            }

            document.getElementById('modal-my-bookings').style.display = 'flex';
        }

        function openLogoutModal() {
            document.getElementById('modal-logout-confirm').style.display = 'flex';
        }

        function executeLogout() {
            window.location.href = 'login.php';
        }

        function triggerToast(message) {

            const toast = document.getElementById('toast-global');
            if (toast) {
                toast.querySelector('span').textContent = message;
                toast.classList.add('show-toast');
                setTimeout(() => {
                    toast.classList.remove('show-toast');
                }, 3000);
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                if (event.target.id === 'modal-my-bookings') {
                    switchTab('home');
                }
            }
        }

        const venueImages = {
            "Dewan Serbaguna Telok Mas": [
                "images/weddingVenue.jpg",
                "images/pelamin.jpg",
            ],
            "Dahlia Wedding Hall": [
                "images/weddingVenue1.jpg",

                "images/pelamin1.jpg",

            ]
        };
        let currentVenue = "";
        let currentImgIndex = 0;
        let dynamicImgList = [];
        function openGallery(venueName, databaseImage) {
            currentVenue = venueName;
            currentImgIndex = 0;
            dynamicImgList = [];

            const pathFolder = "images/";
            if (databaseImage && databaseImage.trim() !== "") {
                dynamicImgList.push(pathFolder + databaseImage);
            } else if (venueImages[venueName]) {
                dynamicImgList = venueImages[venueName];
            }

            document.getElementById('modal-image-gallery').style.display = 'flex';
            updateGalleryContent();
        }

        function updateGalleryContent() {
            const imgElement = document.getElementById('gallery-current-img');
            const titleElement = document.getElementById('gallery-display-title');

            if (dynamicImgList && dynamicImgList.length > 0) {
                imgElement.src = dynamicImgList[currentImgIndex];
                titleElement.textContent = `${currentVenue} (Picture ${currentImgIndex + 1} of ${dynamicImgList.length})`;
            } else {
                imgElement.src = "https://via.placeholder.com/600x380?text=No+Image+Available";
                titleElement.textContent = currentVenue;
            }
        }

        function changeImage(direction) {
            if (!dynamicImgList || dynamicImgList.length <= 1) return;
            currentImgIndex += direction;
            if (currentImgIndex >= dynamicImgList.length) {
                currentImgIndex = 0;
            } else if (currentImgIndex < 0) {
                currentImgIndex = dynamicImgList.length - 1;
            }

            updateGalleryContent();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('book-date');
            if (dateInput) {
                const targetDate = new Date();

                targetDate.setMonth(targetDate.getMonth() + 3);



                const yyyy = targetDate.getFullYear();
                const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
                const dd = String(targetDate.getDate()).padStart(2, '0');

                const minDate = `${yyyy}-${mm}-${dd}`;


                dateInput.setAttribute('min', minDate);

            }

            <?php if ($booking_submitted || !empty($msg_toast)): ?>
                triggerToast('<?php echo $msg_toast; ?>');
            <?php endif; ?>
        });
    </script>

</body>
</html>