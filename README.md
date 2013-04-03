SnapChat.org
============

1. Requirements
The SnapChat website is built on CodeIgniter 2.1.3 (http://ellislab.com/codeigniter) and requires PHP 5.1.6 or newer with sockets enabled (see http://www.php.net/manual/en/sockets.installation.php for more information).

2. Setup
The website and server are separated from each other with a common folder to share certain settings.
The site may be handled by your choice of web server, SnapChat.org is served using lighttpd (http://www.lighttpd.net/).
The socket servers are ran via command line using two primary scripts, startDaemon and startRoom, both under the server folder. startDaemon is used to run a central service that launches new rooms on demand, but individual rooms can be started by using startRoom.

3. Work In Progress
SnapChat.org is a work in progress and has been for over 3 years now. If there is a lack of information or feature requests, please use the bug/feature/question form located at http://snapchat.org/bugs.
