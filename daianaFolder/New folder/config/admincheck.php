<?php
declare(strict_types=1);
session_start();
// If no active session or no email stored, kick out
if (empty($_SESSION['emp_email'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/db.php';

if ($pdo instanceof PDO) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
//-------------------------------------------------------------------------
try {
    // Fetch user info by email from the session
    $stmt = $pdo->prepare(
        'SELECT role FROM empusers WHERE emp_email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $_SESSION['emp_email']]);
    $user = $stmt->fetch();

    // Check if user exists and is an admin
    if (!$user || strtolower(trim($user['role'])) !== 'admin') {
        // Not admin — redirect to home or login
        header('Location: index.php');
        exit;
    }

    // Otherwise, the user is an admin — continue page load-----------------------------------------------
} catch (Throwable $e) {
    error_log('Admin check failed: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>