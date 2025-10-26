<?php
session_start();
  echo $_SESSION["emp_email"] . "<br>";
  echo $_SESSION["role"] . "<br>";
require_once __DIR__ . '/config/admincheck.php';
if(isset($_POST["logout"])) {
  session_destroy();
  header("Location: index.php");
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  </head>

  <body>
    <div class="layout">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="brand">Admin Panel</div><br>
        <nav class="nav">
          <a href="elevateUsertoEmp.html">Change User Role</a><br>
          <a href="elevatePriv.html">Elevate Employee Privileges</a><br>
          <a href="adminList.html">List Admins</a><br>
          <a href="empList.html">List Employees</a><br>
          <a href="get.html">Search One Employee</a><br>
          <a href="index.php">Back to Home</a><br>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="main">
        <header class="topbar">
          <h1>Welcome, Admin</h1>
        </header>

        <section class="content">
          <p>Select an option from the sidebar to manage users, privileges, or view the admin list.</p>
        </section>
      </main>
    </div>
  </body>
</html>

<?php

?>