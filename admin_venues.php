<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wedding_db";
$port = 3307;

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);


if (!$conn) {
    die("Sambungan database gagal: " . mysqli_connect_error());
}


$sql = "SELECT * FROM venue";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Venues Management</title>
    
    <style>
        
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #710349; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn-action { padding: 5px 10px; color: white; background-color: #710349; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>Admin Dashboard - Manage Venues</h2>
        <p>Selamat datang, Admin. Berikut adalah senarai venue/kenderaan yang aktif.</p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Venue</th>
                    <th>Lokasi / Jenis</th>
                    <th>Harga</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                if (mysqli_num_rows($result) > 0) {
                    
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["venue_name"] . "</td>";
                        echo "<td>" . $row["location"] . "</td>";   
                        echo "<td>RM " . $row["price"] . "</td>";    
                        echo "<td><button class='btn-action'>Edit</button> <button class='btn-action' style='background-color:red;'>Delete</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Tiada data dijumpai</td></tr>";
                }
                
                
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>