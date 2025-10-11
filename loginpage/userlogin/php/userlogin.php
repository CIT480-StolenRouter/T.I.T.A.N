<?php
// ---- DB CONNECTION ----
$host = "100.111.190.113";
$port = "5432";
$dbname = "mydb";
$user = "postgres";
$pass = "projecttitan";
$dsn  = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('DB connect error: ' . $e->getMessage());
    http_response_code(500);
    exit("Database Connection FAILED.");
}

// ---- LOGIN ----
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed. Try POST');
}

// INPUT HANDLING
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    http_response_code(400);
    exit('Invalid Input');
}

try {
    //  Include role (and optionally an "is_active" check)
    $stmt = $pdo->prepare(
        'SELECT emp_id, emp_email, emp_passwordhash, role
           FROM empusers
          WHERE emp_email = :email
          LIMIT 1'
        // Optionally add: AND is_active = true
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['emp_passwordhash'])) {

        // Opportunistic rehash
        if (password_needs_rehash($user['emp_passwordhash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE empusers SET emp_passwordhash = :h WHERE emp_id = :emp_id');
            $upd->execute([':h' => $newHash, ':emp_id' => $user['emp_id']]);
        }

        // Session setup
        session_regenerate_id(true);
        $_SESSION['emp_id'] = (int)$user['emp_id'];
        $_SESSION['emp_email']   = $user['email'];
        $_SESSION['role']    = $user['role']; // store role

        // Role-based redirect
        // Normalize role to be safe against case/whitespace differences
        $role = strtolower(trim((string)$user['role']));
        switch ($role) {
            case ' ':
                $dest = '../../../index.html';
                break;
            case 'employee':
                $dest = '../../../index.html';
                break;
            case 'admin':
                $dest = '../../adminPages/admin.html';
                break;
            case 'user':
            default:
                $dest = '../userlogin.html';
                break;
        }

        header('Location: ' . $dest);
        exit();
    } else {
        http_response_code(401);
        exit('Invalid email or password');
    }
} catch (Throwable $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    exit('Server error');
}

