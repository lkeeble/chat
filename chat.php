<!DOCTYPE html>
<?php
  session_start(); // repeatedly calling this should be OK i.e. we should keep the same session.
  $boardName = "Test board";
  $handle = "Test handle";
  if (isset($_POST["boardname"])) {
    $boardName = $_POST["boardname"];
  } elseif (isset($_GET["boardname"])) {
    $boardName = $_GET["boardname"];
  }
      
  if (isset($_POST["handle"])) {
    $handle = $_POST["handle"];
  } elseif (isset($_GET["handle"])) {
    $handle = $_GET["handle"];
  }

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
    var $messagesDiv = $('#messagesDiv');
    var $chatTextarea = $('#chatTextarea');
    var $handle = $('#handle');

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
      $chatTextarea.focus();
    }
    
    $chatTextarea.keypress(function(e) {
      if (e.which == 13) {
        var text = $chatTextarea.val();
        var handle = $handle.val();
        var message = Message.createChatMessage(handle, text);
        
        sendMessage(message);
        appendMessage(message);
        scrollDown();
        clearChatWindow();
        $chatTextarea.focus();
      }
    });
    
    function clearChatWindow() {
        $chatTextarea.val('');
    }
    
    $('#btnClear').click(function(e) {
      clearAllMessagesOnServer();
      clearAllMessagesOnClient();
    });

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
    
    function clearAllMessagesOnServer() {
      // Clear the database records for this board.
      $.ajax({
        type: "GET",
        url: "ClearData.php",
        dataType: "json",
        data: {BoardID : $('#boardID').val()},
        success : clearAllMessagesOnServerSuccess,
        error : clearMessagesError
      });
    }
    
    function clearAllMessagesOnServerSuccess() {
      sendClearMessage();
    }
      
    function sendClearMessage() {
        // Send message so that all other clients can be cleared as well.
        var message = Message.createClearMessage();
        sendMessage(message);
    }
    
    function clearMessagesError() {
              alert('ajax clearAllMessagesOnServer() call failed, status: ' + status);
    }
    
    function getMessagesSuccess(data, status) {
      var messagesStrArr = data.messages; // array of strings containing JSON for the messages
      if (messagesStrArr.length == 0) {
        return;
      }
      
      var messagesArr = [];
      for (var i = 0; i < messagesStrArr.length - 1; i++) {
        var message = JSON.parse(messagesStrArr[i]);
        messagesArr.push(message);
      }
      
      // The creation date of the final message is tacked onto the data. 
      otherClientMessageLatestDate = messagesStrArr[messagesStrArr.length - 1];
      
      for (var i = 0; i < messagesArr.length; i++) {
        var message = messagesArr[i];
        
        var action = messagesArr[i].action;
        if (action == "clear") {
          clearAllMessagesOnServer();
        }
        else if (action == "chat") {
          appendMessage(messagesArr[i])          
        }        
      }
      
      scrollDown();
    }
    
    function appendMessage(message) {
      var text = message.text;
      var handle = message.handle;
      var messageDate = message.nowISO;
      var messageText = '(' + message.nowISO + ') ' + message.handle + ':' + text;
      var $messageDiv = $('<div>' + messageText + '</div><br>');
      $messagesDiv.append($messageDiv);
    }
    
    function clearAllMessagesOnClient() {
      $messagesDiv.empty();
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
    
    function sendMessageData(boardID, clientID, message) {
      $.ajax({
        type: "POST",
        url: "StoreData.php",
        data: {BoardID : boardID, ClientID : clientID, MessageData : JSON.stringify(message)},
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
    
    function scrollDown() { 
      $messagesDiv.scrollTop($messagesDiv[0].scrollHeight);
    }
    
    function Message() {
    }

    Message.createChatMessage = function(handle, text) {
      var s = new Message();
      s.action = "chat";
      s.handle = handle;
      s.text = text;
      var nowISO = (new Date()).toISOString();
      s.nowISO = nowISO;
      
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
        <strong>Handle:</strong>
        <input type="text" id="handle" name="handle" value="<?php echo $handle;?>"></input>
        <button type="button" id="btnClear">Clear</button>  
        <input type="hidden" id="clientID" name="ClientID" value="<?php echo session_id(); ?>"></input>
      </div>
    </form>
    
    <div id="messagesDiv">
      <div id="message1">
        a test Message
      </div><br>
    </div>
    <div id="spacer">&nbsp;</div>
    <textarea rows="3" id="chatTextarea"></textarea>

  </body>
</html>
