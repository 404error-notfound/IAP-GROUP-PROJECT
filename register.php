<?php
// register.php
require_once 'AuthController.php';

$auth = new AuthController();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$messages = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password'] ?? ''; // Using same password field
        $account_type = $_POST['account_type'] ?? 'client';
        $gender = $_POST['gender'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $preferred_breed = $_POST['preferred_breed'] ?? [];
        // Ensure $preferred_breed is always an array
        if (!is_array($preferred_breed)) {
            $preferred_breed = [$preferred_breed];
        }
        $preferred_age = $_POST['preferred_age'] ?? null;
        $license_number = $_POST['license_number'] ?? null;
        $location = $_POST['location'] ?? null;
        $contact_email_1 = $_POST['contact_email_1'] ?? null;
        $contact_email_2 = $_POST['contact_email_2'] ?? null;
        
        if ($auth->register(
            $email, 
            $full_name, 
            $password, 
            $confirmPassword, 
            $account_type, 
            $gender, 
            $phone, 
            $preferred_breed, 
            $preferred_age, 
            $license_number, 
            $location, 
            $contact_email_1, 
            $contact_email_2
        )) {
            $_SESSION['flash_messages'] = $auth->getMessages();
            header("Location: login.php");
            exit;
        } else {
            $errors = $auth->getErrors();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Go Puppy Go</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header {
            text-align: center;
            padding: 40px 20px 30px;
            background: white;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: #3b4a6b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .logo-text {
            font-size: 36px;
            font-weight: 600;
            color: #3b4a6b;
        }

        .page-title {
            font-size: 32px;
            font-weight: 500;
            color: #3b4a6b;
            margin-top: 30px;
        }

        .form-container {
            padding: 0 40px 40px;
        }

        .account-type {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
        }

        .radio-option {
            position: relative;
        }

        .radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .radio-option label {
            display: block;
            padding: 12px 30px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
            min-width: 120px;
            text-align: center;
        }

        .radio-option input[type="radio"]:checked + label {
            background: #4472c4;
            color: white;
            border-color: #4472c4;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4472c4;
        }

        .form-group input::placeholder {
            color: #999;
        }

        /* Checkbox Styling */
        .checkbox-group {
            grid-column: 1 / -1;
        }

        .checkbox-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
            padding: 0;
            transform: scale(1.2);
            accent-color: #4472c4;
        }

        .checkbox-item label {
            margin: 0;
            font-size: 14px;
            color: #333;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-item:hover label {
            color: #4472c4;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 30px 0 20px 0;
        }

        .register-btn {
            width: 100%;
            padding: 16px;
            background: #4472c4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 30px;
        }

        .register-btn:hover {
            background: #365a96;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .error-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 5px;
        }

        .error-list li:before {
            content: "• ";
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .form-container {
                padding: 0 20px 30px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .account-type {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">🐶</div>
                <div class="logo-text">GoPuppyGo</div>
            </div>
            <h1 class="page-title">Registration</h1>
        </div>

        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Account Type Selection -->
                <div class="account-type">
                    <div class="radio-option">
                        <input type="radio" id="client" name="account_type" value="client" checked>
                        <label for="client">Client</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="rehomer" name="account_type" value="rehomer">
                        <label for="rehomer">Rehomer</label>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="form-group full-width">
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="Name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <select name="gender" required>
                            <option value="">Gender</option>
                            <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input 
                            type="tel" 
                            name="phone" 
                            placeholder="Phone number"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >
                    </div>
                    <div class="form-group">
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Password"
                            required
                            minlength="6"
                        >
                    </div>
                </div>

                <!-- Dog Preferences Section - Only for Clients -->
                <div class="section-title" id="dog-preferences-section">Dog preferences</div>
                
                <div class="form-row" id="dog-preferences-fields">
                    <div class="form-group checkbox-group" id="preferred_breed_field">
                        <label>Preferred Breeds (Select all that apply):</label>
                        <div class="checkbox-container">
                            <?php 
                            $breed_options = [
                                'German Shepherd',
                                'Golden Retriever', 
                                'Japanese Spitz',
                                'Husky',
                                'Rottweiler',
                                'Pug',
                                'Pitbull',
                                'Dachshund',
                                'Doberman Pinscher',
                                'Poodle',
                                'Bulldog',
                                'Bloodhound',
                                'Cocker Spaniel',
                                'Mixed Breed',
                                'No Preferences'
                            ];
                            
                            $selected_breeds = $_POST['preferred_breed'] ?? [];
                            // Ensure $selected_breeds is always an array
                            if (!is_array($selected_breeds)) {
                                $selected_breeds = [$selected_breeds];
                            }
                            ?>
                            
                            <?php foreach ($breed_options as $breed): ?>
                                <div class="checkbox-item">
                                    <input 
                                        type="checkbox" 
                                        name="preferred_breed[]" 
                                        value="<?php echo htmlspecialchars($breed); ?>"
                                        id="breed_<?php echo str_replace([' ', '_'], '_', strtolower($breed)); ?>"
                                        <?php echo in_array($breed, $selected_breeds) ? 'checked' : ''; ?>
                                    >
                                    <label for="breed_<?php echo str_replace([' ', '_'], '_', strtolower($breed)); ?>">
                                        <?php echo htmlspecialchars($breed); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input 
                            type="number" 
                            name="preferred_age" 
                            placeholder="Age"
                            min="0"
                            max="20"
                            id="preferred_age_field"
                        >
                    </div>
                </div>

                <!-- License Information Section - Only for Rehomers -->
                <div class="section-title" id="license-section" style="display: none;">License number</div>
                
                <div class="form-row" id="license-fields" style="display: none;">
                    <div class="form-group">
                        <input 
                            type="text" 
                            name="license_number" 
                            placeholder="License number"
                            id="license_number_field"
                        >
                    </div>
                    <div class="form-group">
                        <input 
                            type="text" 
                            name="location" 
                            placeholder="Location"
                            id="location_field"
                        >
                    </div>
                </div>

                <div class="form-row" id="contact-fields" style="display: none;">
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="contact_email_1" 
                            placeholder="Contact email"
                            id="contact_email_1_field"
                        >
                    </div>
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="contact_email_2" 
                            placeholder="Contact email"
                            id="contact_email_2_field"
                        >
                    </div>
                </div>

                <button type="submit" name="register" class="register-btn">
                    Register
                </button>
            </form>
        </div>
    </div>

    <script>
        // Auto-focus on the first input field
        document.querySelector('input[name="name"]').focus();

        // Handle account type change
        function handleAccountTypeChange() {
            const clientRadio = document.getElementById('client');
            const rehomerRadio = document.getElementById('rehomer');
            
            // Dog preferences elements
            const dogPreferencesSection = document.getElementById('dog-preferences-section');
            const dogPreferencesFields = document.getElementById('dog-preferences-fields');
            
            // License elements
            const licenseSection = document.getElementById('license-section');
            const licenseFields = document.getElementById('license-fields');
            const contactFields = document.getElementById('contact-fields');
            
            // Get all input fields
            const dogPreferenceInputs = [
                document.getElementById('preferred_age_field')
            ];
            
            // Get all breed checkboxes
            const breedCheckboxes = document.querySelectorAll('input[name="preferred_breed[]"]');
            
            const licenseInputs = [
                document.getElementById('license_number_field'),
                document.getElementById('location_field'),
                document.getElementById('contact_email_1_field'),
                document.getElementById('contact_email_2_field')
            ];

            if (rehomerRadio.checked) {
                // REHOMER: Hide dog preferences, show license fields
                dogPreferencesSection.style.display = 'none';
                dogPreferencesFields.style.display = 'none';
                
                // Clear dog preference values for rehomers
                dogPreferenceInputs.forEach(input => {
                    if (input) {
                        input.value = '';
                        input.required = false;
                    }
                });
                
                // Uncheck all breed checkboxes for rehomers
                breedCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Show license section for rehomers
                licenseSection.style.display = 'block';
                licenseFields.style.display = 'grid';
                contactFields.style.display = 'grid';
                
                // Make license fields required for rehomers
                licenseInputs[0].required = true; // license_number
                licenseInputs[1].required = true; // location
                
            } else {
                // CLIENT: Show dog preferences, hide license fields
                dogPreferencesSection.style.display = 'block';
                dogPreferencesFields.style.display = 'grid';
                
                // Hide license section for clients
                licenseSection.style.display = 'none';
                licenseFields.style.display = 'none';
                contactFields.style.display = 'none';
                
                // Remove required attribute and clear values for clients
                licenseInputs.forEach(input => {
                    if (input) {
                        input.required = false;
                        input.value = '';
                    }
                });
            }
        }

        // Add event listeners for radio buttons
        document.getElementById('client').addEventListener('change', handleAccountTypeChange);
        document.getElementById('rehomer').addEventListener('change', handleAccountTypeChange);

        // Initialize form state on page load
        handleAccountTypeChange();

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const email = document.querySelector('input[name="email"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            const isRehomer = document.getElementById('rehomer').checked;

            if (!name) {
                e.preventDefault();
                alert('Please enter your name.');
                document.querySelector('input[name="name"]').focus();
                return;
            }

            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                document.querySelector('input[name="email"]').focus();
                return;
            }

            if (!password || password.length < 6) {
                e.preventDefault();
                alert('Please enter a password (at least 6 characters).');
                document.querySelector('input[name="password"]').focus();
                return;
            }

            // Additional validation for rehomers
            if (isRehomer) {
                const licenseNumber = document.getElementById('license_number_field').value.trim();
                const location = document.getElementById('location_field').value.trim();

                if (!licenseNumber) {
                    e.preventDefault();
                    alert('License number is required for rehomers.');
                    document.getElementById('license_number_field').focus();
                    return;
                }

                if (!location) {
                    e.preventDefault();
                    alert('Location is required for rehomers.');
                    document.getElementById('location_field').focus();
                    return;
                }
            }

            // Show loading state
            const submitBtn = document.querySelector('button[name="register"]');
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
        });

        // Clear any previous form errors when user starts typing
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const alerts = document.querySelectorAll('.alert-danger');
                alerts.forEach(alert => alert.style.display = 'none');
            });
        });
    </script>
</body>
</html>