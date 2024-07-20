<?php
require '../vendor/autoload.php'; // assuming you installed the imap package via composer

use Webklex\IMAP\ClientManager;

function fetch_emails($email, $password) {
    $cm = new ClientManager('config/imap.php');
    $client = $cm->account('default');
    $client->connect();

    $inbox = $client->getFolder('INBOX');
    $messages = $inbox->messages()->all()->get();

    return $messages;
}

function save_attachments($message, $attachment_dir) {
    $attachments = $message->getAttachments();

    foreach ($attachments as $attachment) {
        $filePath = $attachment_dir . '/' . $attachment->getName();
        $attachment->save($attachment_dir, $attachment->getName());
    }
}
?>
