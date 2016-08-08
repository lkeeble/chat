<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_POST["BoardID"];
  $clientID = $_POST["ClientID"];
  $messageData = $_POST['MessageData'];
  
  // Creation date is stored in UTC. This makes date storage independent of the web server's timezone setting.
  // Makes it possible to copy databases between PHP 5.2 (prod) and PHP 5.5 (dev). 
  list($usec, $sec) = explode(" ", microtime());
  $creationDate = gmdate("Y-m-d\TH:i:s", $sec) . substr($usec, 1, 8); // . date("P", $sec);
  
  $pdo = getPDO();
  
  // POSTed data when using < 5.4 versions of PHP can have double quotes (and other chars) ESCAPED using \.
  // For more info, look up the config setting "magic_quotes_gpc". 
  // Fix this here so that we don't store escaped double quotes in the database. 
  // This makes it so that we can move databases around between PHP 5.2 (prod) and PHP 5.5 (dev).
  $messageData = fixEscaping($messageData);
  
  insertBoardData($pdo, $boardID, $clientID, $creationDate, $messageData);
  $retArr = array('status' => 'success');
  echo json_encode($retArr);
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function insertBoardData($pdo, $boardID, $clientID, $creationDate, $data) {
  $sql = "insert into messages (BoardID, ClientID, CreationDate, Data) values (?,?,?,?)";
  $pdo->prepare($sql)->execute(array($boardID,$clientID,$creationDate,$data));
}
?>
