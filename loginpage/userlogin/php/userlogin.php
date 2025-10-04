<?php
// ---- DB CONNECTION (same as yours; consider env vars in prod) ----
$host = "100.111.190.113";
$port = "5432";
$dbname = "mydb";
$user = "postgres";
$pass = "projecttitan";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
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
        'SELECT id, email, password_hash, role
           FROM app_user
          WHERE email = :email
          LIMIT 1'
        // Optionally add: AND is_active = true
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        // Opportunistic rehash
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE app_user SET password_hash = :h WHERE id = :id');
            $upd->execute([':h' => $newHash, ':id' => $user['id']]);
        }

        // Session setup
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role']; // store role

        // Role-based redirect
        // Normalize role to be safe against case/whitespace differences
        $role = strtolower(trim((string)$user['role']));
        switch ($role) {
            case 'Employee':
                $dest = '../../../index.html';
                break;
            case 'Admin':
                $dest = '../../adminPages/admin.php';
                break;
            case 'User':
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
    http_response_code(500);
    exit('Server error');
}
