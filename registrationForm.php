<!-- Hero Section with Title -->
<header class="hero-header"> 
    <div class="center-header">
        <h1>Add User</h1>
    </div>
</header>

<main>
  <div class="main-content-box w-full max-w-3xl p-8 mb-8">
    <form class="signup-form" method="post">
	<div class="text-center mb-8">
          <h2 class="mb-8">Registration Form</h2>
            <div class="main-content-box border-2 mb-0 shadow-xs w-full p-4">
              <p class="sub-text">Please fill out each section of the following form to add a user to the system.</p>
              <p>An asterisk (<em>*</em>) indicates a required field.</p>
            </div>
	</div>
        
        <fieldset class="section-box mb-4">

            <h3 class="mt-2">Personal Information</h3>
	    <div class="blue-div"></div>

            <label for="first_name"><em>* </em>First Name</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">

            <label for="last_name"><em>* </em>Last Name</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">

        </fieldset>

        <fieldset class="section-box mb-4">
            <h3>Contact Information</h3>
	    <div class="blue-div"></div>

            <label for="email"><em>* </em>E-mail</label>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

            <label><em>* </em>Phone Type</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="phone-type-cellphone" name="phone_type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-home" name="phone_type" value="home" required><label for="phone-type-home">Home</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-work" name="phone_type" value="work" required><label for="phone-type-work">Work</label>
	      </div>
            </div>

        </fieldset>
                
        <script>
            

            
           

            
            

             // Event listeners for changes in volunteer/participant selection and the complete statuses
            //document.querySelectorAll('input[name="is_community_service_volunteer"]').forEach(radio => {
              //  radio.addEventListener('change', toggleTrainingSection);
            //});



            
            // Initial check on page load
            
        </script>


        <fieldset class="section-box mb-4">
            <h3>Login Credentials</h3>
	    <div class="blue-div"></div>

            <label for="permissions"><em>* </em>Role(s)</label>
            <label for="admin">Admin</label><input type="checkbox" id="admin" name="Admin" value="0" required>
            <label for="casemanager">Case Manager</label><input type="checkbox" id="casemanager" name="Case Manager" value="1" required>
            <label for="maintperson">Maintenance Person</label><input type="checkbox" id="maintperson" name="Maintenance Person" value="2" required>

            <label for="username"><em>* </em>Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter a username">

            <label for="password"><em>* </em>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required>
            <p id="password-error" class="error hidden">Password needs to be at least 8 characters long, contain at least one number, one uppercase letter, and one lowercase letter!</p>

            <label for="password-reenter"><em>* </em>Re-enter Password</label>
            <input type="password" id="password-reenter" name="password-reenter" placeholder="Re-enter password" required>
            <p id="password-match-error" class="error hidden">Passwords do not match!</p>
            
              <!-- Required by backend -->
        <input type="hidden" name="is_new_volunteer" value="1">
        <input type="hidden" name="total_hours_volunteered" value="0">
        </fieldset>
            
        <input type="submit" name="registration-form" value="Submit" class="blue-button">
    </form>
   </div> 
</main>
