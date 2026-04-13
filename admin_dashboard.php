<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle delete request
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $getName = $conn->query("SELECT first_name, last_name FROM employee_details WHERE user_id=$user_id")->fetch_assoc();
    $empName = $getName ? $getName['first_name'] . " " . $getName['last_name'] : "Unknown";

    $stmt = $conn->prepare("DELETE FROM employee_details WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $action = "Deleted employee: " . $empName;
    $performed_by = $_SESSION['username'];
    $logStmt = $conn->prepare("INSERT INTO activity_logs (action, performed_by) VALUES (?, ?)");
    $logStmt->bind_param("ss", $action, $performed_by);
    $logStmt->execute();
    $logStmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

// ✅ Quick Stats
$totalEmployees = $conn->query("SELECT COUNT(*) AS total FROM employee_details")->fetch_assoc()['total'];
$recentEmployees = $conn->query("SELECT COUNT(*) AS recent FROM employee_details WHERE date_of_joining >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['recent'];
$adminsCount = $conn->query("SELECT COUNT(*) AS admins FROM users WHERE role='admin'")->fetch_assoc()['admins'];

// ✅ Quotes
$quotes = [
    "Leadership is not about being in charge. It is about taking care of those in your charge.",
    "The way to get started is to quit talking and begin doing. – Walt Disney",
    "Success is not final, failure is not fatal: it is the courage to continue that counts. – Winston Churchill",
    "Alone we can do so little; together we can do so much. – Helen Keller",
    "Do not wait for opportunity. Create it."
];
$quote = $quotes[array_rand($quotes)];

// ✅ Daily Tips
$tips = [
    "💡 Tip: Always review logs regularly to keep track of system activities.",
    "💡 Tip: Encourage employees to share feedback to improve teamwork.",
    "💡 Tip: Stay consistent — small actions daily lead to big results.",
    "💡 Tip: Delegate tasks effectively and empower your team.",
    "💡 Tip: Clear communication is the foundation of strong leadership."
];
$tip = $tips[array_rand($tips)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .sidebar { height: 100vh; background: #0d6efd; color: #fff; padding: 20px; position: fixed; width: 240px; }
    .sidebar h4 { color: #fff; }
    .sidebar a { color: #fff; text-decoration: none; display: block; margin: 12px 0; font-weight: 500; }
    .sidebar a:hover { text-decoration: underline; }
    .sidebar .datetime { font-size: 0.9rem; margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px; text-align: center; }
    .content { margin-left: 260px; padding: 30px; }
    .card-stat { border-left: 5px solid #0d6efd; }
    .table-hover tbody tr:hover { background-color: #eef4ff; }
    .quote-box {
        background: linear-gradient(45deg, #0d6efd, #6610f2);
        color: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        text-align: center;
        font-size: 1.1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .tip-box {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 15px;
        border-radius: 10px;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Admin Panel</h4>
    <p>Welcome, <?php echo $_SESSION['username']; ?> 👋</p>
    <a href="admin_dashboard.php">📊 Dashboard</a>
    <h6 class="mt-4">👥 Employees</h6>
    <a href="employee_add.php">➕ Add Employee</a>
    <a href="admin_dashboard.php#employee-list">📋 Manage Employees</a>
    <h6 class="mt-4">⚙️ Tools</h6>
    <a href="activity_logs.php">📑 Activity Logs</a>
    <hr>
    <div class="datetime">
      <div id="date"></div>
      <div id="time"></div>
    </div>
    <a href="logout.php" class="mt-3 d-block">🚪 Logout</a>
  </div>

  <!-- Content -->
  <div class="content">
    <h2>Dashboard Overview</h2>

    <!-- Motivational Quote -->
    <div class="quote-box">
      <em>“<?php echo $quote; ?>”</em>
    </div>

    <!-- Daily Tip -->
    <div class="tip-box">
      <?php echo $tip; ?>
    </div>

    <!-- Stats -->
    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card card-stat shadow-sm p-3">
          <h5>Total Employees</h5>
          <h3><?php echo $totalEmployees; ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-stat shadow-sm p-3">
          <h5>Recently Joined (30 days)</h5>
          <h3><?php echo $recentEmployees; ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-stat shadow-sm p-3">
          <h5>Admins</h5>
          <h3><?php echo $adminsCount; ?></h3>
        </div>
      </div>
    </div>

    <!-- Employee Table -->
    <div class="mt-5" id="employee-list">
      <h3>Employee List</h3>
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped table-hover">
          <thead class="table-primary">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Designation</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Username</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $sql = "SELECT e.employee_id, e.first_name, e.middle_name, e.last_name, e.designation, e.email_id, e.mobile_no, u.username, u.user_id
                  FROM employee_details e
                  JOIN users u ON e.user_id = u.user_id
                  ORDER BY e.employee_id DESC";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo "<tr>
                <td>{$row['employee_id']}</td>
                <td>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</td>
                <td>{$row['designation']}</td>
                <td>{$row['email_id']}</td>
                <td>{$row['mobile_no']}</td>
                <td>{$row['username']}</td>
                <td>
                  <a href='employee_edit.php?id={$row['employee_id']}' class='btn btn-sm btn-warning'>Edit</a>
                  <a href='?delete={$row['user_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete this employee?')\">Delete</a>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7' class='text-center'>No employees found</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Chart -->
    <div class="mt-5">
      <h3>Employees by Designation</h3>
      <canvas id="designationChart" height="100"></canvas>
    </div>
  </div>

  <script>
    // Live Date & Time
    function updateDateTime() {
      const now = new Date();
      document.getElementById("date").textContent = now.toLocaleDateString();
      document.getElementById("time").textContent = now.toLocaleTimeString();
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Employees by designation chart
    fetch("designation_data.php")
      .then(response => response.json())
      .then(data => {
        new Chart(document.getElementById("designationChart"), {
          type: "bar",
          data: {
            labels: data.labels,
            datasets: [{
              label: "Employees",
              data: data.counts,
              backgroundColor: "#0d6efd"
            }]
          }
        });
      });
  </script>
</body>
</html>
