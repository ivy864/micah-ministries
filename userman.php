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
  <link href="css/management_tw.css" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
        .date-box {
            background: #274471;
            padding: 7px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }
	.dropdown {
	    padding-right: 50px;
	}

</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>

<body>


  <!-- Larger Hero Section -->
  <header class="hero-header"></header>


  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Buttons Section -->
      <div class="button-section">

        <button onclick="window.location.href='createNewUser.php';">
	  <div class="button-left-gray"></div>
	  <div>Add User</div>
	  <img class="button-icon h-12 w-12 left-4" src="images/plus-solid.svg" alt="Plus Icon">
        </button>

        <button onclick="window.location.href='editlogin.php';">
	  <div class="button-left-gray"></div>
	  <div>Modify User Role</div>
	  <img class="button-icon h-10 w-10 left-5" src="images/user-check.svg" alt="List Icon">
        </button>

        <button onclick="window.location.href='personSearch.php';">
	  <div class="button-left-gray"></div>
	  <div>Search for User</div>
	  <img class="button-icon h-10 w-10 left-5" src="images/search.svg" alt="Search Icon">
        </button>


	<div class="text-center mt-6">
        	<a href="micahportal.php" class="return-button">Return to Dashboard</a>
	</div>


     </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1>User Management</h1>
        <div class="div-blue"></div>
        <p>
          Welcome to the user management hub. Use the controls on the left to manage user accounts, search for users, etc. Everything you need to maintain and manage your facilities is just a click away.
        </p>
      </div>

    </div>
  </main>
</body>
</html>
