<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../login.html");
    exit();
}

include 'imap_functions.php';

$email = $_SESSION['email'];
$password = 'password'; // retrieve or manage the password securely

$messages = fetch_emails($email, $password);

foreach ($messages as $message) {
    echo "<li>";
    echo "<strong>" . $message->getSubject() . "</strong><br>";
    echo "De : " . $message->getFrom()[0]->mail . "<br>";
    echo "Date : " . $message->getDate()->format('d/m/Y H:i') . "<br>";
    echo "<p>" . $message->getTextBody() . "</p>";

    // Save and display attachments
    $attachment_dir = "../attachments/" . $message->getUid();
    if (!file_exists($attachment_dir)) {
        mkdir($attachment_dir, 0777, true);
    }

    save_attachments($message, $attachment_dir);

    $attachments = $message->getAttachments();
    if (count($attachments) > 0) {
        echo "<p>Attachments:</p><ul>";
        foreach ($attachments as $attachment) {
            $filePath = $attachment_dir . '/' . $attachment->getName();
            echo "<li><a href='$filePath'>" . $attachment->getName() . "</a></li>";
        }
        echo "</ul>";
    }

    echo "</li>";
}
?>
