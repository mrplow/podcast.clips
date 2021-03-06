-- create the databases
CREATE DATABASE IF NOT EXISTS podcast_clips;

-- create the user for the database
CREATE USER 'databaseuser'@'%' IDENTIFIED BY 'changeme';
GRANT CREATE, ALTER, INDEX, LOCK TABLES, REFERENCES, UPDATE, DELETE, DROP, SELECT, INSERT ON `podcast_clips`.* TO 'databaseuser'@'%';

FLUSH PRIVILEGES;
