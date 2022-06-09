<?php

namespace Ssl\App;

use Ssl\Helper\Tasks as Tasks;
use DateTime;

require_once 'bootstrap.php';

$task = new Tasks();
$urls = explode(',', $_ENV['DOMAINS']);
$now = new DateTime();


foreach ($urls as $url) {
    $orignal_parse = parse_url("https://" . $url, PHP_URL_HOST);
    $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
    $read = stream_socket_client("ssl://" . $orignal_parse . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
    $cert = stream_context_get_params($read);
    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

    $valid_to = new DateTime(date(DATE_RFC2822, $certinfo['validTo_time_t']));
    $expiring_in_days = $valid_to->diff($now)->days;

    if ($expiring_in_days < $_ENV["EXPIRY_NOTIFICATION_DAYS"]) {
        $answer[] = array($url, $valid_to, $expiring_in_days);
    }
}

$emailData = "<h3>Expiring SSL Domains</h3><table style='border: 1px solid;border-collapse: collapse;border-color:#96D4D4;'><thead><tr><th style='border: 1px solid;border-collapse: collapse;border-color:#96D4D4;'>Sr.No.</th><th style='border: 1px solid;border-collapse: collapse;border-color:#96D4D4;'>Domain Name</th><th style='border: 1px solid;border-collapse: collapse;border-color:#96D4D4;' >Expiry Date</th><th style='border: 1px solid;border-collapse: collapse;border-color:#96D4D4;'>Days Left</th></tr></thead><tbody>";

$countOfDomainExpiry = count($answer);
if (empty($answer)) {
    $task->log("All Good: No SSL is expiring");
} else {
    $count = 1;
    foreach ($answer as $domain) {
        $expiryDate = $domain[1]->format('d-m-Y H:i:s');
        $emailData .= "<tr><td>$count</td><td>$domain[0]</td><td>$expiryDate</td><td>$domain[2]</td></tr>";
        $count = $count + 1;
    }
    $emailData .= "</tbody></table>";
    $task->sendEmails("Urgent: " . $countOfDomainExpiry . " SSL Expiring", $emailData);
}

