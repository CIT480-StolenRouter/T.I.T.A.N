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

$sql = "INSERT INTO admin (emp_id, role) 
        VALUES (:emp_id, :role)
        ON CONFLICT (emp_id) DO UPDATE
        SET role = EXCLUDED.role";       

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':emp_id' => $empid,
        ':role'   => $role,
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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Submission Complete</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
  <h1>Submission Accepted</h1>

  <p><a href="../adminPages/adminList.html">List Administrators</a></p>
  <p><a href="../adminPages/admin.html">Admin Home</a></p>
  <p><a href="../index.html">Back to Home</a></p>
</body>
</html>