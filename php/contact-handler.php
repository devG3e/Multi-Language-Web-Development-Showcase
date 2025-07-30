<?php
// ===== PHP CONTACT FORM HANDLER =====

require_once 'config.php';

// Set content type to JSON for AJAX requests
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

// Get form data
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!validateEmail($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (empty($message)) {
    $errors[] = 'Message is required';
} elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters long';
}

// Check for spam (simple honeypot)
if (!empty($_POST['website'])) {
    $errors[] = 'Invalid submission detected';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the following errors:',
        'errors' => $errors
    ]);
    exit();
}

try {
    // Save to database
    $contactData = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'status' => 'new',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $contactId = $db->insert('contacts', $contactData);
    
    if (!$contactId) {
        throw new Exception('Failed to save contact message');
    }
    
    // Send email notification (if configured)
    if (defined('ADMIN_EMAIL') && ADMIN_EMAIL) {
        sendContactEmail($contactData);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you soon.',
        'contact_id' => $contactId
    ]);
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}

/**
 * Send email notification to admin
 */
function sendContactEmail($contactData) {
    $to = ADMIN_EMAIL;
    $subject = "New Contact Form Submission: " . $contactData['subject'];
    
    $message = "
    New contact form submission received:
    
    Name: {$contactData['name']}
    Email: {$contactData['email']}
    Subject: {$contactData['subject']}
    Date: {$contactData['created_at']}
    
    Message:
    {$contactData['message']}
    
    IP Address: {$contactData['ip_address']}
    User Agent: {$contactData['user_agent']}
    ";
    
    $headers = [
        'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        'Reply-To: ' . $contactData['email'],
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send auto-reply to user
 */
function sendAutoReply($contactData) {
    $to = $contactData['email'];
    $subject = "Thank you for contacting " . SITE_NAME;
    
    $message = "
    Dear {$contactData['name']},
    
    Thank you for contacting us. We have received your message and will get back to you as soon as possible.
    
    Your message details:
    Subject: {$contactData['subject']}
    Date: {$contactData['created_at']}
    
    Best regards,
    " . SITE_NAME . " Team
    ";
    
    $headers = [
        'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    mail($to, $subject, $message, implode("\r\n", $headers));
}
?> 