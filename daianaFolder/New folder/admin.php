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
    <title>My First Webpage</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  </head>

<body>
    <h1>Admin Page</h1>
    <form action="index.php" method="post">
        <input type="submit" name="logout" value="logout">
    </form>
    <form action="php/listemps.php" method="get">
        <input type="submit" name="listemps" value="listemps"><br>
    </form>
    <div><a href="home.php">Home</a></div>
</body>
</html>

<?php

?>