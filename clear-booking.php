<?php
session_start();

unset($_SESSION['last_booking']);
unset($_SESSION['booked_hall']);
unset($_SESSION['booking_success']);
unset($_SESSION['booked_hall_name']);

header("Location: booking.php");
exit();
?>