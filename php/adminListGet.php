<?php
//--------------------------------------------------------
session_start();
echo $_SESSION["emp_email"] . "<br>";
echo $_SESSION["role"] . "<br>";
require_once __DIR__ . '/../config/db.php';

// --- Run query ---
$sql = "SELECT a.admin_id, u.emp_id, u.emp_firstname, u.emp_lastname, a.admin_role
        FROM admin a
        JOIN empusers u ON a.emp_id = u.emp_id;
        ";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();

// --- Build HTML result ---
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>All Employees</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
  <h1>All Employees</h1>

  <?php if (!$rows): ?>
    <p>No employees found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Admin ID</th>
          <th>Employee ID</th>
          <th>First</th>
          <th>Last</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['admin_id']) ?></td>
            <td><?= htmlspecialchars($row['emp_id']) ?></td>
            <td><?= htmlspecialchars($row['emp_firstname']) ?></td>
            <td><?= htmlspecialchars($row['emp_lastname']) ?></td>
            <td><?= htmlspecialchars($row['admin_role']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <p><a href="../admin.php">Admin Home</a></p>
  <p><a href="../index.php">Back to Home</a></p>
</body>
</html>