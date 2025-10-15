<?php
// NotificationInterface.php
interface NotificationInterface {
    public function send($to, $subject, $message);
}

// EmailNotification.php
class EmailNotification implements NotificationInterface {
    public function send($to, $subject, $message) {
        // Send email using PHPMailer
        mail($to, $subject, $message);
    }
}

// SMSNotification.php (for future expansion)
class SMSNotification implements NotificationInterface {
    public function send($to, $subject, $message) {
        // Send SMS logic
    }
}
