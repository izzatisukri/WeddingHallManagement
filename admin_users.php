<?php
$conn = new mysqli("localhost", "root", "", "wedding_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - All Users</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
</style>

</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo-area">🌸 Wedding Hall Management - Admin Panel</div>

        <div class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_venues.php">All Venues</a>
            <a href="admin_packages.php">All Packages</a>
            <a href="admin_users.php" class="active">All Users</a>
            <a href="admin_bookings.php">All Bookings</a>
        </div>
    </div>

    <div class="welcome-banner">
        <h2>Welcome, Admin! 👑</h2>
        <p>Manage users, venues and generate reports.</p>
    </div>

    <div class="content-section" style="display:block;">
        <div class="section-header">
            <div class="section-title-container">
                <h3>All Users</h3>

                <div class="sub-nav">
                    <span id="sub-chart" onclick="toggleView('chart')">Chart</span>
                    <span id="sub-list" class="active-sub" onclick="toggleView('list')">List</span>
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div id="view-chart" class="chart-container">
            <canvas id="userRoleChart"></canvas>
        </div>

        <!-- LIST -->
        <div id="view-list" style="display:block;">
            <table id="table-users">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($row = $result->fetch_assoc()) { ?>

                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo ucfirst($row['role']); ?></td>

                        <td>
                            <select class="status-select <?php echo ($row['status']=='Active') ? 'status-active' : 'status-suspended'; ?>"
                                    onchange="updateStatusStyle(this)">
                                <option value="Active" <?php if($row['status']=='Active') echo 'selected'; ?>>
                                    Active
                                </option>

                                <option value="Suspended" <?php if($row['status']=='Suspended') echo 'selected'; ?>>
                                    Suspended
                                </option>
                            </select>
                        </td>
                    </tr>

                <?php } ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TOAST -->
<div class="toast-notification" id="toast-global"></div>

<script>
let userPieChart = null;

function updateStatusStyle(selectElement) {
    if (selectElement.value === "Active") {
        selectElement.classList.add('status-active');
        selectElement.classList.remove('status-suspended');
        triggerToast("User status updated to Active!");
    } else {
        selectElement.classList.add('status-suspended');
        selectElement.classList.remove('status-active');
        triggerToast("User status updated to Suspended!");
    }
}

function toggleView(viewType) {
    const chartView = document.getElementById('view-chart');
    const listView = document.getElementById('view-list');

    if (viewType === 'chart') {
        chartView.style.display = 'block';
        listView.style.display = 'none';
        renderUserChart();
    } else {
        chartView.style.display = 'none';
        listView.style.display = 'block';
    }
}

function renderUserChart() {
    const rows = document.querySelectorAll('#table-users tbody tr');

    let clientCount = 0;
    let ownerCount = 0;

    rows.forEach(row => {
        const role = row.cells[3].textContent.trim().toLowerCase();
        if (role === 'client') clientCount++;
        if (role === 'owner') ownerCount++;
    });

    if (userPieChart) userPieChart.destroy();

    const ctx = document.getElementById('userRoleChart').getContext('2d');

    userPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Client', 'Owner'],
            datasets: [{
                data: [clientCount, ownerCount],
                backgroundColor: ['#710349', '#4a042e']
            }]
        }
    });
}

function triggerToast(message) {
    const toast = document.getElementById('toast-global');
    toast.textContent = message;
    toast.classList.add('show-toast');

    setTimeout(() => {
        toast.classList.remove('show-toast');
    }, 3000);
}
</script>

</body>
</html>

<?php $conn->close(); ?>