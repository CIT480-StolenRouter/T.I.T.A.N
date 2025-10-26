<?php

//--------------------------------------------------------
session_start();
echo $_SESSION["emp_email"] . "<br>";
echo $_SESSION["role"] . "<br>";
require_once __DIR__ . '/../config/db.php';

//--------------------------------------------------------
// Grab data from the html form that was sent by POST

// $_POST "superglobal": array of all fields sent via POST

// ?? '' operator is the "null coalescing operator"
// if this value exists, use it, else, use '' (empty string)
// This prevents undefined index errors if a field is missing

// trim() removes whitespace from start/end
$empid = trim($_POST['emp_id'] ?? '');
$role = trim($_POST['role'] ?? '');
// isset() checks if the checkbox was submitted

//--------------------------------------------------------
// Server Side Validation

if (
    $empid === '' ||
    $role === ''
    ) {
    http_response_code(422); //442 means "Unprocessable Entity" (bad input)
    exit("Invalid input. Please check required fields.");
}

//--------------------------------------------------------
// SQL INSERT using prepared statement

//SQL with named placeholders (:first, :last, :hash, :phone)
//Placeholders prevent SQL injection

$sql = "INSERT INTO admin (emp_id, admin_role) 
        VALUES (:emp_id, :admin_role)
        ON CONFLICT (emp_id) DO UPDATE
        SET admin_role = EXCLUDED.admin_role";       

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':emp_id' => $empid,
        ':admin_role'   => $role,
    ]);
} catch (PDOException $e) {
    // 23505 = unique_violation (e.g., if you have UNIQUE(emp_id) on admin)
    if ($e->getCode() === '23505') {
        http_response_code(409);
        exit("<h1>Conflict</h1><p>An admin record already exists for employee ID "
            . htmlspecialchars($empid) . ".</p>");
    }
    // 23503 = foreign_key_violation (emp_id not present in empusers)
    if ($e->getCode() === '23503') {
        http_response_code(400);
        exit("<h1>Invalid emp_id</h1><p>No matching employee for ID "
            . htmlspecialchars($empid) . ".</p>");
    }
    http_response_code(500);
    exit("<h1>Insert failed</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// --- Build HTML result ---
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My First Webpage</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  </head>

  <body>
    <div>
        <h1>Admin Page</h1>
</div>

    <h2>Update Complete</h2>
    <p><a href="../admin.php">Admin Home</a></p>
    <p><a href="../index.php">Back to Home</a></p>
  </body>

  </html>