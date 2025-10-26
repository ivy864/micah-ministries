<!-- This looks really, really great!  -Thomas -->
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
<?php if (empty($tailwind_mode)): ?>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
<?php endif; ?>
        body {
            font-family: Quicksand, sans-serif;
            padding-top: 96px;
        }
        h2 {
        	font-weight: normal;
            font-size: 30px;
        }

/*BEGIN STYLE TEST*/
         .extra-info {
            max-height: 0px;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            font-size: 14px;
            color: #444;
            margin-top: 5px;
        }
       .content-box-test{
            flex: 1 1 370px; /* Adjusts width dynamically */
            max-width: 470px;
            padding: 10px 10px; /* Altered padding to make closer */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            cursor: pointer;
            border: 0.1px solid black;
            transition: border 0.3s;
            border-radius: 10px;
            border-bottom-right-radius: 50px;
        }
         .content-box-test:hover {
            border: 4px solid #007BFF;
        }
/*END STYLE TEST*/

        .full-width-bar {
            width: 100%;
            background: #294877;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .full-width-bar-sub {
            width: 100%;
            background: white;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .content-box {
            flex: 1 1 280px; /* Adjusts width dynamically */
            max-width: 375px;
            padding: 10px 2px; /* Altered padding to make closer */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box-sub {
            flex: 1 1 300px; /* Adjusts width dynamically */
            max-width: 470px;
            padding: 10px 10px; /* Altered padding to make closer */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box img {
            width: 100%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 0.5px solid #828282;
        }

        .content-box-sub img {
            width: 105%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 1px solid #828282;
        }

        .small-text {
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 14px;
            font-weight: 700;
            color: #294877;
        }

        .large-text {
            position: absolute;
            top: 40px;
            left: 30px;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .large-text-sub {
            position: absolute;
            /*top: 120px;*/
            top: 60%;
            left: 10%;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .graph-text {
            position: absolute;
            top: 75%;
            left: 10%;
            font-size: 14px;
            font-weight: 700;
            color: #294877;
            max-width: 90%;
        }

        /* Navbar Container */
        .navbar {
	    gap: 10px;
            width: 100%;
            height: 95px;
            position: fixed;
            top: 0;
            left: 0;
            background: white;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }

        /* Left Section: Logo & Nav Links */
        .left-section {
            display: flex;
            align-items: center;
            gap: 20px; /* Space between logo and links */
        }

        /* Logo 
        .logo-container {
            background: #294877;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25) inset;
        } */

        .logo-container img#logo {
            width: 52px;
            height: auto;
            display: block;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links div {
            font-size: 24px;
            font-weight: 700;
            color: black;
            cursor: pointer;
        }

        /* Right Section: Date & Icon */
        .right-section {
            margin-left: auto; /* Pushes right section to the end */
            display: flex;
            align-items: center;
            gap: 20px;
	}

        /* Dropdown Control */
        .nav-item {
            position: relative;
            cursor: pointer;
            padding: 0px;
            transition: color 0.3s, outline 0.3s;
        }


        .dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 8px);   /* sit just below the nav item */
    left: 0;                 /* align with the left edge of the nav item */
    background-color: white;
    border: 1px solid #ccc;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-radius: 5px;

    width: max-content;      
    min-width: 150px;        
    white-space: nowrap;     
    box-sizing: border-box;  
    z-index: 2000;
    padding: 10px;
}
        .dropdown div {
            padding: 8px;
            white-space: nowrap;
            transition: background 0.3s;
        }
        .dropdown div:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .nav-item:hover, .nav-item.active {
            color: #7aacf5;
            outline: 1px solid #7aacf5;
            outline-offset: 7px;
        }

        .date-box {
            background: #274471;
            padding: 10px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }

        .icon {
            width: 47px;
            height: 47px;
            /*background: #292D32;*/
            border-radius: 50%;

        }
.nav-buttons {
    position: absolute;
    bottom: 10%; /* Adjust as needed */
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    justify-content: center;
    width: 100%;
}

/* Button Styling */
.nav-button {
    background: #274471;
    border: none;
    color: white;
    font-size: 20px;
    font-family: 'Quicksand', sans-serif;
    font-weight: 600;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.4s ease-in-out;
    backdrop-filter: blur(8px);
    padding: 6px 8px;
    padding-top: 10px;
    width: 55px; /* Initially a circle */
    overflow: hidden;
    white-space: nowrap;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Expand button on hover */
.nav-button:hover {
    width: 160px;
    padding: 6px 8px;
    padding-top: 10px
}

.nav-button .text {
    opacity: 0;
    transform: translateX(-10px);
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.nav-button:hover .text {
    opacity: 1;
    transform: translateX(0);
}

.nav-button .arrow {
    display: inline-block;
    transition: transform 0.3s ease;
}

.nav-button:hover .arrow {
    transform: translateX(5px);
}
       /* Button Control */
        .arrow-button {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .arrow-button:hover {
            transform: translateX(5px); /* Moves the arrow slightly on hover */
        }

        /* Footer */
        .footer {
            width: 100%;
            background: #294877;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 30px 50px;
            flex-wrap: wrap;
        }

        /* Left Section */
        .footer-left {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-logo {
            width: 150px; /* Adjust logo size */
            margin-bottom: 15px;
        }

        /* Social Media Icons */
        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            color: white;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #dcdcdc;
        }

        /* Right Section */
        .footer-right {
            display: flex;
            gap: 50px;
            flex-wrap: wrap;
        }

        .footer-section {
            display: flex;
            flex-direction: column;
            gap: 10px;
            color: white;
            font-family: Inter, sans-serif;
            font-size: 16px;
            font-weight: 500;
        }

        .footer-topic {
            font-size: 18px;
            font-weight: bold;
        }

        .footer a {
            color: white;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .footer a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #dcdcdc;
        }

        /* Icon Overlay */
        .background-image {
            width: 100%;
            border-radius: 17px;
        }

        .icon-overlay {
            position: absolute;
            top: 40px; /* Adjust as needed */
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.8); /* Optional background for better visibility */
            padding: 10px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-overlay img {
            width: 40px; /* Adjust size as needed */
            height: 40px;
            opacity: 0.9;
        }

        .nav-item img {
            border-radius: 15px;
            transition: filter 0.3s, background-color 0.3s;
        }

        .nav-item:hover img, .nav-item.active img {
            filter: none;
            background-color: #cbe0ff;
        }
       
        .icon .dropdown{
            top: 130%;
            left: -415%;
        }

        .in-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }
	.in-nav span {
	    font-size:24px;
	}

	.in-nav img {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            border-bottom-right-radius: 20px;
        }

/* for calendar */
    .icon-butt svg {
        transition: transform 0.2s ease, fill 0.2s ease;
        cursor: pointer;
    }

    .icon-butt:hover svg {
        transform: scale(1.1) rotate(5deg); /* Slight enlarge & tilt effect */
        fill: #7aacf5; /* Changes to a blue shade */
    }

    .font-change {
	font-size: 30px;
	font-family: Quicksand;
    }



        /* Responsive Design */
	@media (max-width: 0px) {
	   .content-box-test {
		flex: 1 1 300px;
	    }
	}

        @media (max-width: 900px) {
           .footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .footer-right {
                flex-direction: column;
                align-items: center;
                gap: 30px;
                margin-top: 20px;
            }

        }
    </style>

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
        $permission_array['micahportal.php'] = 1;
        
        //pages case managers can view (Level 2) - Lease + Maintenance
        $permission_array['editlease.php'] = 2;
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
        $permission_array['personsearch.php'] = 3;
        $permission_array['personedit.php'] = 0; // changed to 0 so that applicants can apply
        $permission_array['viewschedule.php'] = 3;
        $permission_array['addweek.php'] = 3;
        $permission_array['log.php'] = 3;
        $permission_array['reports.php'] = 3;
        $permission_array['eventedit.php'] = 3;
        $permission_array['modifyuserrole.php'] = 3;
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
        $permission_array['micahportal.php'] = 1;
        
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
<a href="micahportal.php"><img src="images/micah-ministries-logo.jpg"
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
<a href="calendar.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/view-calendar.png">
    <span>Calendar</span>
  </div>
</a>
<a href="eventsearch.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/search.svg">
    <span>Search Events</span>
  </div>
</a>
<a href="addLease.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Add Lease</span>
  </div>
</a>
<a href="editLease.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/edit-pencil.svg">
    <span>Edit Lease</span>
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
<a href="assignMaintenanceTasks.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/user-check.svg">
    <span>Assign Tasks</span>
  </div>
</a>
<a href="viewPendingMaintenanceRequests.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/clock-regular.svg">
    <span>Pending Requests</span>
  </div>
</a>


                    </div>
                </div>
                <div class="nav-item">User Management
                    <div class="dropdown">

<a href="personSearch.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/search.svg">
    <span>Search Users</span>
  </div>
</a>
<a href="modifyUserRole.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/user-check.svg">
    <span>Modify User Role</span>
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
<a href="calendar.php">
<div class="icon-butt">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="#294877" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 4C3 3.44772 3.44772 3 4 3H6V2C6 1.44772 6.44772 1 7 1C7.55228 1 8 1.44772 8 2V3H16V2C16 1.44772 16.4477 1 17 1C17.5523 1 18 1.44772 18 2V3H20C20.5523 3 21 3.44772 21 4V21C21 21.5523 20.5523 22 20 22H4C3.44772 22 3 21.5523 3 21V4ZM5 5V20H19V5H5ZM7 10H9V12H7V10ZM11 10H13V12H11V10ZM15 10H17V12H15V10ZM7 14H9V16H7V14ZM11 14H13V16H11V14ZM15 14H17V16H15V14Z"/>
        </svg>
</div>
</a>
            <div class="date-box"></div>
            <div class="nav-links">
                <div class="nav-item" style="outline:none;">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon">
                        <div class="dropdown">
                            <a href="changePassword.php" style="text-decoration: none;"><div>Change Password</div></a>
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
<a href="micahportal.php"><img src="images/micah-ministries-logo.jpg"
     alt="Micah Ecumenical Ministries" id="logo"
     style="height:52px;width:auto;object-fit:contain;display:block"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item">Lease Management
                    <div class="dropdown">
<a href="leaseView.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Leases</span>
  </div>
</a>
<a href="calendar.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/calendar.svg">
    <span>Calendar</span>
  </div>
</a>
<a href="eventsearch.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/search.svg">
    <span>Search Events</span>
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
    <img src="images/user.svg">
    <span>My Profile</span>
  </div>
</a>
<a href="editProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/edit.svg">
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
    </div>');
	}

        // MAINTENANCE STAFF NAVIGATION (Level 1) - Maintenance Only
        if ($_SESSION['access_level'] == 1) {
		echo('<div class="navbar">
        <!-- Left Section: Logo & Nav Links -->
        <div class="left-section">
            <div class="logo-container">
<a href="micahportal.php"><img src="images/micah-ministries-logo.jpg"
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
<a href="viewPendingMaintenanceRequests.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/clock-regular.svg">
    <span>Pending Requests</span>
  </div>
</a>
<a href="assignMaintenanceTasks.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/user-check.svg">
    <span>Assign Tasks</span>
  </div>
</a>
                    </div>
                </div>
                <div class="nav-item">Profile
                    <div class="dropdown">
<a href="viewProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/user.svg">
    <span>My Profile</span>
  </div>
</a>
<a href="editProfile.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/edit.svg">
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
<a href="calendar.php">
<div class="icon-butt">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="#294877" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 4C3 3.44772 3.44772 3 4 3H6V2C6 1.44772 6.44772 1 7 1C7.55228 1 8 1.44772 8 2V3H16V2C16 1.44772 16.4477 1 17 1C17.5523 1 18 1.44772 18 2V3H20C20.5523 3 21 3.44772 21 4V21C21 21.5523 20.5523 22 20 22H4C3.44772 22 3 21.5523 3 21V4ZM5 5V20H19V5H5ZM7 10H9V12H7V10ZM11 10H13V12H11V10ZM15 10H17V12H15V10ZM7 14H9V16H7V14ZM11 14H13V16H11V14ZM15 14H17V16H15V14Z"/>
        </svg>
</div>
</a>
            <div class="date-box"></div>
            <div class="nav-links">
                <div class="nav-item" style="outline:none;">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon">
                        <div class="dropdown">
                            <a href="viewProfile.php" style="text-decoration: none;"><div>View Profile</div></a>
                            <a href="editProfile.php" style="text-decoration: none;"><div>Edit Profile</div></a>
                            <a href="volunteerReport.php" style="text-decoration: none;"><div>View Hours</div></a>
                            <a href="inbox.php" style="text-decoration: none;"><div>Notifications</div></a>
                            <a href="changePassword.php" style="text-decoration: none;"><div>Change Password</div></a>
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
