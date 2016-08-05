<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_GET["BoardID"];

  $pdo = getPDO();
  clearBoardData($pdo, $boardID);
  
  $retArr = array('status' => 'success');
  echo json_encode($retArr);
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function clearBoardData($pdo, $boardID) {
  $sql = "delete from segments where BoardID = ?";
  $pdo->prepare($sql)->execute(array($boardID));
}
?>

