<?php
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    $isAdmin = false;
    if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
        header('Location: login.php');
        die();
    }
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $isAdmin = $accessLevel >= 2;
        $userID = $_SESSION['_id'];
    } else {
        header('Location: login.php');
        die();
    }
    if ($isAdmin && isset($_GET['id'])) {
        require_once('include/input-validation.php');
        $args = sanitize($_GET);
        $id = strtolower($args['id']);
    } else {
        $id = $userID;
    }
    require_once('database/dbPersons.php');
    //if (isset($_GET['removePic'])) {
     // if ($_GET['removePic'] === 'true') {
       // remove_profile_picture($id);
      //}
    //}

   $user = retrieve_person($id);
   if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_hours'])) {
    require_once('database/dbPersons.php'); // already required, so you can just remove the duplicate
    $con = connect();

    $newHours = floatval($_POST['new_hours']);
    $safeID = mysqli_real_escape_string($con, $id);

    $update = mysqli_query($con, "
        UPDATE dbpersons 
        SET total_hours_volunteered = $newHours 
        WHERE id = '$safeID'
    ");

    if ($update) {
        $user = retrieve_person($id); // refresh with updated hours
        echo '
        <div id="success-message" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
          Hours updated successfully!
        </div>
        <script>
          setTimeout(() => {
            const msg = document.getElementById("success-message");
            if (msg) msg.remove();
          }, 3000);
        </script>
        ';
    } else {
        echo '<div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">Failed to update hours.</div>';
    }
  
}

    $viewingOwnProfile = $id == $userID;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (isset($_POST['url'])) {
        if (!update_profile_pic($id, $_POST['url'])) {
          header('Location: viewProfile.php?id='.$id.'&picsuccess=False');
        } else {
          header('Location: viewProfile.php?id='.$id.'&picsuccess=True');
        }
      }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page - Micah Ministries</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.profile-section');
            sections.forEach(section => section.classList.add('hidden'));
            document.getElementById(sectionId).classList.remove('hidden');

            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.classList.remove('border-b-4', 'border-blue-600');
                tab.classList.add('hover:border-b-2', 'hover:border-blue-400');
            });

            const activeTab = document.querySelector(`[data-tab="${sectionId}"]`);
            activeTab.classList.add('border-b-4', 'border-blue-600');
            activeTab.classList.remove('hover:border-b-2', 'hover:border-blue-400');
        }

        window.onload = () => showSection('personal');
    </script>
    <?php 
        require_once('header.php'); 
        require_once('include/output.php');
    ?>
</head>
            <?php if ($id == 'vmsroot'): ?>
                <div class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
                        The root user does not have a profile.
                    </div>
                </div>
                </body></html>
                <?php die() ?>
            <?php elseif (!$user): ?>
                <div class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
                        User does not exist.
                    </div>
                </div>
                </body></html>
                <?php die() ?>
            <?php endif ?>
            <?php if (isset($_GET['editSuccess'])): ?>
                <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
                    Profile updated successfully!
                </div>
            <?php endif ?>
            <?php if (isset($_GET['rscSuccess'])): ?>
                <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
                    User role/status updated successfully!
                </div>
            <?php endif ?>

<body class="bg-gray-50 font-quicksand">
    <!-- Profile Content -->
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-8">
            <!-- Left Card -->
            <div class="w-full lg:w-1/3 bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <?php if ($viewingOwnProfile): ?>
                            <h2 class="text-2xl font-bold text-gray-900">My Profile</h2>
                        <?php else: ?>
                            <h2 class="text-2xl font-bold text-gray-900">Viewing <?php echo htmlspecialchars($user->get_first_name() . ' ' . $user->get_last_name()) ?></h2>
                        <?php endif ?>
                    </div>
                    <div class="space-y-3 divide-y divide-gray-200">
                        <div class="flex justify-between py-3">
                            <span class="font-medium text-gray-700">Joined</span>
                            <span class="text-gray-900"><?php echo date('M Y', strtotime($user->get_start_date())) ?></span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium text-gray-700">Role</span>
                            <span class="text-gray-900"><?php 
                                $userType = $user->get_type();
                                switch($userType) {
                                    case 'admin':
                                        echo 'Admin';
                                        break;
                                    case 'case_manager':
                                        echo 'Case Manager';
                                        break;
                                    case 'maintenance':
                                        echo 'Maintenance';
                                        break;
                                    case 'volunteer':
                                    case 'participant':
                                        echo 'Volunteer';
                                        break;
                                    default:
                                        echo ucfirst(str_replace('_', ' ', $userType));
                                        break;
                                }
                            ?></span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="font-medium text-gray-700">Status</span>
                            <span class="<?php echo $user->get_archived() ? 'text-red-600' : 'text-green-600' ?> font-semibold">
                                <?php echo $user->get_archived() ? 'Archived' : 'Active' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <button onclick="window.location.href='editProfile.php<?php if ($id != $userID) echo '?id=' . $id ?>';" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                        Edit Profile
                    </button>

                    <?php if ($id != $userID): ?>
                        <?php if (($accessLevel == 2 && $user->get_access_level() == 1) || $accessLevel >= 3): ?>
                            <button onclick="window.location.href='managePassword.php?user_id=<?php echo htmlspecialchars($id) ?>';" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                                Change Password
                            </button>
                            <button onclick="window.location.href='deletePerson.php<?php if ($id != $userID) echo '?id=' . $id ?>';" 
                                    class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200 font-medium">
                                Delete User
                            </button>
                        <?php endif ?>
                        <button onclick="window.location.href='personSearch.php';" 
                                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:border-blue-500 hover:text-blue-600 transition-colors duration-200 font-medium">
                            Return to User Search
                        </button>
                    <?php else: ?>
                        <button onclick="window.location.href='managePassword.php';" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium">
                            Change Password
                        </button>
                        <button onclick="window.location.href='deletePerson.php';" 
                                class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200 font-medium">
                            Delete User
                        </button>
                    <?php endif ?>

                    <button onclick="window.location.href='micahportal.php';" 
                            class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:border-blue-500 hover:text-blue-600 transition-colors duration-200 font-medium">
                        Return to Dashboard
                    </button>
                </div>
    </div>

            <!-- Right Card -->
            <div class="w-full lg:w-2/3 bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button class="tab-button px-4 py-2 text-lg font-medium text-gray-700 border-b-4 border-blue-600" data-tab="personal" onclick="showSection('personal')">Personal Information</button>
                    <button class="tab-button px-4 py-2 text-lg font-medium text-gray-700 hover:border-b-2 hover:border-blue-400" data-tab="contact" onclick="showSection('contact')">Contact Information</button>
                </div>

                <!-- Personal Section -->
                <div id="personal" class="profile-section space-y-6">
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Username</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo htmlspecialchars($user->get_id()) ?></p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Name</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo htmlspecialchars($user->get_first_name() . ' ' . $user->get_last_name()) ?></p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Date of Birth</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo date('m/d/Y', strtotime($user->get_birthday())) ?></p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Address</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo htmlspecialchars($user->get_street_address() . ', ' . $user->get_city() . ', ' . $user->get_state() . ' ' . $user->get_zip_code()) ?></p>
                    </div>
                </div>

                <!-- Contact Section -->
                <div id="contact" class="profile-section space-y-6 hidden">
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Email</span>
                        <p class="text-gray-900 font-semibold text-lg">
                            <a href="mailto:<?php echo $user->get_email() ?>" class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <?php echo htmlspecialchars($user->get_email()) ?>
                            </a>
                        </p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Phone Number</span>
                        <p class="text-gray-900 font-semibold text-lg">
                            <a href="tel:<?php echo $user->get_phone1() ?>" class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <?php echo formatPhoneNumber($user->get_phone1()) ?>
                            </a> 
                            <span class="text-gray-900">(<?php echo ucfirst($user->get_phone1type()) ?>)</span>
                        </p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Emergency Contact Name</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo htmlspecialchars($user->get_emergency_contact_first_name() . ' ' . $user->get_emergency_contact_last_name()) ?></p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Emergency Contact Relation</span>
                        <p class="text-gray-900 font-semibold text-lg"><?php echo htmlspecialchars($user->get_emergency_contact_relation()) ?></p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-blue-600 mb-1">Emergency Contact Phone Number</span>
                        <p class="text-gray-900 font-semibold text-lg">
                            <a href="tel:<?php echo $user->get_emergency_contact_phone() ?>" class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                <?php echo formatPhoneNumber($user->get_emergency_contact_phone()) ?>
                            </a> 
                            <span class="text-gray-900">(<?php echo ucfirst($user->get_emergency_contact_phone_type()) ?>)</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
