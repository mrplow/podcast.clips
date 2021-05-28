# podcast.clips

## Overview
A simple web interface for podcast listeners to stream/download episodes and save timestamped segments with comments as well as search transcriptions of episodes.

Automatic transcription is powered by autosubs upon upload of new content.

---

### Goals
- [x] docker-compose backend
- [x] mariaDB tables created
  - [x] episodes
  - [x] segments
  - [x] users
  - [x] user levels (owner, moderator, normal)
  - [ ] tags
  - [ ] segment voting, thumbs up/down or star
- [x] Figuring how how to implement a php submit form within a javascript loop
- [x] Uploading episodes (owner ownly)
- [x] Searching and filtering of segments
- [x] Searching transcriptions with links to episode/timestamp
- [x] Exporting clips
  - [ ] possibly appending clips together
- [x] User management (kinda)
- [ ] Make it not ugly
- [ ] Other things

---

## Made with [The After Disaster Podcast](https://www.patreon.com/AfterDisaster) in mind.

---

### Setup Instructions
clone the repository

`git pull https://github.com/mrplow/podcast.clips.git`

`cd podcast.clips`

Edit .env_mariadb and change the root password, probably want a decent password 😉
<pre>
MYSQL_ROOT_PASSWORD=<b>654321</b>
</pre>
<br />

Edit connect.php and change the username and password variable which will be used by php
<pre>
$username = "<b>databaseuser</b>";
$password = "<b>changeme</b>";
</pre>
<br />

Edit setupdb/01-create-database-and-user.sql and change the username (in two places) and the password to match the connect.php variables
<pre>
CREATE USER '<b>databaseuser</b>'@'%' IDENTIFIED BY '<b>changeme</b>';
GRANT CREATE, ALTER, INDEX, LOCK TABLES, REFERENCES, UPDATE, DELETE, DROP, SELECT, INSERT ON `podcast_clips`.* TO '<b>databaseuser</b>'@'%';`
</pre>

start docker-compose

`docker-compose up -d`

Login to the site http://localhost:8889

The first user to create an account will be set as the owner

Optionally setup an HTTPS reverse proxy with nginx or apache (google it)
