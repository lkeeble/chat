<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  $boardID = $_GET["BoardID"];
  $pdo = getPDO();
  
  $data = getBoardMemberData($pdo, $boardID);
  $retArr = array('status' => 'success',
                  'handles' => $data);
  $j = json_encode($retArr);
  echo $j;
  // HACK to adjust for difference in encoding PHP 5.2 and 5.5. 
  // echo fixEscaping($j); 
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function getBoardMemberData($pdo, $boardID) {
  $beginDate = gmdate("c", strtotime("-10 minutes"));
  $sql = "select Handle from BoardMembers where boardid = ? and LastActivity > ? order by Handle desc";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array($boardID, $beginDate));
  $rows = $stmt->fetchAll();

  $dataArr = array();
  foreach ($rows as $row) {
    array_push($dataArr, $row['Handle']);
  }

  return $dataArr;  
}
?>
