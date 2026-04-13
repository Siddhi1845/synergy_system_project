<?php
session_start();
include 'db.php';

// ✅ Only customers allowed
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// --- Handle product filter for history ---
$selected_product = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Fetch upcoming reminders (next 90 days, pending only)
$sql_upcoming = "SELECT sr.id, sr.next_service_date, sr.status, sr.recurrence_months,
                        sr.last_completed, sr.notes, p.name AS product_name
                 FROM service_reminders sr
                 LEFT JOIN products p ON sr.product_id = p.product_id
                 WHERE sr.customer_id = ?
                   AND sr.status = 'Pending'
                   AND sr.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                 ORDER BY sr.next_service_date ASC";
$stmt = $conn->prepare($sql_upcoming);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$upcoming = $stmt->get_result();
$stmt->close();

// Fetch completed reminders
$sql_completed = "SELECT sr.id, sr.next_service_date, sr.status, sr.recurrence_months,
                         sr.last_completed, sr.notes, p.name AS product_name
                  FROM service_reminders sr
                  LEFT JOIN products p ON sr.product_id = p.product_id
                  WHERE sr.customer_id = ?
                    AND sr.status = 'Completed'
                  ORDER BY sr.last_completed DESC";
$stmt2 = $conn->prepare($sql_completed);
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$completed = $stmt2->get_result();
$stmt2->close();

// Fetch product list for filter
$sql_products = "SELECT DISTINCT p.product_id, p.name 
                 FROM service_reminders sr
                 LEFT JOIN products p ON sr.product_id = p.product_id
                 WHERE sr.customer_id = ? 
                 ORDER BY p.name ASC";
$stmtP = $conn->prepare($sql_products);
$stmtP->bind_param("i", $customer_id);
$stmtP->execute();
$products = $stmtP->get_result();
$stmtP->close();

// Fetch all reminders (with optional filter)
if ($selected_product > 0) {
    $sql_all = "SELECT sr.id, sr.next_service_date, sr.status, sr.recurrence_months,
                       sr.last_completed, sr.notes, p.name AS product_name
                FROM service_reminders sr
                LEFT JOIN products p ON sr.product_id = p.product_id
                WHERE sr.customer_id = ? AND sr.product_id = ?
                ORDER BY sr.next_service_date ASC";
    $stmt3 = $conn->prepare($sql_all);
    $stmt3->bind_param("ii", $customer_id, $selected_product);
} else {
    $sql_all = "SELECT sr.id, sr.next_service_date, sr.status, sr.recurrence_months,
                       sr.last_completed, sr.notes, p.name AS product_name
                FROM service_reminders sr
                LEFT JOIN products p ON sr.product_id = p.product_id
                WHERE sr.customer_id = ?
                ORDER BY sr.next_service_date ASC";
    $stmt3 = $conn->prepare($sql_all);
    $stmt3->bind_param("i", $customer_id);
}
$stmt3->execute();
$all = $stmt3->get_result();
$stmt3->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Service Reminders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .badge { font-size: 0.85rem; }
  </style>
</head>
<body>
<div class="container mt-5">
  <h3>🔔 My Service Reminders</h3>
  <a href="customer_dashboard.php" class="btn btn-secondary mb-3">⬅ Back</a>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="reminderTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">📅 Upcoming</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">✅ Completed</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">📖 History</button>
    </li>
  </ul>

  <div class="tab-content mt-3">
    <!-- Upcoming -->
    <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Product</th>
              <th>Next Service Date</th>
              <th>Last Completed</th>
              <th>Status</th>
              <th>Recurrence (months)</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($upcoming && $upcoming->num_rows > 0): ?>
              <?php while ($row = $upcoming->fetch_assoc()): ?>
                <?php
                  $dueDate = new DateTime($row['next_service_date']);
                  $today   = new DateTime();
                  $diff    = $today->diff($dueDate)->days;
                  $rowClass = "";

                  if ($dueDate < $today) {
                      $rowClass = "table-danger"; // overdue
                  } elseif ($diff <= 7) {
                      $rowClass = "table-warning"; // due soon
                  }
                ?>
                <tr class="<?= $rowClass ?>">
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['product_name'] ?: '—') ?></td>
                  <td><?= $row['next_service_date'] ?></td>
                  <td><?= $row['last_completed'] ?: '—' ?></td>
                  <td><span class="badge bg-warning text-dark">Pending</span></td>
                  <td><?= $row['recurrence_months'] ?></td>
                  <td><?= htmlspecialchars($row['notes'] ?: '—') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  🎉 Great news! You have no upcoming service reminders.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Completed -->
    <div class="tab-pane fade" id="completed" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
          <thead class="table-success">
            <tr>
              <th>ID</th>
              <th>Product</th>
              <th>Next Service Date</th>
              <th>Last Completed</th>
              <th>Status</th>
              <th>Recurrence (months)</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($completed && $completed->num_rows > 0): ?>
              <?php while ($row = $completed->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['product_name'] ?: '—') ?></td>
                  <td><?= $row['next_service_date'] ?: '—' ?></td>
                  <td><?= $row['last_completed'] ?: '—' ?></td>
                  <td><span class="badge bg-success">Completed</span></td>
                  <td><?= $row['recurrence_months'] ?></td>
                  <td><?= htmlspecialchars($row['notes'] ?: '—') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  No services completed yet.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- All (History) -->
    <div class="tab-pane fade" id="all" role="tabpanel">
      <!-- Filter -->
      <form method="get" class="mb-3">
        <div class="row g-2">
          <div class="col-md-4">
            <select name="product_id" class="form-select" onchange="this.form.submit()">
              <option value="0">All Products</option>
              <?php while ($p = $products->fetch_assoc()): ?>
                <option value="<?= $p['product_id'] ?>" <?= ($selected_product == $p['product_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
      </form>

      <!-- History Table -->
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
          <thead class="table-primary">
            <tr>
              <th>ID</th>
              <th>Product</th>
              <th>Next Service Date</th>
              <th>Last Completed</th>
              <th>Status</th>
              <th>Recurrence (months)</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($all && $all->num_rows > 0): ?>
              <?php while ($row = $all->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['product_name'] ?: '—') ?></td>
                  <td><?= $row['next_service_date'] ?: '—' ?></td>
                  <td><?= $row['last_completed'] ?: '—' ?></td>
                  <td>
                    <?php if ($row['status'] === 'Pending'): ?>
                      <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($row['status'] === 'Completed'): ?>
                      <span class="badge bg-success">Completed</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= $row['recurrence_months'] ?></td>
                  <td><?= htmlspecialchars($row['notes'] ?: '—') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  No reminders found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
