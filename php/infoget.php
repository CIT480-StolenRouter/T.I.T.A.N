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
$id = trim($_GET['emp_id'] ?? '');
$first = trim($_GET['emp_firstname'] ?? '');
$last = trim($_GET['emp_lastname'] ?? '');
$email = trim($_GET['emp_email'] ?? '');
$phone = trim($_GET['emp_phonenum'] ?? '');