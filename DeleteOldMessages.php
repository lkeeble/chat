<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_GET["BoardID"];
  $oneDayAgo = gmdate("c", strtotime("-1 day"));

  $pdo = getPDO();
  deleteOldMessages($pdo, $boardID, $oneDayAgo);
  
  $retArr = array('status' => 'success');
  echo json_encode($retArr);
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function deleteOldMessages($pdo, $boardID, $staleDate) {
  $sql = "delete from messages where BoardID = ? and CreationDate < ?";
  $pdo->prepare($sql)->execute(array($boardID, $staleDate));
}
?>

