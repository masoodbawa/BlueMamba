BlueMamba
=========

BlueMamba Webmail


I started BlueMamba in 2004 for a client of mine, who was a dial up ISP (www.KVINet.com). 
The initial project was based on IlohaMail. Over a the course of a few years, I slowely modified
the base code to fit the needs of my client, whom still uses BlueMamba in 2013, and my own needs
for my clients. Changes haven't been made to the code in quite a while, though when I get some
free time I would like to revisit the project and make some massive overhauls with things I've
learned. Until then it shall remain fully functional, even though its a bit antiquated.

Basic install is create a database with webmail.sql. Then update conf/db_conf.php with the database 
login. Next change your mail servers in conf/conf.php. After that setup your http server to use
the source/ directory as the root directory. It's old code and not too dificult to setup. 
If you're having issues go back to the basics and don't over think it. I was 20-22 when I wrote
this code, so not an advanced developer.

