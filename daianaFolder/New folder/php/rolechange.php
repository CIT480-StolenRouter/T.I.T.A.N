<?php
// --- Database connection ---
$dsn  = "pgsql:host=100.111.190.113;port=5432;dbname=mydb";
$user = "postgres";
$pass = "projecttitan";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit("<h1>Database Connection FAILED</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// --- Read emp_id from POST ---
$empid = trim($_POST['emp_id'] ?? '');
if ($empid === '') {
    http_response_code(422);
    exit("<h1>Error</h1><p>Please provide emp_id.</p>");
}

// --- If role form submitted, update role ---
if (isset($_POST['choice'])) {
    // Map radio to stored values
    $choiceMap = [
        'optUser' => 'User',
        'optEmp'  => 'Employee',
        'optAdmin' => 'Admin',
    ];
    $newRole = $choiceMap[$_POST['choice']] ?? null;

    if ($newRole !== null) {
        // Use quotes around "role" since it's a keyword in Postgres
        $updateSql = 'UPDATE empusers SET "role" = :role WHERE emp_id = :emp_id';
        $upd = $pdo->prepare($updateSql);
        $upd->execute([
            ':role'   => $newRole,
            ':emp_id' => $empid,
        ]);
    }
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
  <h1>Search Result</h1>

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
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($row['emp_id']) ?></td>
          <td><?= htmlspecialchars($row['emp_firstname']) ?></td>
          <td><?= htmlspecialchars($row['emp_lastname']) ?></td>
          <td><?= htmlspecialchars($row['emp_email']) ?></td>
          <td><?= htmlspecialchars($row['emp_phonenum']) ?></td>
        </tr>
      </tbody>
    </table>
        
    <table>
      <thead>
        <tr>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($row['role']) ?></td>
        </tr>
        </tbody>
    </table>
    <form method="post" action="">
      <p>Choose role:</p>

      <input type="hidden" name="emp_id" value="<?= htmlspecialchars($row['emp_id']) ?>">

      <input type="radio" id="optUser" name="choice" value="optUser" <?= ($row['role'] === 'User') ? 'checked' : '' ?>>
      <label for="optUser">User</label><br>

      <input type="radio" id="optEmp" name="choice" value="optEmp" <?= ($row['role'] === 'Employee') ? 'checked' : '' ?>>
      <label for="optEmp">Employee</label><br>

      <input type="radio" id="optAdmin" name="choice" value="optAdmin" <?= ($row['role'] === 'Admin') ? 'checked' : '' ?>>
      <label for="optAdmin">Admin</label><br>
      <button type="submit">Confirm</button>
    </form>   
  <?php endif; ?>
  
  <p><a href="../admin.php">Back to Admin Home</a></p>
  <p><a href="../elevateUsertoEmp.html">Back</a></p>
  <p><a href="../index.php">Back to Home</a></p>
</body>
</html>
