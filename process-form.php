<?php

//--------------------------------------------------------
/* DATABASE CONNECTION SECTION 
   Hardcoded for testing, change this later*/

$host = "10.0.2.15"; //postgreSQL VM private IP
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
    exit("Database Connection FAILED: " . $e->getMessage())
}

//--------------------------------------------------------
// Grab data from the html form that was sent by POST

// $_POST "superglobal": array of all fields sent via POST

// ?? '' operator is the "null coalescing operator"
// if this value exists, use it, else, use '' (empty string)
// This prevents undefined index errors if a field is missing

// trim() removes whitespace from start/end
$first = trim($_POST['emp_firstname'] ?? '');
$last = trim($_POST['emp_lastname'] ?? '');
$email = trim($_POST['emp_email'] ?? '');
$phone = trim($_POST['emp_phonenum'] ?? '');
$passw = $_POST['emp_passwordhash'] ?? ''; //no trim because passw can have spaces
$terms = isset($_POST['terms']) true : false;
// isset() checks if the checkbox was submitted

//--------------------------------------------------------
// Server Side Validation

//Double check data gere since client-side JS validation can be bypassed
// First/Last name and Password must not be empty
// Email must bevalid (filter_var and FILTER_VALIDATE_EMAIL is automatic)
// Terms must be checked (true)

if (
    $first === '' || 
    $last === '' || 
    !filter_var($email, FILTER_VALIDATE_EMAIL) || 
    $passw === '' || !$terms
    ) {
    http_response_code(422); //442 means "Unprocessable Entity" (bad input)
    exit("Invalid input. Please check required fields.")
}

//--------------------------------------------------------
// Password Hashing

//password_hash() built in php func
// generates one way hash 
// auto uses strong algo
// auto adds salt
// STORE $hash in the DB rather than actual password
$hash = password_hash($passw, PASSWORD_DEFAULT);

//--------------------------------------------------------
// SQL INSERT using prepared statement

//SQL with named placeholders (:first, :last, :hash, :phone)
//Placeholders prevent SQL injection

$sql = "INSERT INTO empusers
        (emp_firstname, emp_lastname, emp_email, emp_phonenum, emp_passwordhash, accepted_terms)
        VALUES (:first, :last, :email, :phone, :hash, :terms)";

// Prepare the statement
// Tells DB engine, heres the sql with placeholders
$stmt = $pdo->prepare($sql);

//Execute prepared stmt w/ array of values
// keys in array match placeholders above
try {
    $stmt->execute([
        ':first' => $first,
        ':last'  => $last,
        ':email' => $email,
        ':phone' => $phone,
        ':hash'  => $hash,
        ':terms' => $terms,        
    ]);
} catch (PDOException $e) {
    //If error during INSERT, throws another PDOException
    //PostgreSQL uses SQLSTATE error (5 digits) 23505=unique_violation
    //Happens if emp_email is marked UNIQUE and a duplicate is inserted
    if ($e->getCode() === "23505") {
        http_response_code(409); //409 means conflict
        exit("That email is already registered.")
    }
    // ELSE other error, bad conn, wrong col, etc. Generic failure
    http_response_code=(500);
    exit("Insert failed: " . $e->getMessage());
}

//Success with no exceptions thrown ifwe make it here
echo "Thanks! Your info was saved.";