<?php 
require "OpenTok/vendor/autoload.php";
use OpenTok\OpenTok;
use OpenTok\MediaMode;
header("Access-Control-Allow-Origin: *");
$API_KEY = '45708072';
$API_SECRET = '6e38ecb0448606d5d4ec93d862e386fde9d9c3c3';

if(!empty($_GET['action']) && $_GET['action'] == 'disconnect') {
		disconnect($_GET, $API_KEY, $API_SECRET);
}
if(!empty($_GET['action']) && $_GET['action'] == 'notificationAll') {
		sendNotificationToAll($_GET, $API_KEY, $API_SECRET);
}

if(empty($_GET['sender_id']) || empty($_GET['reciver_id'])){
	echo json_encode(array(
	"status" => false,
	"message" => "Please provide the sender and reciver ids."
    ));
    exit;
}

$senderId = $_GET['sender_id'];
$reciverId = $_GET['reciver_id'];
$con = connection();
$data = $con->query("SELECT * from opentak where sender_id in(".$senderId.",".$reciverId.") OR reciver_id in(".$senderId.",".$reciverId.")");
if(!empty($data->num_rows)) {
	$row = $data->fetch_assoc();
	$to_time = strtotime(date('Y-m-d H:i:s'));
	$from_time = strtotime($row['time']);
	$timediff = round(abs($to_time - $from_time) / 60,2);
	if($timediff > 59) {
		$con->query("DELETE from opentak where sender_id in(".$senderId.",".$reciverId.") OR reciver_id in(".$senderId.",".$reciverId.")");
		echo token($senderId, $reciverId, $API_KEY, $API_SECRET, $con);
		exit;
	}
    echo json_encode(array(
	"sessionId" => $row['session_id'],
     "apiKey" => $API_KEY,
     "token" => $row['token']
    ));
    exit;
} else {
	echo token($senderId, $reciverId, $API_KEY, $API_SECRET, $con);
	exit;
}


function connection() {
	$con = mysqli_connect("localhost","root","root","opentak");
	if (mysqli_connect_errno()){
		echo json_encode(array(
			"status" => false,
			"message" => "Failed to connect to MySQL: " . mysqli_connect_error()
		));
		exit;
	}
	return $con;
}

function token($senderId, $reciverId, $API_KEY, $API_SECRET, $con) {
	$apiObj = new OpenTok($API_KEY, $API_SECRET);
	$session = $apiObj->createSession(array('mediaMode' => MediaMode::ROUTED));
	$sessionId =  $session->getSessionId();
	$connectionMetaData = "username=Abhay Raj Singh";
	//'role' => RoleConstants::PUBLISHER,  
	$parem = array('expireTime' => time()+(1 * 60 * 60), 'data' => $connectionMetaData);
	$token = $apiObj->generateToken($sessionId, $parem);
	$con->query("INSERT INTO opentak (sender_id, reciver_id, session_id, token) VALUES (".$senderId.",".$reciverId.","."'".$sessionId."'".","."'".$token."'".")");
	return json_encode(array(
		"status" => true,
		"sessionId" => $sessionId,
	     "apiKey" => $API_KEY,
	     "token" => $token
	));
}

function disconnect($parem, $API_KEY, $API_SECRET) {
	// Initialize options for REST interface
	if(empty($parem['session_id'] || empty($parem['connection_id']))) {
		echo json_encode(array(
		"status" => false,
		"message" => "Please provide the session-id and connection-id."
	    ));
    	exit;
	}

	$header = array(
		'X-TB-PARTNER-AUTH:'.$API_KEY.':'.$API_SECRET,
		'Content-Type:application/json'
	);	

    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, "http://api.opentok.com/v2/project/".$API_KEY."/session/".$parem['session_id']."/connection/".$parem['connection_id']);
    curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true );
    $responce = curl_exec ($curl);
    curl_close ($curl);
	var_dump(json_decode(($responce),true));
	exit;
}

function sendNotificationToAll($parem, $API_KEY, $API_SECRET) {
	if(empty($parem['session_id'])) {
		echo json_encode(array(
		"status" => false,
		"message" => "Please provide the session-id."
	    ));
    	exit;
	}
	$con = connection();
	$data = $con->query("SELECT * from opentak");
	if(!empty($data->num_rows)) {
		$row = $data->fetch_assoc();
		$to_time = strtotime(date('Y-m-d H:i:s'));
		$from_time = strtotime($row['time']);
		$timediff = round(abs($to_time - $from_time) / 60);
	}

	$json = json_encode(array('type'=> 'Remaining-Time', 'data' => "Remaining Time is : ".$timediff." minutes"));
	$header = array(
		'X-TB-PARTNER-AUTH:'.$API_KEY.':'.$API_SECRET,
		'Content-Type:application/json'
	);	

    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, "http://api.opentok.com/v2/project/".$API_KEY."/session/".$parem['session_id']."/signal");
    curl_setopt ($curl, CURLOPT_POST, 1);
    curl_setopt ($curl, CURLOPT_POSTFIELDS, $json);
    curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true );
    $responce = curl_exec ($curl);
    curl_close ($curl);
	var_dump(json_decode(($responce),true));
	exit;
}

?>