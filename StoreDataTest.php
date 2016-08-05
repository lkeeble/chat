<html>
<body>
<?php
session_start();
echo "session id: " . session_id() . "<br>";
?>
<form action="StoreData.php" method="POST">
  <table>
    <tr>
      <td>Board ID:</td>
      <td><input type="text" name="BoardID"></td>
    </tr>
    <tr>
      <td>Client ID:</td>
      <td><input type="text" name="ClientID" value="<?php echo session_id() ?>" size="30"></td>
    </tr>
    <tr>
      <td>Data:</td>
      <td><input type="text" name="SegmentData"></td>
    </tr>
    <tr>
      <td colspan="2"><input type="submit" value="Submit"></td>
    </tr>
  </table>
    
</form>
</body>
</html>