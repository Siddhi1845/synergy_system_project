<?php
session_start();
include 'db.php';
require 'sendmail.php'; // ✅ include your email function

// Allow only employees
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle "mark complete" action (reschedule next occurrence + email)
if (isset($_GET['complete_id'])) {
    $id = intval($_GET['complete_id']);

    // Fetch current reminder + customer details
    $r = $conn->query("SELECT sr.*, c.email, c.first_name, c.last_name, p.name AS product_name
                       FROM service_reminders sr
                       LEFT JOIN customers c ON sr.customer_id = c.customer_id
                       LEFT JOIN products p ON sr.product_id = p.product_id
                       WHERE sr.id = $id")->fetch_assoc();

    if ($r) {
        $rec   = intval($r['recurrence_months']);
        $next  = ($rec > 0) ? date('Y-m-d', strtotime($r['next_service_date'] . " + $rec months")) : null;
        $toEmail = $r['email'];
        $toName  = $r['first_name'] . " " . $r['last_name'];
        $product = $r['product_name'];

        if ($next) {
            // Mark this reminder as completed
            $stmt = $conn->prepare("UPDATE service_reminders 
                                    SET last_completed = NOW(), status = 'Completed' 
                                    WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Create a new pending reminder for the next occurrence
            $stmt2 = $conn->prepare("INSERT INTO service_reminders 
                                     (customer_id, product_id, next_service_date, recurrence_months, status, notes, last_completed)
                                     SELECT customer_id, product_id, ?, recurrence_months, 'Pending', notes, NULL
                                     FROM service_reminders WHERE id = ?");
            $stmt2->bind_param("si", $next, $id);
            $stmt2->execute();
            $stmt2->close();

            $message = "✅ Marked complete. Next reminder created for: " . $next;

            // Send email to customer
            $subject = "Service Completed: $product";
            $body = "
                <p>Dear $toName,</p>
                <p>Your service for <b>$product</b> has been marked as <b>completed</b> on <b>" . date('Y-m-d') . "</b>.</p>
                <p>Your next service is scheduled for: <b>$next</b>.</p>
                <p>Thank you for using our service!</p>
                <p><b>Synergy System</b></p>
            ";
            sendNotification($toEmail, $toName, $subject, $body);

        } else {
            // No recurrence → just mark completed
            $stmt = $conn->prepare("UPDATE service_reminders 
                                    SET last_completed = NOW(), status = 'Completed' 
                                    WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $message = "✅ Marked complete. No recurrence set.";

            // Send email to customer
            $subject = "Service Completed: $product";
            $body = "
                <p>Dear $toName,</p>
                <p>Your service for <b>$product</b> has been marked as <b>completed</b> on <b>" . date('Y-m-d') . "</b>.</p>
                <p>Since no recurrence is set, no future service date has been scheduled.</p>
                <p>Thank you for using our service!</p>
                <p><b>Synergy System</b></p>
            ";
            sendNotification($toEmail, $toName, $subject, $body);
        }
    }
}

// Fetch upcoming reminders (next 90 days)
$upcoming_q = "SELECT sr.*, 
                      CONCAT(c.first_name, ' ', c.last_name) AS customer_name, 
                      p.name AS product_name
               FROM service_reminders sr
               LEFT JOIN customers c ON sr.customer_id = c.customer_id
               LEFT JOIN products p ON sr.product_id = p.product_id
               WHERE sr.next_service_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
               ORDER BY sr.next_service_date ASC";
$upcoming = $conn->query($upcoming_q);

// Fetch all reminders
$all_q = "SELECT sr.*, 
                 CONCAT(c.first_name, ' ', c.last_name) AS customer_name, 
                 p.name AS product_name
          FROM service_reminders sr
          LEFT JOIN customers c ON sr.customer_id = c.customer_id
          LEFT JOIN products p ON sr.product_id = p.product_id
          ORDER BY sr.next_service_date ASC";
$all = $conn->query($all_q);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Service Reminders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>🔔 Service Reminders</h3>
  <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <div class="mb-3">
    <a href="add_service_reminder.php" class="btn btn-success">+ Add Reminder</a>
    <a href="employee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>

  <!-- Upcoming -->
  <h5>Upcoming (next 90 days)</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered table-hover bg-white">
      <thead class="table-success">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Next Date</th>
          <th>Recurrence (months)</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($upcoming && $upcoming->num_rows): ?>
            <?php while ($r = $upcoming->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['customer_name'] ?: '—') ?></td>
              <td><?= htmlspecialchars($r['product_name'] ?: $r['product_name']) ?></td>
              <td><?= $r['next_service_date'] ?></td>
              <td><?= $r['recurrence_months'] ?></td>
              <td>
                <?php if ($r['status'] === 'Pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($r['status'] === 'Completed'): ?>
                  <span class="badge bg-success">Completed</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($r['status'] !== 'Completed'): ?>
                  <a href="service_remainders.php?complete_id=<?= $r['id'] ?>" 
                     class="btn btn-sm btn-success"
                     onclick="return confirm('Mark service complete? This will schedule the next occurrence if recurrence > 0')">
                     Mark Complete
                  </a>
                <?php else: ?>
                  <span class="text-muted">Completed</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No upcoming reminders</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- All -->
  <h5>All Reminders</h5>
  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Next Date</th>
          <th>Last Completed</th>
          <th>Recurrence</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($all && $all->num_rows): ?>
            <?php while ($r = $all->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['customer_name'] ?: '—') ?></td>
              <td><?= htmlspecialchars($r['product_name'] ?: $r['product_name']) ?></td>
              <td><?= $r['next_service_date'] ?></td>
              <td><?= $r['last_completed'] ?: '—' ?></td>
              <td><?= $r['recurrence_months'] ?></td>
              <td>
                <?php if ($r['status'] === 'Pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($r['status'] === 'Completed'): ?>
                  <span class="badge bg-success">Completed</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No reminders found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
