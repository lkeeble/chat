<?php
// For help with PDO, see https://phpdelusions.net/pdo

function getPDO() {
  $pdo = new PDO('sqlite:test.db');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  return $pdo;
}

function printBoardRows() {
  $pdo = getPDO();

  $sql = "select * from messages";
  $stmt = $pdo->prepare($sql);
  //$row = $stmt->fetch();
  $stmt->execute();

  $i = 0;
  foreach ($stmt as $row) {
    echo "$i ";
    printlnBoardRow($row);
    $i++;
  }
}

function printlnBoardRow($row) {
  echo "ClientID: " . $row['ClientID'] . " BoardID: " . $row['BoardID'] . " CreationDate: " . $row['CreationDate'] . " Data: " . $row['Data'];
  echo "<br>";
}

function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function fixEscaping($s) {
    // HACK - to workaround PHP 5.2 escaping double quotes (in JSON strings).
    // Suspect this is due to pre-5.4 PHP having magic_quotes_gpc enabled, gpc is "get, post, cookies", so strings containing quotes that are POSTED will be escaped.
    if (substr(phpversion(), 0, 3) == "5.2") {
      $s2 = stripslashes($s);      
        // $s2 = str_replace('\"','"', $s);
        // $s3 = str_replace('\\\\', "\\", $s2);

        return $s2;
    }
    
    return $s;
}
?>
