<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['imap_password']) || !isset($_GET['email_number'])) {
    echo 'Access denied';
    exit();
}

$email = $_SESSION['email'];
$imap_password = $_SESSION['imap_password'];
$email_number = (int)$_GET['email_number'];

// Se connecter au serveur IMAP
$imap_host = 'mail.shipmanager.fr';
$imap_port = 993;
$imap_user = $email;

$inbox = imap_open("{{$imap_host}:$imap_port/imap/ssl}INBOX", $imap_user, $imap_password);

if (!$inbox) {
    echo 'Cannot connect to mail server: ' . imap_last_error();
    exit();
}

$overview = imap_fetch_overview($inbox, $email_number, 0)[0];
$structure = imap_fetchstructure($inbox, $email_number);

// Fonction pour extraire le contenu textuel de l'email
function get_body($inbox, $email_number, $part, $part_number = null) {
    $data = ($part_number) ? imap_fetchbody($inbox, $email_number, $part_number) : imap_body($inbox, $email_number);

    if ($part->encoding == 3) { // base64
        $data = base64_decode($data);
    } elseif ($part->encoding == 4) { // quoted-printable
        $data = quoted_printable_decode($data);
    }

    return $data;
}

// Fonction pour obtenir uniquement le texte principal de l'email
function get_main_message($inbox, $email_number, $structure, &$body, $part_number = null) {
    if (isset($structure->parts) && count($structure->parts)) {
        foreach ($structure->parts as $index => $sub_part) {
            $part_num = $part_number ? "$part_number." . ($index + 1) : ($index + 1);

            if ($sub_part->type == 0 && $sub_part->subtype == 'PLAIN' && !isset($sub_part->disposition)) {
                $body .= get_body($inbox, $email_number, $sub_part, $part_num);
            } elseif ($sub_part->type == 1 && isset($sub_part->parts)) {
                get_main_message($inbox, $email_number, $sub_part, $body, $part_num);
            }
        }
    } else {
        if ($structure->type == 0 && $structure->subtype == 'PLAIN') {
            $body .= get_body($inbox, $email_number, $structure);
        }
    }
}

// Fonction pour obtenir les pièces jointes
function get_attachments($inbox, $email_number, $structure, &$attachments, $part_number = null) {
    if (isset($structure->parts) && count($structure->parts)) {
        foreach ($structure->parts as $index => $sub_part) {
            $part_num = $part_number ? "$part_number." . ($index + 1) : ($index + 1);

            if (isset($sub_part->disposition) && ($sub_part->disposition == 'attachment' || $sub_part->disposition == 'inline')) {
                $filename = $sub_part->dparameters[0]->value ?? $sub_part->parameters[0]->value ?? '';
                $attachment = array(
                    'is_attachment' => true,
                    'filename' => $filename,
                    'attachment' => get_body($inbox, $email_number, $sub_part, $part_num)
                );
                $attachments[] = $attachment;
            } elseif ($sub_part->type == 1 && isset($sub_part->parts)) {
                get_attachments($inbox, $email_number, $sub_part, $attachments, $part_num);
            }
        }
    }
}

// Initialisation des variables pour le corps et les pièces jointes
$body = '';
$attachments = array();

get_main_message($inbox, $email_number, $structure, $body);
get_attachments($inbox, $email_number, $structure, $attachments);

?>

<p><strong>From:</strong> <?php echo htmlspecialchars($overview->from); ?></p>
<p><strong>To:</strong> <?php echo htmlspecialchars($overview->to); ?></p>
<p><strong>Date:</strong> <?php echo htmlspecialchars($overview->date); ?></p>
<p><strong>Subject:</strong> <?php echo htmlspecialchars(imap_utf8($overview->subject)); ?></p>
<h3>Message:</h3>
<p><?php echo nl2br(htmlspecialchars($body)); ?></p>

<?php if (count($attachments) > 0): ?>
    <h3>Attachments:</h3>
    <ul>
        <?php foreach ($attachments as $attachment): ?>
            <li><a href="data:application/octet-stream;base64,<?php echo base64_encode($attachment['attachment']); ?>" download="<?php echo htmlspecialchars($attachment['filename']); ?>"><?php echo htmlspecialchars($attachment['filename']); ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
imap_close($inbox);
?>
