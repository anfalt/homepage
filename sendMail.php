<?php

require_once("../../../wp-load.php");

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$mail = $_POST['mail'];
$givenName = $_POST['givenName'];
$familyName = $_POST['familyName'];
$message = $_POST['message'];
$message = nl2br($message);
$givenName = $_POST['givenName'];


if (!(filter_var($mail, FILTER_VALIDATE_EMAIL))) {
    echo "0";
    return;
}

$recieptMailSent = sendRecieptMail('info@tc1860rosenheim.de', $givenName, $familyName, $message, $mail);

$messageMailSent = sendMessageMail('info@tc1860rosenheim.de', $givenName, $familyName, $message, $mail);

if ($messageMailSent && $recieptMailSent) {

    echo "true";
}


function sendRecieptMail($tennisMail, $givenName, $familyName, $message, $mail)
{

    $mailHtml = "<html><body><p>Hallo " . $givenName  . " ,</p>";
    $mailHtml .= "<p>Vielen Dank für dein Interesse an unserem Verein.<br/>Deine Nachricht wird baldmöglichst beantwortet!</p><br/>";
    $mailHtml .= "<p>Sportliche Grüße,<br/><br/>TC 1860 Rosenheim </p><br/><br/><br/>";
    $mailHtml .= "<p>Name:" . $givenName . "," . $familyName . "<br/>";
    $mailHtml .= "Nachricht:" . $message . "</p></body></html>";

    $headers = "From: " . $tennisMail   . "\r\n";
    $headers .= "BCC: andreas.faltermaier@gmail.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


    $success = wp_mail($mail, 'Kontaktanfrage TC 1860 Rosenheim', $mailHtml, $headers);
    return $success == "1";
}

function sendMessageMail($tennisMail, $givenName, $familyName, $message, $mail)
{

    $mailHtml = "<html><body><p>Neue Kontaktanfrage von Homepage</p>";
    $mailHtml .= "<p>Name:" . $givenName . " " . $familyName . "<br/>";
    $mailHtml .= "<p>Nachricht:<br/>" . $message . "</p>";
    $mailHtml .= "<p>Mail: " . $mail . "</p></body></html>";

    $headers = "From: " . $mail   . "\r\n";
    $headers .= "BCC: andreas.faltermaier@gmail.com\r\n";
    $headers .= "Reply-To: " . $mail . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


    $success = wp_mail($tennisMail, 'Neue Kontaktanfrage Homepage', $mailHtml, $headers);
    return $success == "1";
}
