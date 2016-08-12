<?php

include_once('Util.php');

header('Content-Type: application/json;charset=utf-8');

try {
  
  if (!empty($_GET["board"])) {
    $board = $_GET["board"];
  }
  else {
    $board = "all";
  }
  
  if (!empty($_GET["hourcutoff"])) { 
    $hourCutoff = $_GET["hourcutoff"];
  } else {
    $retArr = array('status' => 'error', 'message' => 'hourcutoff was empty so no action was taken');
    echo json_encode($retArr);
    return;  
  }
  
  $debug = FALSE;
  if (!empty($_GET["debug"])) {
    $debug = TRUE;
  }
  
  $pdo = getPDO();
  
  if ($board == "all") {
    trimAllData($pdo, $hourCutoff);
  } else {
    trimBoardData($pdo, $board, $hourCutoff);
  }
  
  $retArr = array('status' => 'success');
  echo json_encode($retArr);
} catch (Exception $e) {
  $retArr = array('status' => 'error', 
                  'message' => $e->getMessage());
  echo json_encode($retArr);
}

function trimBoardData($pdo, $board, $hourCutoff) {
  $sql = "delete from messages where (" . getSqlTimeSinceCreationHours() . ") > ? and  BoardID = ?";

  global $debug;
  if ($debug) {
    echo "hour cutoff: " . $hourCutoff . "\n";
    echo "board: " . $board . "\n";
    echo $sql;
    return;
  }

  $pdo->prepare($sql)->execute(array($board, $hourCutoff));
}

function trimAllData($pdo, $hourCutoff) {
  $sql = "delete from messages where (" . getSqlTimeSinceCreationHours() . ") > ? ";

  global $debug;
  if ($debug) {
    echo "hour cutoff: " . $hourCutoff . "\n";
    echo $sql;
    return;
  }

  $pdo->prepare($sql)->execute(array($hourCutoff));
}

function getSqlTimeSinceCreationHours() {
  $secondsInHour = 3600; 
  $sqlTimeSinceCreationSecs = " cast(strftime('%s',datetime('now')) as integer) - cast(strftime('%s',creationDate) as integer) ";
  $sqlTimeSinceCreationHours = " (" . $sqlTimeSinceCreationSecs . ") " . " / " . $secondsInHour ;

  return $sqlTimeSinceCreationHours;  
}
?>

