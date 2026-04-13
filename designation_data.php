<?php
include 'db.php';

$sql = "SELECT designation, COUNT(*) as count FROM employee_details GROUP BY designation";
$result = $conn->query($sql);

$labels = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['designation'];
    $counts[] = $row['count'];
}

echo json_encode(["labels" => $labels, "counts" => $counts]);
