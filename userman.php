<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    // admin-only access
    if ($accessLevel < 2) {
        header('Location: index.php');
        die();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Management Page</title>
  <link href="css/base.css" rel="stylesheet">
  <link href="css/dashboard.css" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
</head>

<body>
  <!-- Header -->
  <div class="hero-header">
    <div class="center-header">
        <h1>User Management Hub</h1>
    </div>
  </div>

  <div class="overall-container">

    <!-- Button Cards -->
    <div class="actions-container">

      <div class="hub-card">
        <h2 class="hub-title">Search Users</h2>
        <p class="hub-desc">Search for an existing user.</p>
        <div class="hub-action">
            <a href="personSearch.php" class="portal-link">Go to Search Users Page</a>
        </div>
      </div>

      <div class="hub-card">
        <h2 class="hub-title">Add User</h2>
        <p class="hub-desc">Add user that can be searchable on the search users page.</p>
        <div class="hub-action">
            <a href="createNewUser.php" class="portal-link">Go to Add User Page</a>
        </div>
      </div>

    </div>

    <!-- Text Section -->
    <div class="text-section">
      <h1 class="main-text">User Management</h1>
      <p class="secondary-text">
        Welcome to the user management hub. Use the controls on the left to manage user accounts, search for users, etc. Everything you need to maintain and manage your facilities is just a click away.
      </p>
      <div style="text-align: center; padding-top: 20px;">
        <a href="index.php" class="gray-button">Return to Dashboard</a>
      </div>
    </div>
  </div>

</body>
</html>
