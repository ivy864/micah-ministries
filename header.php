<?php
date_default_timezone_set('America/New_York');
/*
 * Copyright 2013 by Allen Tucker. 
 * This program is part of RMHP-Homebase, which is free software.  It comes with 
 * absolutely no warranty. You can redistribute and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software Foundation
 * (see <http://www.gnu.org/licenses/ for more information).
 * 
if (date("H:i:s") > "18:19:59") {
	require_once 'database/dbShifts.php';
	auto_checkout_missing_shifts();
}
 */

// check if we are in locked mode, if so,
// user cannot access anything else without 
// logging back in
?>
<head>     
  
  <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/base.css" rel="stylesheet">
  <link href="css/header.css" rel="stylesheet">

  <script>
      document.addEventListener("DOMContentLoaded", function() {
          document.querySelectorAll(".nav-item").forEach(item => {
              item.addEventListener("click", function(event) {
                  event.stopPropagation();
                  document.querySelectorAll(".nav-item").forEach(nav => {
                      if (nav !== item) {
                          nav.classList.remove("active");
                          nav.querySelector(".dropdown").style.display = "none";
                      }
                  });
                  this.classList.toggle("active");
                  let dropdown = this.querySelector(".dropdown");
                  if (dropdown) {
                      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
                  }
              });
          });
          document.addEventListener("click", function() {
              document.querySelectorAll(".nav-item").forEach(nav => {
                  nav.classList.remove("active");
                  nav.querySelector(".dropdown").style.display = "none";
              });
          });
      });
  </script>
</head>

<header>

    <?php
    //Log-in security
    //If they aren't logged in, display our log-in form.
    $showing_login = false;
    if (!isset($_SESSION['logged_in'])) {
		echo('<div class="navbar">
        <!-- Left Section: Logo & Nav Links -->
        <div class="left-section">
            <div class="logo-container">
                <a href="index.php"><img src="images/micah-ministries-logo.jpg"
     alt="Micah Ecumenical Ministries" id="logo"
     style="height:52px;width:auto;object-fit:contain;display:block"></a>

            </div>
            <div class="nav-links">
		<div class="nav-item"><span class="font-change">Volunteer Management System</span>
		</div>
           </div>
        </div>

        <!-- Right Section: Date & Icon -->
        <div class="right-section">
            <div class="date-box">'); echo date('l, F j, Y'); echo('</div>           
        </div>
    </div>');

    } else if ($_SESSION['logged_in']) {

        /*         * Set our permission array.
         * anything a guest can do, a volunteer and manager can also do
         * anything a volunteer can do, a manager can do.
         *
         * If a page is not specified in the permission array, anyone logged into the system
         * can view it. If someone logged into the system attempts to access a page above their
         * permission level, they will be sent back to the home page.
         */
        //pages guests are allowed to view
        // LOWERCASE
        $permission_array['index.php'] = 0;
        $permission_array['about.php'] = 0;
        $permission_array['apply.php'] = 0;
        $permission_array['logout.php'] = 0;
        $permission_array['volunteerregister.php'] = 0;
	$permission_array['leaderboard.php'] = 0;
        // $permission_array['findanimal.php'] = 0; //TODO DELETE
        //pages maintenance staff can view (Level 1)
        //pages volunteers can view
        $permission_array['leaseview.php'] = 1;
        $permission_array['leaseman.php'] = 1;
        $permission_array['maintman.php'] = 1;
        $permission_array['help.php'] = 1;
        $permission_array['dashboard.php'] = 1;
        $permission_array['changepassword.php'] = 1;
        $permission_array['managepassword.php'] = 1;
        $permission_array['editprofile.php'] = 1;
        $permission_array['inbox.php'] = 1;
        $permission_array['viewprofile.php'] = 1;
        $permission_array['viewnotification.php'] = 1;
        $permission_array['viewresources.php'] = 1;
        $permission_array['discussionmain.php'] = 1;
        $permission_array['viewdiscussions.php'] = 1;
        $permission_array['discussioncontent.php'] = 1;
        $permission_array['milestonepoints.php'] = 1;
        $permission_array['selectvotm.php'] = 1;
        $permission_array['volunteerviewgroupmembers.php'] = 1;
        $permission_array['managemaintenancerequest.php'] = 1;
        $permission_array['index.php'] = 1;
        
        //pages case managers can view (Level 2) - Lease + Maintenance
        $permission_array['restoremaintenancerequest.php'] = 2;
        $permission_array['addlease.php'] = 2;
        $permission_array['editlease.php'] = 2;
        $permission_array['deletelease.php'] = 2;
        $permission_array['archivemaintenancerequest.php'] = 2;
        $permission_array['restoreMaintenancerequest.php'] = 2;
        $permission_array['leaseview.php'] = 2;
        $permission_array['calendar.php'] = 2;
        $permission_array['eventsearch.php'] = 2;
        $permission_array['date.php'] = 2;
        $permission_array['event.php'] = 2;
        $permission_array['volunteerreport.php'] = 2;
        $permission_array['viewmyupcomingevents.php'] = 2;
        $permission_array['volunteerviewgroup.php'] = 2;
	    $permission_array['viewcheckinout.php'] = 2;
        $permission_array['milestonepoints.php'] = 2;
        $permission_array['selectvotm.php'] = 2;
        $permission_array['volunteerviewgroupmembers.php'] = 2;
        //pages only managers can view
        $permission_array['viewallevents.php'] = 0;
        //user management - admin only (Level 3)
        $permission_array['userman.php'] = 3;
        $permission_array['personsearch.php'] = 3;
        $permission_array['personedit.php'] = 0; // changed to 0 so that applicants can apply
        $permission_array['viewschedule.php'] = 3;
        $permission_array['addweek.php'] = 3;
        $permission_array['log.php'] = 3;
        $permission_array['reports.php'] = 3;
        $permission_array['eventedit.php'] = 3;
        $permission_array['modifyuserrole.php'] = 3;
        $permission_array['createnewuser.php'] = 3;
        $permission_array['addevent.php'] = 2;
        $permission_array['editevent.php'] = 2;
        // $permission_array['roster.php'] = 2; //TODO DELETE
        $permission_array['report.php'] = 2;
        $permission_array['reportspage.php'] = 2;
        $permission_array['resetpassword.php'] = 2;
        // $permission_array['addappointment.php'] = 2; //TODO DELETE
        // $permission_array['addanimal.php'] = 2; //TODO DELETE
        // $permission_array['addservice.php'] = 2; //TODO DELETE
        // $permission_array['addlocation.php'] = 2; //TODO DELETE
        // $permission_array['viewvece.php'] = 2; //TODO DELETE
        // $permission_array['viewlocation.php'] = 2; //TODO DELETE
        // $permission_array['viewarchived.php'] = 2; //TODO DELETE
        // $permission_array['animal.php'] = 2; //TODO DELETE
        // $permission_array['editanimal.php'] = 2; //TODO DELETE
        $permission_array['eventsuccess.php'] = 2;
        $permission_array['viewsignuplist.php'] = 2;
        $permission_array['vieweventsignups.php'] = 2;
        $permission_array['viewalleventsignups.php'] = 2;
        $permission_array['resources.php'] = 2;
        $permission_array['uploadresources.php'] = 2;        
        $permission_array['deleteresources.php'] = 2;
        $permission_array['creategroup.php'] = 2;
        $permission_array['showgroups.php'] = 2;
        $permission_array['groupview.php'] = 2;
        $permission_array['managemembers.php'] = 2;
        $permission_array['deleteGroup.php'] = 2;
        //admin-only management pages (Level 3)
        $permission_array['volunteermanagement.php'] = 3;
        $permission_array['groupmanagement.php'] = 3;
        $permission_array['eventmanagement.php'] = 3;
        //maintenance management - all roles can access (Level 1)
        $permission_array['startmaintenancerequest.php'] = 1;
        $permission_array['maintman.php'] = 1;
        $permission_array['viewallmaintenancerequests.php'] = 1;
        $permission_array['addmaintenancerequest.php'] = 1;
        $permission_array['editmaintenancerequest.php'] = 1;
        $permission_array['assignmaintenancetasks.php'] = 1;
        $permission_array['viewpendingmaintenancerequests.php'] = 1;
        $permission_array['creatediscussion.php'] = 2;
        $permission_array['checkedinvolunteers.php'] = 2;
        $permission_array['deletediscussion.php'] = 2;
        $permission_array['generatereport.php'] = 2; //adding this to the generate report page
        $permission_array['generateemaillist.php'] = 2; //adding this to the generate report page
        $permission_array['clockoutbulk.php'] = 2;
        $permission_array['clockOut.php'] = 2;
        $permission_array['edithours.php'] = 2;
        $permission_array['eventlist.php'] = 1;
        $permission_array['eventsignup.php'] = 1;
        $permission_array['eventfailure.php'] = 1;
        $permission_array['signupsuccess.php'] = 1;
        $permission_array['edittimes.php'] = 1;
        $permission_array['adminviewingevents.php'] = 2;
        $permission_array['signuppending.php'] = 1;
        $permission_array['requestfailed.php'] = 1;
        $permission_array['settimes.php'] = 1;
        $permission_array['eventfailurebaddeparturetime.php'] = 1;
        $permission_array['index.php'] = 1;
        $permission_array['viewarchive.php'] = 2;
        $permission_array['requesthistory.php'] = 2;
        $permission_array['completemaintenancerequest.php'] = 1;
        
        // LOWERCASE



        //Check if they're at a valid page for their access level.
        $current_page = strtolower(substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1));
        $current_page = substr($current_page, strpos($current_page,"/"));
        
        if($permission_array[$current_page]>$_SESSION['access_level']){
            //in this case, the user doesn't have permission to view this page.
            //we redirect them to the index page.
            echo "<script type=\"text/javascript\">window.location = \"index.php\";</script>";
            //note: if javascript is disabled for a user's browser, it would still show the page.
            //so we die().
            die();
        }
        //This line gives us the path to the html pages in question, useful if the server isn't installed @ root.
        $path = strrev(substr(strrev($_SERVER['SCRIPT_NAME']), strpos(strrev($_SERVER['SCRIPT_NAME']), '/')));
		$venues = array("portland"=>"RMH Portland");
        
        //they're logged in and session variables are set.
	//
	// ADMIN NAVIGATION (Level 3) - Full Access
        if ($_SESSION['access_level'] >= 3) {
		echo('<div class="navbar">
        <!-- Left Section: Logo & Nav Links -->
        <div class="left-section">
            <div class="logo-container">
<a href="index.php"><img src="images/micah-ministries-logo.jpg"
     alt="Micah Ecumenical Ministries" id="logo"
     style="height:52px;width:auto;object-fit:contain;display:block"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item">Lease Management
                    <div class="dropdown">
<a href="leaseman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Leases Hub</span>
  </div>
</a>
<a href="leaseView.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Leases</span>
  </div>
</a>
<a href="addLease.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Add Lease</span>
  </div>
</a>
                    </div>
                </div>
                <div class="nav-item">Maintenance
                    <div class="dropdown">

<a href="maintman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Maintenance Hub</span>
  </div>
</a>
<a href="viewAllMaintenanceRequests.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Requests</span>
  </div>
</a>
<a href="addMaintenanceRequest.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Create Request</span>
  </div>
</a>




                    </div>
                </div>
                <div class="nav-item">User Management
                    <div class="dropdown">

<a href="userman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Users Hub</span>
  </div>
</a>
<a href="personSearch.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/search.svg">
    <span>Search Users</span>
  </div>
</a>
<a href="createNewUser.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/add-user.svg">
    <span>Add User</span>
  </div>
</a>

                    </div>
               </div>
            </div>
        </div>

        <!-- Right Section: Date & Icon -->
        <div class="right-section">
            <div class="date-box"></div>
            <div class="nav-links">
                <div class="nav-item" style="outline:none;">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon">
                        <div class="dropdown">
                            <a href="managePassword.php" style="text-decoration: none;"><div>Change Password</div></a>
                            <a href="logout.php" style="text-decoration: none;"><div>Log Out</div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>');
	}

        // CASE MANAGER NAVIGATION (Level 2) - Lease + Maintenance
        if ($_SESSION['access_level'] == 2) {
		echo('<div class="navbar">
        <!-- Left Section: Logo & Nav Links -->
        <div class="left-section">
            <div class="logo-container">
<a href="index.php"><img src="images/micah-ministries-logo.jpg"
     alt="Micah Ecumenical Ministries" id="logo"
     style="height:52px;width:auto;object-fit:contain;display:block"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item">Lease Management
                    <div class="dropdown">
<a href="leaseman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Leases Hub</span>
  </div>
</a>
<a href="leaseView.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Leases</span>
  </div>
</a>
<a href="addLease.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Add Lease</span>
  </div>
</a>
                    </div>
                </div>
                <div class="nav-item">Maintenance
                    <div class="dropdown">

<a href="maintman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Maintenance Hub</span>
  </div>
</a>
<a href="viewAllMaintenanceRequests.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Requests</span>
  </div>
</a>
<a href="addMaintenanceRequest.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Create Request</span>
  </div>
</a>




                    </div>
                </div>
                <div class="nav-item">Profile
                    <div class="dropdown">
<a href="viewProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/view-profile.svg">
    <span>My Profile</span>
  </div>
</a>
<a href="editProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/edit-pencil.svg">
    <span>Edit Profile</span>
  </div>
</a>
<a href="logout.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/logout.svg">
    <span>Logout</span>
  </div>
</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section: Date & Icon -->
        <div class="right-section">
            <div class="date-box"></div>
            <div class="nav-links">
                <div class="nav-item" style="outline:none;">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon">
                        <div class="dropdown">
                            <a href="managePassword.php" style="text-decoration: none;"><div>Change Password</div></a>
                            <a href="logout.php" style="text-decoration: none;"><div>Log Out</div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>');
	}

        // MAINTENANCE STAFF NAVIGATION (Level 1) - Maintenance Only
        if ($_SESSION['access_level'] == 1) {
		echo('<div class="navbar">
        <!-- Left Section: Logo & Nav Links -->
        <div class="left-section">
            <div class="logo-container">
<a href="index.php"><img src="images/micah-ministries-logo.jpg"
     alt="Micah Ecumenical Ministries" id="logo"
     style="height:52px;width:auto;object-fit:contain;display:block"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item">Maintenance
                    <div class="dropdown">
<a href="maintman.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/dashboard.svg">
    <span>Maintenance Hub</span>
  </div>
</a>
<a href="viewAllMaintenanceRequests.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View All Requests</span>
  </div>
</a>
<a href="addMaintenanceRequest.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Create Request</span>
  </div>
</a>
                    </div>
                </div>
                <div class="nav-item">Profile
                    <div class="dropdown">
<a href="viewProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/view-profile.svg">
    <span>My Profile</span>
  </div>
</a>
<a href="editProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/edit-pencil.svg">
    <span>Edit Profile</span>
  </div>
</a>
<a href="logout.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/logout.svg">
    <span>Logout</span>
  </div>
</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section: Date & Icon -->
        <div class="right-section">
            <div class="date-box"></div>
            <div class="nav-links">
                <div class="nav-item" style="outline:none;">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon">
                        <div class="dropdown">
                            <a href="viewProfile.php" style="text-decoration: none;"><div>View Profile</div></a>
                            <a href="editProfile.php" style="text-decoration: none;"><div>Edit Profile</div></a>
                            <a href="inbox.php" style="text-decoration: none;"><div>Notifications</div></a>
                            <a href="managePassword.php" style="text-decoration: none;"><div>Change Password</div></a>
                            <a href="logout.php" style="text-decoration: none;"><div>Log Out</div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>');
	}


    }
    ?>
<script>
  function updateDateAndCheckBoxes() {
    const now = new Date();
    const width = window.innerWidth;

    // Format the date based on width
    let formatted = "";
    if (width > 1650) {
      formatted = "Today is " + now.toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
      });
    } else if (width >= 1450) {
      formatted = now.toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
      });
    } else {
      formatted = now.toLocaleDateString("en-US"); // e.g., 04/17/2025
    }

    // Update right-section date boxes
    document.querySelectorAll(".right-section .date-box").forEach(el => {
      if (width < 1130) {
        el.style.display = "none";
      } else {
        el.style.display = "";
        el.textContent = formatted;
      }
    });

    // Update left-section date boxes (Check In / Out or icon)
document.querySelectorAll(".left-section .date-box").forEach(el => {
  if (width < 750) {
    el.style.display = "none";
  } else {
    el.style.display = "";
    el.textContent = width < 1130 ? "🔁" : "Check In/Out";
  }
});

document.querySelectorAll(".icon-butt").forEach(el => {
  if (width < 800) {
    el.style.display = "none";
  } else {
    el.style.display = "";
  } 
});




  }

  // Run on load and resize
  window.addEventListener("resize", updateDateAndCheckBoxes);
  window.addEventListener("load", updateDateAndCheckBoxes);
</script>
</header>
