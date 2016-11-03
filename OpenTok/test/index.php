<?php
require "../vendor/autoload.php";
use OpenTok\OpenTok;

$API_KEY = '45708072';
$API_SECRET = '6e38ecb0448606d5d4ec93d862e386fde9d9c3c3';
$apiObj = new OpenTok($API_KEY, $API_SECRET);
print_r($apiObj);die;
$session = $apiObj->createSession(array('mediaMode' => MediaMode::ROUTED));
$sessionId =  $session->getSessionId();
echo $sessionId;

echo json_encode(array(
       "sessionId" => $sessionId,
    "apiKey" => $API_KEY,
    "token" => $apiObj->generateToken($sessionId)
   ));


//$opentok = new OpenTok($apiKey, $apiSecret);
?>