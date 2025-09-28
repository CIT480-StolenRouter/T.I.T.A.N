<?php
//--------------------------------------------------------
/* DATABASE CONNECTION SECTION 
   Hardcoded for testing, change this later*/

$host = "100.111.190.113"; //postgreSQL VM private IP
$port = "5432"; //default postgre port
$dbname = "mydb"; 
$user = "postgres"; 
$pass = "projecttitan"; //user password

//Build Data Source Name (DSN) string for PHP Data Objects (PDO)
//dsn: connection information
//pdo: abstraction layer, consistent interface for database access in php apps
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

//--------------------------------------------------------
//Create a new PDO object for postgreSQL
// $dsn tells where the DB is
// $user and $pass authenticate
// PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION --- throw exceptions if error
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    //If connection fails, return 500 and stop
    http_response_code(500);
    exit("Database Connection FAILED: " . $e->getMessage());
}

// --- Run query ---
$sql = "SELECT a.admin_id, u.emp_id, u.emp_firstname, u.emp_lastname, a.role
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
            <td><?= htmlspecialchars($row['role']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <p><a href="../adminPages/admin.html">Admin Home</a></p>
  <p><a href="../index.html">Back to Home</a></p>
</body>
</html>