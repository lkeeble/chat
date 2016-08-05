<?php
include_once('Util.php');
echo "php version: " . phpversion();
echo "<br>";

$s = '["\\\"{\\\\\\\"penColor\\\\\\\":\\\\\\\"green';

// $s2 = str_replace('\"','"', $s);
// $s3 = str_replace('\\\\', "\\", $s2);

$s3 = adjustJsonEncoding($s);

echo "original: " . $s;
echo "<br>";
echo "output: " . $s3;
echo "<br>";
echo "needs to be: " . '["\"{\\\"penColor\\\":\\\"green';

?>

