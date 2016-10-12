<!DOCTYPE html>
<?php
  session_start(); // repeatedly calling this should be OK i.e. we should keep the same session.
  $board = "TestBoard123";
  $handle = "John Doe";
  if (isset($_POST["board"])) {
    $board = $_POST["board"];
  } elseif (isset($_GET["board"])) {
    $board = $_GET["board"];
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
  var $boardGroup = $('#boardGroup');
  var $yourHandleGroup = $('#yourHandleGroup');
  var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
  // green, blue, black, red, orange, purple, fuchsia, brown, grey, tan
  var handleColors = ["#00ff00", "#0000ff", "#000000", "#ff0000", "#e67e22", "#8e44ad", "#ff0080", "#8B4513", "#808080", "#D2B48C"];
  var promptMessage = '1. Enter a message board name above and click "Go". \n2. Pick a nickname \n3. Start typing here! Press Enter to post your message.';

  function log(msg) {
    if (debug) {
      console.log(msg);
    }
  }
    
  function linkify(text) {
      var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
      return text.replace(urlRegex, function(url) {
          return '<a href="' + url + '" target="_blank">' + url + '</a>';
      });
  }

  function getHashCode(str) {
    var hash = 0, i, chr, len;
    if (str.length === 0) return hash;
    for (i = 0, len = str.length; i < len; i++) {
      chr   = str.charCodeAt(i);
      hash  = ((hash << 5) - hash) + chr;
      hash |= 0; // Convert to 32bit integer
    }
    return hash;
  };
  
  function getColorIndex(str) {
    return getHashCode(str) % 10;
  }
  
  function getHexColor(str) {
    return handleColors[getColorIndex(str)];
  }
    
  function initChat() {
    setMessagesHeight();
    initPromptMessage();
    getAllMessages();
    var nowISO = (new Date()).toISOString(); 
    otherClientMessageLatestDate = nowISO;
    var reloadOtherClientInterval = setInterval(getMessagesFromOtherClients, 1000);
    $chatTextarea.focus();
  }
  
  function setMessagesHeight() {
    $messagesDiv.height($(window).height() - $boardGroup.height() - $yourHandleGroup.height() - $chatTextarea.height() - 100);
  }
  
  $(window).resize(function() {
    setMessagesHeight();
  })
  
  function initPromptMessage() {
    $chatTextarea.val(promptMessage);
    $chatTextarea.addClass('prompt-text');
  }
  
  $chatTextarea.keypress(function(e) {
    var $this = $(this);
    if ($this.val() == promptMessage) {
      clearPrompt();      
    }
    
    if (e.which == 13) {
      var text = $chatTextarea.val();
      var handle = $handle.val();
      var message = Message.createChatMessage(handle, text);
      
      sendMessage(message);
      appendMessage(message);
      scrollDown();
      clearChatWindow();
      $chatTextarea.focus();
      
      // Stop newline from going into the textarea when the user hits enter.
      e.preventDefault();
    }
  });
  
  function clearPrompt() {
    $chatTextarea.val('');
    $chatTextarea.removeClass('prompt-text');
    $chatTextarea.addClass('message-text');
  }
  
  function clearChatWindow() {
      $chatTextarea.val('');
  }
  
  $('#btnClear').click(function(e) {
    clearAllMessagesOnServer();
    clearAllMessagesOnClient();
  });
  
  $('#btnSetBoard').click(function (evt) {
    submitFormWithBoardName();         
  });
    
  $('#boardID').keypress(function(e) {
      if (e.which == 13) {
        submitFormWithBoardName();         
        return false; <?php // prevent double submit ?>  
      }
  });
  
  function submitFormWithBoardName() {
    var mainForm = $('#mainForm');
    mainForm.attr("action", "chat.php?board=" + $('#boardID').val());
    mainForm.submit();
  }

  function getAllMessages() {
      $.ajax({
        type: "GET",
        url: "GetMessageData.php",
        dataType: "json",
        data: {BoardID : $('#boardID').val(), handle : $handle.val()},
        success : getAllMessagesSuccess,
        error : getAllMessagesError
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
        clearAllMessagesOnClient();
      }
      else if (action == "chat") {
        appendMessage(messagesArr[i])          
      }        
    }
    
    scrollDown();
  }
  
  function getMessagesError(jqXHR, status, error) {
    alert('ajax getMessagesFromOtherClients() call failed, status: ' + status);
  }
  
  function getMessagesFromOtherClients() {
    $.ajax({
    type: "GET",
    url: "GetMessageData.php",
    dataType: "json",
    data: {BoardID : $('#boardID').val(), ClientID : $('#clientID').val(), handle : $handle.val(), BeginDate : otherClientMessageLatestDate},
    success : getMessagesSuccess,
    error : getMessagesError
    });
  }
  
  function getAllMessagesSuccess(data, status) {
    getMessagesSuccess(data, status);
  }
  
  function getAllMessagesError() {
    alert('ajax getAllMessages() call failed, status: ' + status);
  }
  
  function fromISODateStrToLocalDateStr(isoDateStr) {
    var d = new Date(isoDateStr);
    return d.getMonth() + 1 + "/" + d.getDate() + "/" + d.getFullYear() + " " + forceTwoDigits(d.getHours()) + ":" + forceTwoDigits(d.getMinutes()) + ":" + forceTwoDigits(d.getSeconds());       
  }
  
  function forceTwoDigits(i)
  {
    if (i < 10) {
      return "0" + i.toString();
    }
    
    return i.toString();
  }
  
  function testConvertDate() {
    var convertedData = fromISODateStrToLocalDateStr('2016-08-09T16:42:50.221Z');
    var i = 0;
  }
  
  function appendMessage(message) {
    var text = message.text;
    text = linkify(text);
    var handle = message.handle;
    var messageDate = message.nowISO;
    var messageDateLocal = fromISODateStrToLocalDateStr(messageDate);
    var messageText = '(' + messageDateLocal + ') <span class="handle">' + message.handle + '</span>: ' + text;
    var $messageDiv = $('<div>' + messageText + '</div><br>');
    $messageDiv.find(".handle").css('color', getHexColor(handle));
    $messagesDiv.append($messageDiv);
  }
  
  function clearAllMessagesOnClient() {
    $messagesDiv.empty();
  }
    
  function submitFormWithBoardName() {
    var mainForm = $('#mainForm');
    mainForm.attr("action", "chat.php?board=" + $('#boardID').val());
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
      <div id="messagesDiv"></div>

      <div id="boardGroup">
        <strong>Board:</strong>  
        <input type="text" id="boardID" name="board" value="<?php echo $board;?>">
        <button type="button" id="btnSetBoard">Go</button> <?php // type="button" makes the button not submit the form ?>
        <button type="button" id="btnClear">Clear all messages</button>  
        <input type="hidden" id="clientID" name="ClientID" value="<?php echo session_id(); ?>"></input>
      </div>
    
      <div id="yourHandleGroup">
        <strong>Nickname:</strong>
        <input type="text" id="handle" name="handle" value="<?php echo $handle;?>"></input>
      </div>    
      <textarea rows="3" id="chatTextarea"></textarea>
    </form>
  </body>
</html>
