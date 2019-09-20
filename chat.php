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
	  <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Chat</title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
      <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
      <link rel="stylesheet" href="chat.css?v=<?php echo time();?>">
      <link rel='shortcut icon' href='favicon.ico' type='image/x-icon'/ >
<script>
  $(chat);

  function chat()	{
  var debug = true;
  var $messagesDiv = $('#messagesDiv');
  var $boardMembersDiv = $('#boardMembersDiv');
  var $chatTextarea = $('#chatTextarea');
  var $handle = $('#handle');
  var $boardGroup = $('#boardGroup');
  var $yourHandleGroup = $('#yourHandleGroup');
  var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
  // green, blue, black, red, orange, purple, fuchsia, brown, grey, tan
  var handleColors = ["#00ff00", "#0000ff", "#000000", "#ff0000", "#e67e22", "#8e44ad", "#ff0080", "#8B4513", "#808080", "#D2B48C"];
  var promptMessage = 'Type here, press <Enter> to post your message.';

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
  
  // Icons from Icons8.com.
  function emojify(text) {
    var happyRegex = /:\)/g;
    text = text.replace(happyRegex, function(textEmoticon) {
     return '<img src="Happy_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });
    
    var sadRegex = /:\(/g;
    text = text.replace(sadRegex, function(textEmoticon) {
     return '<img src="Sad_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });
    
    var lolRegex = /:D/g;
    text = text.replace(lolRegex, function(textEmoticon) {
     return '<img src="LOL_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });

    var neutralRegex = /:\|/g;
    text = text.replace(neutralRegex, function(textEmoticon) {
     return '<img src="Neutral_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });

    var winkRegex = /;\)/g;
    text = text.replace(winkRegex, function(textEmoticon) {
     return '<img src="Wink_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });

    var tongueOutRegex = /:p/g;
    text = text.replace(tongueOutRegex, function(textEmoticon) {
     return '<img src="TongueOut_48px.png" height="24" width="24" style="vertical-align:text-bottom;" ></img>';  
    });

    return text;
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
    // setMessagesHeight();
    initPromptMessage();
    getAllMessages();
    var nowISO = (new Date()).toISOString(); 
    otherClientMessageLatestDate = nowISO;
    var reloadOtherClientInterval = setInterval(getMessagesFromOtherClients, 1000);
    deleteOldMessages();
    getBoardMemberData();
    var reloadBoardMemberInterval = setInterval(getBoardMemberData, 5000);
    $chatTextarea.focus();
  }
  
  // function setMessagesHeight() {
  //   // return;
  //   var messagesHeight = $(window).height() - $boardGroup.height() - $yourHandleGroup.height() - $chatTextarea.height() - 100;
  //   var boardMembersHeight = messagesHeight - $('#peopleInRoomTd').height(); 
  //   //$("#topTable").height(height);
  //   $messagesDiv.height(messagesHeight);
  //   $('#boardMembersTable').height(messagesHeight);
  //   $('#boardMembersDiv').height(boardMembersHeight);
  //   scrollDown();
  // }
  
  // $(window).resize(function() {
  //   setMessagesHeight();
  // })
  
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
  
  function deleteOldMessages() {
      $.ajax({
        type: "GET",
        url: "DeleteOldMessages.php",
        dataType: "json",
        data: {BoardID : $('#boardID').val()},
        success : deleteOldMessagesSuccess,
        error : deleteOldMessagesError
      });
  }
  
  function deleteOldMessagesSuccess(data, status) {
  }
  
  function deleteOldMessagesError(jqXHR, status, error) {
    alert('ajax deleteOldMessages() call failed, status: ' + status);    
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
    // Last element in the array is not a message, it's the creation date of the final message.
    // That's why we loop until less than messagesStrArr.length -1.
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
  
  function getBoardMemberDataSuccess(data, status) {
    var handlesArr = data.handles;
    
    $boardMembersDiv.empty();   
    for (var i = 0; i < handlesArr.length; i++) {
      appendBoardMember(handlesArr[i]);      
    }
    $('#peopleInRoom').text(handlesArr.length);
  }
  
  function appendBoardMember(handleStr) {
    var $handleDiv = $('<div><span class="handle">' + handleStr + '</span></div><br>');
    $handleDiv.find(".handle").css('color', getHexColor(handleStr));
    $boardMembersDiv.append($handleDiv);
  }
  
  function getBoardMemberDataError(jqXHR, status, error) {
    alert('ajax getBoardMemberData() call failed, status: ' + status);
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
  
  function getBoardMemberData() {
    $.ajax({
    type: "GET",
    url: "GetBoardMemberData.php",
    dataType: "json",
    data: {BoardID : $('#boardID').val()},
    success : getBoardMemberDataSuccess,
    error : getBoardMemberDataError
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
  
  function fromISODateStrToLocalTimeStr(isoDateStr) {
    var d = new Date(isoDateStr);
    return forceTwoDigits(d.getHours()) + ":" + forceTwoDigits(d.getMinutes()) + ":" + forceTwoDigits(d.getSeconds());       
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
    text = emojify(text);
    var handle = message.handle;
    var messageDate = message.nowISO;
    var messageDateLocal = fromISODateStrToLocalTimeStr(messageDate);
    var messageText = '(<span class="messageDate">' + messageDateLocal + '</span>) <span class="handle">' + message.handle + '</span>: ' + text;
    var $messageDiv = $('<div class="messageDiv">' + messageText + '</div>');
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

      <div id="yourHandleGroup">
        <span style="font-size: 11pt;">Your Nickname:</span>
        <input type="text" id="handle" name="handle" value="<?php echo $handle;?>" style="width: 150px;"></input>
      </div>    

      <div id="boardGroup">
        <span style="font-size: 11pt;">Your Chatroom:</span>  
        <input type="text" id="boardID" name="board" value="<?php echo $board;?>" style="width: 150px;">
        <button type="button" id="btnSetBoard">Go</button> <?php // type="button" makes the button not submit the form ?>
        <input type="hidden" id="clientID" name="ClientID" value="<?php echo session_id(); ?>"></input>
      </div>
    
      <table id="topTable" width="100%" border="0px">
        <tr>
          <td style="width:90%;min-width: 200px;">
            <strong>Messages for Chatroom "<?php echo $board; ?>"</strong>
          </td>
          <td>
            <strong>Occupants</strong>
          </td>
        </tr>
        <tr>
          <td>
            <div id="messagesDiv"></div>
          </td>
          <td>
            <div id="boardMembersDiv"></div></td></tr>
          </td>
        </tr>
        <tr>
          <td colspan="3"><textarea rows="3" id="chatTextarea" style="min-width: 240px; width: 96%; max-width: 700px;"></textarea></td>
        </tr>
        <tr>
          <td><button type="button" id="btnClear" style="margin-left: 3px;">Clear ALL Messages</button></td>
        </tr>
      </table>

      
      <div id="icons8Link" style="font-size: 8pt; padding-bottom: 10px; padding-top: 10px; margin-left: 10px;">
      <a href="https://icons8.com/android-L">Free icons by Icons8</a>
      </div>
    
    </form>
    
  </body>
</html>
