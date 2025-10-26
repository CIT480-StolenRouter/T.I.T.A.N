<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
session_destroy();
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
    <h1>Error Page</h1>
    <h2>Invalid Credentials</h2>
    <h3>Back to Login</h3>
    <button type="button" onclick="location.href='../index.php'">Return to Login</button>
  </body>
</html>