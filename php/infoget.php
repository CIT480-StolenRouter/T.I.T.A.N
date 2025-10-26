<?php
// --- Database connection ---
session_start();
echo $_SESSION["emp_email"] . "<br>";
echo $_SESSION["role"] . "<br>";
require_once __DIR__ . '/../config/db.php';

// --- Read emp_id from POST ---
$empid = trim($_POST['emp_id'] ?? '');
if ($empid === '') {
    http_response_code(422);
    exit("<h1>Error</h1><p>Please provide emp_id.</p>");
}

// --- Run query ---
$sql = "SELECT emp_id, emp_firstname, emp_lastname, emp_email, emp_phonenum, role
        FROM empusers
        WHERE emp_id = :emp_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':emp_id' => $empid]);
$row = $stmt->fetch();

// --- Build HTML result ---
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Employee Result</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
  <h1>Employee Search Result</h1>

  <?php if (!$row): ?>
    <p>No employee found for ID <strong><?= htmlspecialchars($empid) ?></strong>.</p>
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
        <tr>
          <td><?= htmlspecialchars($row['emp_id']) ?></td>
          <td><?= htmlspecialchars($row['emp_firstname']) ?></td>
          <td><?= htmlspecialchars($row['emp_lastname']) ?></td>
          <td><?= htmlspecialchars($row['emp_email']) ?></td>
          <td><?= htmlspecialchars($row['emp_phonenum']) ?></td>
          <td><?= htmlspecialchars($row['role']) ?></td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>

  <p><a href="../get.html">Go to Search</a></p>
  <p><a href="../admin.php">Admin</a></p>
  <p><a href="../index.php">Back to Home</a></p>
</body>
</html>
