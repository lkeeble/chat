<!DOCTYPE html>
<?php
  session_start(); // repeatedly calling this should be OK i.e. we should keep the same session.
  $boardName = "Test board";
  if (isset($_POST["boardname"])) {
    $boardName = $_POST["boardname"];
  } elseif (isset($_GET["boardname"])) {
    $boardName = $_GET["boardname"];
  }
      
  if ($_GET)
?>

<html>
  <head>
      <meta http-equiv="Content-type" content="text/html; charset=utf-8">
      <title>Chat</title>
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
      <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
      <link rel="stylesheet" href="chat.css?v=2">
      <link rel='shortcut icon' href='favicon.ico' type='image/x-icon'/ >
<script>
    $(chat);

function chat()	{
    var debug = true;

    function log(msg) {
      if (debug) {
        console.log(msg);
      }
    }
    
    function initChat() {
      
      getAllMessages();
      var nowISO = (new Date()).toISOString(); 
      otherClientMessageLatestDate = nowISO;
      var reloadOtherClientInterval = setInterval(getMessagesFromOtherClients, 1000);
    }

    function getAllMessages() {
        $.ajax({
          type: "GET",
          url: "GetData.php",
          dataType: "json",
          data: {BoardID : $('#boardID').val()},
          success : getMessagesSuccess,
          error : getMessagesError
        });
    }
    
    function clearAllMessages() {
      // Clear the database records for this board.
      $.ajax({
        type: "GET",
        url: "ClearData.php",
        dataType: "json",
        data: {BoardID : $('#boardID').val()},
        success : clearMessagesSuccess,
        error : clearMessagesError
      });
    }
    
    function clearMessagesSuccess() {
      sendClearMessage();
    }
      
    function sendClearMessage() {
        // Send message so that all other clients can be cleared as well.
        segment = Message.createClearMessage();
        sendMessage(segment);
    }
    
    function clearMessagesError() {
              alert('ajax clearAllMessages() call failed, status: ' + status);
    }
    
    function getMessagesSuccess(data, status) {
      var messagesStrArr = data.messages; // array of strings containing JSON for the segment.
      if (messagesStrArr.length == 0) {
        return;
      }
      
      var messagesArr = [];
      for (var i = 0; i < messagesStrArr.length - 1; i++) {
        var segment = JSON.parse(messagesStrArr[i]);
        messagesArr.push(segment);
      }
      
      // The creation date of the final segment is tacked onto the data. 
      otherClientMessageLatestDate = messagesStrArr[messagesStrArr.length - 1];
      
      for (var i = 0; i < messagesArr.length; i++) {
        var segment = messagesArr[i];
        
        var action = messagesArr[i].action;
        if (action == "clear") {
          clearCanvas();
        }
        else if (action == "send") {
          appendMessage(messagesArr[i].message)          
        }        
      }
    }
      
    function getMessagesError(jqXHR, status, error) {
      alert('ajax getMessagesFromOtherClients() call failed, status: ' + status);
    }
    
    function getMessagesFromOtherClients() {
      $.ajax({
      type: "GET",
      url: "GetData.php",
      dataType: "json",
      data: {BoardID : $('#boardID').val(), ClientID : $('#clientID').val(), BeginDate : otherClientMessageLatestDate},
      success : getMessagesSuccess,
      error : getMessagesError
      });
    }
      
    function submitFormWithBoardName() {
      var mainForm = $('#mainForm');
      mainForm.attr("action", "chat.php?boardname=" + $('#boardID').val());
      mainForm.submit();
    }
    
    function sendMessage(message) {
        var boardID = $('#boardID').val();
        var clientID = $('#clientID').val(); // replace with <?php session_id() ?> when done debugging.

        sendMessageData(boardID, clientID, message);
    }
    
    function clearCanvas() {
      context.clearRect(0, 0, canvas.width, canvas.height);
    }
    
    function sendMessageData(boardID, clientID, segment) {
      $.ajax({
        type: "POST",
        url: "StoreData.php",
        data: {BoardID : boardID, ClientID : clientID, MessageData : JSON.stringify(segment)},
        success : sendMessageSuccess,
        error : sendMessageError
      });
    }
    
    function sendMessageSuccess() {
      console.log("sendMessageData success.");
    }
    
    function sendMessageError(xhr, ajaxOptions, thrownError) {
      alert("sendMessageData error: " + thrownError);
    }
    
    function clearToolbarHighlights() {
      $('#toolbar td').removeClass("selected");
    }
    
    // See http://stackoverflow.com/questions/5767325/remove-a-particular-element-from-an-array-in-javascript.
    // Note: IE 8 and below don't support indexOf, see the above link for a polyfill if needed.
    function removeItem(item) {
      var index = items.indexOf(item);
      if (index > -1) {
        items.splice(index, 1);
      }
    }
    
    function Message() {
    }

    Message.createChatMessage = function(text) {
      var s = new Message();
      s.action = "chat";
      s.text = text;
      
      return s; 
    }
    
    Message.createClearMessage = function() {
      var s = new Message();
      s.action = "clear";
      
      return s;
    }
    
    initChat();
  }
    </script>
  </head>

  <body>
    <form id="mainForm" method="post" action="scribble.php">
      <div id="topToolbar">
        <strong>Board Name:</strong>  
        <input type="text" id="boardID" name="boardname" value="<?php echo $boardName;?>">  
        <button type="button" id="btnSetBoardName">Go</button> <?php // type="button" makes the button not submit the form ?>
        <input type="hidden" id="clientID" name="ClientID" value="<?php echo session_id(); ?>"></input>
      </div>
    </form>

    <textarea id="messagesTextarea">Test test test test test test test test
      Test test test test test test test test Test test test test test test test test
      Test test test test test test test test
      Test test test test test test test test
      Test test test test test test test test
      Test test test test test test test test
    </textarea>
    <div id="spacer">&nbsp;</div>
    <textarea rows="3" cols="80" id="chatTextarea"></textarea>

  </body>
</html>
