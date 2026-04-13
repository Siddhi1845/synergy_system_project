<?php
session_start();
require_once 'db.php'; // your DB connection; must set $conn (mysqli)

// --- Session & role check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// ✅ Fetch unread notifications count for this employee
$notif_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_type='employee' AND user_id=? AND is_read=0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $notif_count = $row['c'];
}
$stmt->close();

// small helper to safely get array values
function safe($arr, $key, $default = '—') {
    return isset($arr[$key]) && $arr[$key] !== null && $arr[$key] !== '' ? $arr[$key] : $default;
}

// helper to safely run simple COUNT queries
function safe_count($conn, $table) {
    if (!in_array($table, ['products','customers','orders','complaints','service_reminders','employee_details'])) {
        return 0;
    }
    $sql = "SELECT COUNT(*) AS c FROM `$table`";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) return intval($row['c']);
    return 0;
}

// --- Fetch employee details (prepared) ---
$employee = null;
$stmt = $conn->prepare("SELECT * FROM employee_details WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $employee = $result->fetch_assoc();
    }
    $stmt->close();
}
if (!$employee) {
    // If employee row missing, show friendly error and stop
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Employee Dashboard</title></head><body>";
    echo "<div style='margin:40px;font-family:Segoe UI,sans-serif;'><h2>No profile found</h2>";
    echo "<p>We couldn't find your employee profile. Please contact the administrator.</p>";
    echo "<p><a href='logout.php'>Logout</a></p></div></body></html>";
    exit();
}

// --- Live counts ---
$totalProducts  = safe_count($conn, 'products');
$totalCustomers = safe_count($conn, 'customers');
$totalOrders    = safe_count($conn, 'orders');

// Complaints: prefer complaints assigned to employee if column exists
$complaintsCount = 0;
$colCheck = $conn->query("SHOW COLUMNS FROM `complaints` LIKE 'assigned_to'");
if ($colCheck && $colCheck->num_rows) {
    $q = $conn->prepare("SELECT COUNT(*) AS c FROM complaints WHERE assigned_to = ?");
    if ($q) {
        $q->bind_param("i", $user_id);
        $q->execute();
        $r = $q->get_result();
        if ($r && $row = $r->fetch_assoc()) $complaintsCount = intval($row['c']);
        $q->close();
    }
} else {
    // fallback: show all complaints count
    $complaintsCount = safe_count($conn, 'complaints');
}

// --- Upcoming service reminders (next 30 days) ---
$upcoming = [];
// detect common column names for service_reminders: id, customer_id, product_id, next_service_date, recurrence_months
// We will try a safe JOIN using common column names; if table missing or columns missing, result will be empty
$sr_check = $conn->query("SHOW TABLES LIKE 'service_reminders'");
if ($sr_check && $sr_check->num_rows) {
    $rem_sql = "SELECT sr.id, sr.customer_id, sr.product_id, sr.next_service_date, 
       sr.recurrence_months, sr.notes,
       CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
       COALESCE(p.name, '') AS product_name
FROM service_reminders sr
LEFT JOIN customers c ON sr.customer_id = c.customer_id
LEFT JOIN products p ON sr.product_id = p.product_id
WHERE sr.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY sr.next_service_date ASC
LIMIT 10;
";
    $rem_res = $conn->query($rem_sql);
    if ($rem_res) {
        while ($r = $rem_res->fetch_assoc()) {
            $upcoming[] = $r;
        }
    }
}

// ---------- HTML ----------
?>
<?php include 'header.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Employee Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial; background:#f6f8fa; }
    .sidebar { position: fixed; left:0; top:0; bottom:0; width:240px; background:#198754; padding:28px 18px; color:#fff; }
    .content { margin-left:270px; padding:30px; }
    .card-stat { border-left:6px solid #198754; }
    .sidebar a { color: #fff; display:block; margin:12px 0; text-decoration:none; font-weight:500; }
    .sidebar a:hover { text-decoration:underline; }
  </style>
</head>
<body>

  <div class="sidebar">
    <h4>Employee Panel</h4>
    <p style="margin:10px 0 20px">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

    <a href="employee_dashboard.php">🏠 Dashboard</a>
    <a href="employee_profile_edit.php">✏️ Edit Profile</a>
    <a href="change_password.php">🔑 Change Password</a>

    <hr style="border-color:rgba(255,255,255,0.08)">

    <div style="margin-top:6px;"><strong>Products</strong></div>
    <a href="add_product.php">➕ Add Product</a>
    <a href="view_products.php">📋 Manage Products</a>

    <div style="margin-top:6px;"><strong>Customers</strong></div>
    <a href="view_customers.php">📋 Manage Customers</a>

    <div style="margin-top:6px;"><strong>Orders</strong></div>
    <a href="view_orders.php">📋 Manage Orders</a>

    <div style="margin-top:6px;"><strong>Complaints</strong></div>
    <a href="employee_complaints.php">📋 Manage Complaints</a>
	
	<a href="employee_notifications.php">🔔 Notifications 
	  <?php if ($notif_count > 0): ?>
		<span class="badge bg-danger"><?php echo $notif_count; ?></span>
	  <?php endif; ?>
	</a>


    <div style="margin-top:6px;"><strong>Services</strong></div>
    <a href="add_service_remainder.php">➕ Add Service Remainder</a>
    <a href="service_remainders.php">🔔 Service Remainders</a>

    <hr style="border-color:rgba(255,255,255,0.08)">

    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="content">
    <h1 class="mb-4">Dashboard Overview</h1>

    <div class="row g-3">
      <div class="col-md-3">
        <div class="card card-stat shadow-sm p-3">
          <small class="text-muted">Products</small>
          <h3 class="mt-2"><?php echo intval($totalProducts); ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-stat shadow-sm p-3">
          <small class="text-muted">Customers</small>
          <h3 class="mt-2"><?php echo intval($totalCustomers); ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-stat shadow-sm p-3">
          <small class="text-muted">Orders</small>
          <h3 class="mt-2"><?php echo intval($totalOrders); ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-stat shadow-sm p-3">
          <small class="text-muted">My Complaints</small>
          <h3 class="mt-2"><?php echo intval($complaintsCount); ?></h3>
        </div>
      </div>
    </div>

    <!-- Profile & Details -->
    <div class="row mt-4">
      <div class="col-lg-6">
        <h3>My Profile</h3>
        <div class="card shadow-sm p-3">
          <h4><?php echo htmlspecialchars(safe($employee,'first_name').' '.safe($employee,'middle_name').' '.safe($employee,'last_name')); ?></h4>
          <p><strong>Designation:</strong> <?php echo htmlspecialchars(safe($employee,'designation')); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars(safe($employee,'email_id')); ?></p>
          <p><strong>Mobile:</strong> <?php echo htmlspecialchars(safe($employee,'mobile_no')); ?></p>
          <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
          <p><strong>Date of Joining:</strong> <?php echo htmlspecialchars(safe($employee,'date_of_joining')); ?></p>
          <p><strong>Residential Address:</strong> <?php echo htmlspecialchars(safe($employee,'residential_address')); ?></p>
          <p><strong>Permanent Address:</strong> <?php echo htmlspecialchars(safe($employee,'permanent_address')); ?></p>
        </div>
      </div>

      <div class="col-lg-6">
        <h3>Orders & Products Overview</h3>
        <div class="card shadow-sm p-3">
          <canvas id="overviewChart" height="160"></canvas>
        </div>
      </div>
    </div>

    <!-- Upcoming Reminders -->
    <div class="mt-4">
      <h3>Upcoming Service Reminders (next 30 days)</h3>
      <div class="card shadow-sm p-3">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-success">
              <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Next Date</th>
                <th>Recurrence (months)</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($upcoming) === 0): ?>
                <tr><td colspan="6" class="text-center">No upcoming reminders</td></tr>
              <?php else: ?>
                <?php foreach ($upcoming as $r): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($r['id']); ?></td>
                    <td><?php echo htmlspecialchars(safe($r,'customer_name','—')); ?></td>
                    <td><?php echo htmlspecialchars(safe($r,'product_name','—')); ?></td>
                    <td><?php echo htmlspecialchars(safe($r,'next_service_date','—')); ?></td>
                    <td><?php echo htmlspecialchars(safe($r,'recurrence_months','—')); ?></td>
                    <td><?php echo htmlspecialchars(safe($r,'notes','')); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
	
	
	    <!-- Notifications -->
    <div class="mt-4">
      <h3>🔔 My Notifications</h3>
      <div class="card shadow-sm p-3">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-warning">
              <tr>
                <th>Message</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $notifs = $conn->prepare("SELECT message, created_at 
                                        FROM notifications 
                                        WHERE user_type = 'employee' AND user_id = ? 
                                        ORDER BY created_at DESC LIMIT 10");
              $notifs->bind_param("i", $user_id);
              $notifs->execute();
              $res = $notifs->get_result();
              if ($res && $res->num_rows > 0):
                  while ($n = $res->fetch_assoc()):
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($n['message']); ?></td>
                  <td><?php echo htmlspecialchars($n['created_at']); ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="2" class="text-center">No notifications</td></tr>
              <?php endif;
              $notifs->close();
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>


  </div>

  <script>
    // Chart data from PHP
    const ctx = document.getElementById('overviewChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Products','Orders','Customers','Complaints'],
        datasets: [{
          label: 'Counts',
          data: [
            <?php echo intval($totalProducts); ?>,
            <?php echo intval($totalOrders); ?>,
            <?php echo intval($totalCustomers); ?>,
            <?php echo intval($complaintsCount); ?>
          ],
          backgroundColor: ['#198754','#0d6efd','#ffc107','#dc3545']
        }]
      },
      options: {
        scales: {
          y: { beginAtZero: true, ticks: { precision:0 }}
        }
      }
    });
  </script>

</body>
</html>
