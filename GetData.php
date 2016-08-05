<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_GET["BoardID"];
  $clientID = NULL;
  $beginDate = NULL;
  
  if (isset($_GET["ClientID"])) {
    $clientID = $_GET["ClientID"];  
  }
  
  if (isset($_GET["BeginDate"])) {
    $beginDate = $_GET["BeginDate"];
  }
  
  $pdo = getPDO();
  if ($clientID === NULL) {
    $data = getAllMessages($pdo, $boardID);
  } else {
    $data = getMessagesFromOtherClients($pdo, $boardID, $clientID, $beginDate);  
  }
  
  $retArr = array('status' => 'success',
                  'messages' => $data);
  $j = json_encode($retArr);
  echo $j;
  // HACK to adjust for difference in encoding PHP 5.2 and 5.5. 
  // echo fixEscaping($j); 
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function getMessagesFromOtherClients($pdo, $boardID, $clientID, $beginDate) {
  $sql = "select data, creationDate from messages where boardID = ? and clientID <> ? and creationDate > ? order by creationDate";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($boardID, $clientID, $beginDate));
  $rows = $stmt->fetchAll();
  
  $dataArr = array();
  foreach ($rows as $row) {
    array_push($dataArr, $row['Data']);
  }
  // Add the latest segment date
  if (count($rows) > 0) {
    array_push($dataArr, $row['CreationDate']);
  }
  
  return $dataArr;
}

function getAllMessages($pdo, $boardID) {
  $sql = "select data, CreationDate from messages where boardID = ? order by creationDate";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($boardID));
  $rows = $stmt->fetchAll();
  
  $dataArr = array();
  foreach ($rows as $row) {
    array_push($dataArr, $row['Data']);
  }
  // Add the latest segment date
  if (count($rows) > 0) {
    array_push($dataArr, $row['CreationDate']);
  }
  
  return $dataArr;
}

?>
