<?php
// register.php
require_once __DIR__ . '/../bootstrap.php';

use Angel\IapGroupProject\Controllers\AuthController;

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
    // Process registration if we have the required fields
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
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
        
        $registrationResult = $auth->register(
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
        );
        
        if ($registrationResult) {
            $messages = $auth->getMessages();
            // Don't redirect, show success message on same page
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
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
            flex-wrap: wrap;
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

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error-list, .success-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-list li, .success-list li {
            margin-bottom: 5px;
        }

        .error-list li:before {
            content: "• ";
            color: #dc3545;
        }

        .success-list li:before {
            content: "• ";
            color: #28a745;
        }

        /* Password Strength Indicator */
        .password-section {
            grid-column: 1 / -1;
        }

        .password-strength {
            margin-bottom: 15px;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .strength-label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .strength-bar {
            width: 100%;
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .strength-weak .strength-fill {
            width: 25%;
            background-color: #dc3545;
        }

        .strength-fair .strength-fill {
            width: 50%;
            background-color: #fd7e14;
        }

        .strength-good .strength-fill {
            width: 75%;
            background-color: #ffc107;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background-color: #28a745;
        }

        .strength-text {
            font-size: 12px;
            font-weight: 500;
        }

        .strength-weak .strength-text {
            color: #dc3545;
        }

        .strength-fair .strength-text {
            color: #fd7e14;
        }

        .strength-good .strength-text {
            color: #ffc107;
        }

        .strength-strong .strength-text {
            color: #28a745;
        }

        .password-requirements {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 15px;
        }

        .password-requirements li {
            margin: 2px 0;
        }

        .password-requirements li.valid {
            color: #28a745;
        }

        .password-requirements li.invalid {
            color: #dc3545;
        }

        /* Password Toggle Functionality */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            padding: 2px 6px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .password-toggle:hover {
            color: #4472c4;
            background-color: #f8f9fa;
        }

        .password-toggle:focus {
            outline: none;
            color: #4472c4;
            background-color: #e9ecef;
        }

        .password-toggle:active {
            background-color: #dee2e6;
        }

        /* Adjust padding for password inputs to make room for toggle button */
        .password-wrapper input[type="password"],
        .password-wrapper input[type="text"] {
            padding-right: 50px;
        }

        /* Email Notification Styles */
        .email-notification {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 2px solid #2196f3;
            border-radius: 10px;
            animation: slideIn 0.5s ease-out;
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon {
            font-size: 2.5em;
            flex-shrink: 0;
        }

        .notification-text h4 {
            margin: 0 0 8px 0;
            color: #1976d2;
            font-size: 18px;
            font-weight: 600;
        }

        .notification-text p {
            margin: 0;
            color: #1565c0;
            font-size: 14px;
            line-height: 1.5;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading State for Register Button */
        .register-btn.loading {
            background: #6c757d;
            cursor: not-allowed;
            position: relative;
        }

        .register-btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                gap: 10px;
            }
            
            .radio-option label {
                min-width: 100px;
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

            <?php if (!empty($messages)): ?>
                <div class="alert alert-success" id="success-alert">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <span style="font-size: 32px; margin-right: 15px;">🎉</span>
                        <div>
                            <h3 style="margin: 0; color: #155724; font-size: 22px;">Registration Successful!</h3>
                            <p style="margin: 5px 0 0 0; color: #155724; font-size: 16px; font-weight: 500;">
                                📧 <strong>Verification Email Sent!</strong>
                            </p>
                        </div>
                    </div>
                    <div style="background: rgba(21, 87, 36, 0.1); padding: 15px; border-radius: 8px; margin: 15px 0;">
                        <h4 style="margin: 0 0 10px 0; color: #155724; font-size: 16px;">
                            ✉️ Check Your Email Now
                        </h4>
                        <ul class="success-list" style="margin: 0;">
                            <?php foreach ($messages as $message): ?>
                                <li style="font-size: 14px;"><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #c3e6cb;">
                        <p style="margin: 0 0 15px 0; font-weight: 500; font-size: 14px; color: #155724;">
                            <strong>⚠️ Important:</strong> You must verify your email before logging in. 
                            Check your <strong>inbox AND spam folder</strong>.
                        </p>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="login.php" style="background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: 500; font-size: 14px;">
                                ➡️ Go to Login Page
                            </a>
                            <a href="register.php" style="background: #6c757d; color: white; padding: 12px 20px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: 500; font-size: 14px;">
                                ➕ Register Another User
                            </a>
                        </div>
                    </div>
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
                    <div class="radio-option">
                        <input type="radio" id="admin" name="account_type" value="admin">
                        <label for="admin">Admin</label>
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
                </div>

                <!-- Password Section with Strength Indicator -->
                <div class="form-group password-section">
                    <div class="password-strength" id="password-strength-indicator">
                        <div class="strength-label">Password Strength</div>
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <div class="strength-text">Enter a password to see strength</div>
                        <div class="password-requirements">
                            <ul>
                                <li id="length-req" class="invalid">At least 8 characters</li>
                                <li id="upper-req" class="invalid">At least one uppercase letter</li>
                                <li id="lower-req" class="invalid">At least one lowercase letter</li>
                                <li id="number-req" class="invalid">At least one number</li>
                                <li id="special-req" class="invalid">At least one special character (!@#$%^&*)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="Enter Password"
                                required
                                minlength="8"
                                id="password-field"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password-field', this)">
                                Show
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                name="confirm_password" 
                                placeholder="Confirm Password"
                                required
                                minlength="8"
                                id="confirm-password-field"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm-password-field', this)">
                                Show
                            </button>
                        </div>
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

                <button type="submit" name="register" class="register-btn" id="register-button">
                    Register
                </button>
                
                <!-- Email Verification Notification Area -->
                <div id="email-notification" class="email-notification" style="display: <?php echo !empty($messages) ? 'block' : 'none'; ?>;">
                    <div class="notification-content">
                        <div class="notification-icon">✉️</div>
                        <div class="notification-text">
                            <h4>Verification Email Sent!</h4>
                            <p>We've sent a verification link to your email address. Please check your inbox (and spam folder) to complete your registration.</p>
                        </div>
                    </div>
                </div>
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
            const adminRadio = document.getElementById('admin');
            
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
                
            } else if (adminRadio.checked) {
                // ADMIN: Hide both dog preferences and license fields
                dogPreferencesSection.style.display = 'none';
                dogPreferencesFields.style.display = 'none';
                licenseSection.style.display = 'none';
                licenseFields.style.display = 'none';
                contactFields.style.display = 'none';
                
                // Clear all values for admin
                dogPreferenceInputs.forEach(input => {
                    if (input) {
                        input.value = '';
                        input.required = false;
                    }
                });
                
                breedCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                licenseInputs.forEach(input => {
                    if (input) {
                        input.required = false;
                        input.value = '';
                    }
                });
                
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
        document.getElementById('admin').addEventListener('change', handleAccountTypeChange);

        // Initialize form state on page load
        handleAccountTypeChange();

        // Form validation - Simplified version
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submission started...');
            
            // Show loading state immediately
            const submitBtn = document.getElementById('register-button');
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // Hide any existing notifications
            const emailNotification = document.getElementById('email-notification');
            if (emailNotification) {
                emailNotification.style.display = 'none';
            }
            
            console.log('Form will submit normally...');
            // Let the form submit normally - HTML5 validation will handle basic checks
        });

        // Password Strength Functionality
        function checkPasswordStrength(password) {
            let score = 0;
            const requirements = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            // Calculate score
            Object.values(requirements).forEach(req => req && score++);

            return { score, requirements };
        }

        function updatePasswordStrength(password) {
            const strengthIndicator = document.getElementById('password-strength-indicator');
            const strengthBar = strengthIndicator.querySelector('.strength-fill');
            const strengthText = strengthIndicator.querySelector('.strength-text');
            
            if (!password) {
                strengthIndicator.className = 'password-strength';
                strengthText.textContent = 'Enter a password to see strength';
                return;
            }

            const { score, requirements } = checkPasswordStrength(password);
            
            // Update requirements list
            document.getElementById('length-req').className = requirements.length ? 'valid' : 'invalid';
            document.getElementById('upper-req').className = requirements.upper ? 'valid' : 'invalid';
            document.getElementById('lower-req').className = requirements.lower ? 'valid' : 'invalid';
            document.getElementById('number-req').className = requirements.number ? 'valid' : 'invalid';
            document.getElementById('special-req').className = requirements.special ? 'valid' : 'invalid';

            // Update strength display
            strengthIndicator.className = 'password-strength';
            if (score <= 1) {
                strengthIndicator.classList.add('strength-weak');
                strengthText.textContent = 'Weak';
            } else if (score <= 2) {
                strengthIndicator.classList.add('strength-fair');
                strengthText.textContent = 'Fair';
            } else if (score <= 4) {
                strengthIndicator.classList.add('strength-good');
                strengthText.textContent = 'Good';
            } else {
                strengthIndicator.classList.add('strength-strong');
                strengthText.textContent = 'Strong';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password-field').value;
            const confirmPassword = document.getElementById('confirm-password-field').value;
            const confirmField = document.getElementById('confirm-password-field');

            if (confirmPassword && password !== confirmPassword) {
                confirmField.style.borderColor = '#dc3545';
                confirmField.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            } else if (confirmPassword && password === confirmPassword) {
                confirmField.style.borderColor = '#28a745';
                confirmField.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
            } else {
                confirmField.style.borderColor = '#e0e0e0';
                confirmField.style.boxShadow = 'none';
            }
        }

        // Add event listeners for password fields
        document.getElementById('password-field').addEventListener('input', function() {
            updatePasswordStrength(this.value);
            checkPasswordMatch();
        });

        document.getElementById('confirm-password-field').addEventListener('input', function() {
            checkPasswordMatch();
        });

        // Clear any previous form errors when user starts typing
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const alerts = document.querySelectorAll('.alert-danger');
                alerts.forEach(alert => alert.style.display = 'none');
            });
        });

        // Add feedback for form interaction
        document.addEventListener('DOMContentLoaded', function() {
            // Reset button state if there were validation errors
            const submitBtn = document.getElementById('register-button');
            if (submitBtn) {
                submitBtn.textContent = 'Register';
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            }
        });

        // Show email notification if registration was successful
        <?php if (!empty($messages)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide the regular success message after 2 seconds and show the email notification
            setTimeout(function() {
                const successAlert = document.querySelector('.alert-success');
                const emailNotification = document.getElementById('email-notification');
                
                if (successAlert && emailNotification) {
                    // Fade out success alert
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    
                    setTimeout(function() {
                        successAlert.style.display = 'none';
                        // Show email notification
                        emailNotification.style.display = 'block';
                        // Scroll to notification
                        emailNotification.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 500);
                }
            }, 3000); // Show success message for 3 seconds before switching
        });
        <?php endif; ?>

        // Password Toggle Functionality
        function togglePassword(fieldId, button) {
            const passwordField = document.getElementById(fieldId);
            const isPassword = passwordField.type === 'password';
            
            // Toggle input type
            passwordField.type = isPassword ? 'text' : 'password';
            
            // Toggle button text
            button.textContent = isPassword ? 'Hide' : 'Show';
            
            // Optional: Add tooltip
            button.title = isPassword ? 'Hide password' : 'Show password';
        }
    </script>
</body>
</html>