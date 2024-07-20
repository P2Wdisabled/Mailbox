<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['imap_password'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$imap_password = $_SESSION['imap_password'];

// Se connecter au serveur IMAP
$imap_host = ''; //adresse du serveur imap
$imap_port = 993;
$imap_user = $email;

$inbox = imap_open("{{$imap_host}:$imap_port/imap/ssl}INBOX", $imap_user, $imap_password);

if (!$inbox) {
    die('Cannot connect to mail server: ' . imap_last_error());
}

$emails = imap_search($inbox, 'ALL');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mailbox</title>
    <style>
        .popup {
            display: none;
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 30%;
            max-width: 400px;
            height: 50%;
            border: 1px solid #888;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            background-color: white;
            overflow: auto;
            z-index: 1000;
        }
        .popup-content {
            padding: 10px;
        }
        .popup-close {
            position: absolute;
            top: 5px;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h2>Mailbox</h2>
    <ul>
        <?php
        if ($emails) {
            rsort($emails);
            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
                $subject = isset($overview->subject) ? imap_mime_header_decode($overview->subject) : 'No subject';
                $decoded_subject = '';
                foreach ($subject as $part) {
                    $decoded_subject .= $part->text;
                }
                echo '<li><a href="#" class="email-link" data-email-number="' . $email_number . '"><strong>' . htmlspecialchars($decoded_subject) . '</strong> - ' . htmlspecialchars($overview->from) . ' on ' . htmlspecialchars($overview->date) . '</a></li>';
            }
        } else {
            echo '<li>No emails found.</li>';
        }
        ?>
    </ul>

    <div id="popup" class="popup">
        <div class="popup-content">
            <span id="popup-close" class="popup-close">&times;</span>
            <div id="popup-body"></div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.email-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const emailNumber = this.dataset.emailNumber;

                fetch('view_email.php?email_number=' + emailNumber)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('popup-body').innerHTML = data;
                        document.getElementById('popup').style.display = 'block';
                    });
            });
        });

        document.getElementById('popup-close').addEventListener('click', function() {
            document.getElementById('popup').style.display = 'none';
        });
    </script>
</body>
</html>

<?php
imap_close($inbox);
?>
