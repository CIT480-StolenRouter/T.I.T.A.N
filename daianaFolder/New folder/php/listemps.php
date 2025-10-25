<?php
session_start();
echo $_SESSION["emp_email"] . "<br>";
echo $_SESSION["role"] . "<br>";
require_once __DIR__ . '/../config/db.php';
if(isset($_GET["listemps"])) {


// --- Run query ---
$sql = "SELECT emp_id, emp_firstname, emp_lastname, emp_email, emp_phonenum, role
        FROM empusers
        ";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
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
          <th>ID</th>
          <th>First</th>
          <th>Last</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['emp_id']) ?></td>
            <td><?= htmlspecialchars($row['emp_firstname']) ?></td>
            <td><?= htmlspecialchars($row['emp_lastname']) ?></td>
            <td><?= htmlspecialchars($row['emp_email']) ?></td>
            <td><?= htmlspecialchars($row['emp_phonenum']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <div><a href="../index.php">Home</a></div>
  <div><a href="../admin.php">Admin</a></div>
</body>
</html>
