# README #

### What is this repository for? ###
* Code files for a simple chat utility. The goal is simplicity so that people who run PHP websites can easily add a chat feature to them. Eventually if enough people run a chat server then we can get away from using Facebook messenger and other centralized chat services which do not respect privacy.

### How do I get set up? ###
* Needs a web server (e.g. apache) with PHP 5.2 or higher and the PHP sqlite3 PDO driver installed.
* To get started, do a pull, then bring up chat.php in a browser. 

### TODO
* Make it work better on mobile devices. 
* Use separate sqlite databases for each chat thread. At the moment all chats use a shared sqlite database. This doesn't scale well with many people chatting because the entire sqlite database is locked for each write.
* Notify user with beep or flash or tab icon flashing/color that there's a new message
e.g. http://heyman.info/2010/sep/30/jquery-title-alert/
* Add emoticons
* when you first connect to a chatroom, if there have been previous messages pre-fill some amount of them.


### Contribution guidelines ###

* Talk to Lou Keeble as needed about contributions.

### Who do I talk to? ###
Lou Keeble LKEEBLE@YAHOO.COM
