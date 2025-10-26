<?php //START OF PHP
declare(strict_types=1);
session_start();
$error = null;
// ---------------------------------------------------
// LOGOUT
if(isset($_POST["logout"])) {
  session_destroy();
  header("Location: /../index.php");
}
// ---------------------------------------------------
    // Checking login validity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['login'])) {

    // Pull inputs (no storing raw password in session) also-- ?? '' checks if it exists and NOT empty
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        http_response_code(400);
        require_once __DIR__ . '/../config/errorcode.php';
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        require_once __DIR__ . '/../config/errorcode.php';
        exit;
    }
// ---------------------------------------------------
    // DB connection
    require_once __DIR__ . '/../config/db.php'; // always define $pdo
// ---------------------------------------------------
    // Ensure PDO throws exceptions (in case db.php didnt)
    if ($pdo instanceof PDO) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
// ---------------------------------------------------
    // SQL STATEMENT
    try {
        // Fetch user by email column
        $stmt = $pdo->prepare(
            'SELECT emp_id, emp_email, emp_passwordhash, role
             FROM empusers
             WHERE emp_email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
// ---------------------------------------------------
        if ($user && password_verify($password, $user['emp_passwordhash'])) {

            // Opportunistic rehash?
            if (password_needs_rehash($user['emp_passwordhash'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare(
                    'UPDATE empusers SET emp_passwordhash = :h WHERE emp_id = :emp_id'
                );
                $upd->execute([':h' => $newHash, ':emp_id' => (int)$user['emp_id']]);
            }
// ---------------------------------------------------
            // Session Variables
            session_regenerate_id(true);
            $_SESSION['emp_id']    = (int)$user['emp_id'];
            $_SESSION['emp_email'] = $user['emp_email'];
            $_SESSION['role']      = $user['role'];

            // Role redirect (ADD THESE LATER, NOT IMPLEMENTED)
            $role = strtolower(trim((string)$user['role']));
            switch ($role) {
                case 'admin':
                    $dest = '../admin.php';
                    break;
                case 'employee':
                    $dest = '../employee.php';
                    break;
                case 'user':
                default:
                    $dest = '../index.php';
                    break;
            }

            header('Location: ' . $dest);
            exit;
        }
// ---------------------------------------------------
        // ERROR CODES
        http_response_code(401);
        exit('Invalid email or password');

    } catch (Throwable $e) {
        error_log('Login error: ' . $e->getMessage());
        http_response_code(500);
        exit('Server error');
    }
}
// ---------------------------------------------------
?> <!-- END OF PHP -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <!-- keep this path if signin.css is in /loginpage relative to THIS file -->
  <link rel="stylesheet" href="../CSS/userlogin.css">
</head>

<body class="auth">
  <section class="auth-card">
    <h1>User Login</h1>
    <p class="auth-muted">Log in to continue</p>

            <form action="userlogin.php" method="post"> 
              <label>Email<br /><input type="text" name="email" required placeholder="Email" style="width:100%;border:none;border-radius:12px;padding:.7rem"/></label><br /><br />
              <label>Password<br /><input type="password" name="password" required placeholder="Password" style="width:100%;border:none;border-radius:12px;padding:.7rem"/></label><br /><br />
              <input type="submit" name="login" value="login" class="btn btn-primary">
            </form>

      <div class="auth-actions">
        <a class="link" href="../index.php">Home Page</a>
        
        <a href="signup.html">Create account</a>
      </div>
    </form>

    <p class="auth-muted" style="margin-top:.75rem">
      <a class="link" href="../admin.php">Admin Page</a>
    </p>
  </section>
</body>
</html>
