<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_GET["BoardID"];
  $handle = $_GET["handle"];
  $clientID = NULL;
  $beginDate = NULL;
  
  if (isset($_GET["ClientID"])) {
    $clientID = $_GET["ClientID"];  
  }
  
  if (isset($_GET["BeginDate"])) {
    $beginDate = $_GET["BeginDate"];
  }
  
  $pdo = getPDO();
  
  // Keep track of who's in which board.
  insertOrUpdateBoardMember($pdo, $boardID, $clientID, $handle);

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

function insertOrUpdateBoardMember($pdo, $boardID, $clientID, $handle) {
  if (boardMemberExists($pdo, $boardID, $clientID)) {
    updateBoardMember($pdo, $boardID, $clientID, $handle);
  }
  else
  {
    insertBoardMember($pdo, $boardID, $clientID, $handle);    
  }
}

function boardMemberExists($pdo, $boardID, $clientID) {
  $sql = "select count(*) from BoardMembers where boardid = ? and clientid = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($boardID, $clientID));
  $rowCount = $stmt->fetchColumn();
  
  echo "rowcount: " . $rowCount;
  
  return $rowCount > 0;
}

function insertBoardMember($pdo, $boardID, $clientID, $handle) {
  $sql = "insert into BoardMembers (boardid, clientid, handle, lastActivity) values (?,?,?,?)";
  $stmt = $pdo->prepare($sql);
  $lastActivity = date("c"); //  http://stackoverflow.com/questions/1986586/get-current-iso8601-date-time-stamp
  $stmt->execute(array($boardID, $clientID, $handle, $lastActivity));
}

function updateBoardMember($pdo, $boardID, $clientID, $handle) {
  $sql = "update BoardMembers set handle = ?, lastActivity = ? where boardID = ? and clientID = ?";
  $stmt = $pdo->prepare($sql);
  $lastActivity = date("c"); //  http://stackoverflow.com/questions/1986586/get-current-iso8601-date-time-stamp
  $stmt->execute(array($handle, $lastActivity, $boardID, $clientID));
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
  // Add the latest message date
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
  // Add the latest message date
  if (count($rows) > 0) {
    array_push($dataArr, $row['CreationDate']);
  }
  
  return $dataArr;
}

?>
