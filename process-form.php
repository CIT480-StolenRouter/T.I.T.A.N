<?php

$emp_firstname       = $_POST['emp_firstname'] ?? null;
$emp_lastname        = $_POST['emp_lastname'] ?? null;
$emp_email           = $_POST['emp_email'] ?? null;
$emp_phonenum        = $_POST['emp_phonenum'] ?? null;
$emp_passwordhash    = $_POST['emp_passwordhash'] ?? null;
$terms       = filter_input(INPUT_POST, "terms", FILTER_VALIDATE_BOOL);


// Check required fields
if (empty($emp_firstname) || empty($emp_lastname) || empty($emp_email) ||
    empty($emp_phonenum) || empty($emp_passwordhash) || $terms !== true) {
    die("All fields are required and terms must be accepted.");
}

// Phone validation: allow +, -, space, () and digits, must have 7+ digits
function validatePhone($phone) {
    // Remove non-digits for counting
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) < 7) {
        return false;
    }
    // Check allowed characters
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

// Validate email
if (!filter_var($emp_email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

// Validate phone
if (!validatePhone($emp_phonenum)) {
    die("Invalid phone number.");
}

var_dump($emp_firstname, $emp_lastname, $emp_email, $emp_phonenum, $emp_passwordhash, $terms);

/* DATABASE CONNECTION SECTION */

$env = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_TYPED);

$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s',
    $env['DB_HOST'],
    $env['DB_PORT'],
    $env['DB_NAME']
);

try {
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed.');
}
