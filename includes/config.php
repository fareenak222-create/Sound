<?php
// ---- Database credentials (edit for your server; XAMPP defaults shown) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'sound_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Upload limits (bytes). Also raise upload_max_filesize/post_max_size in php.ini for large videos ----
define('MAX_IMAGE_SIZE', 3 * 1024 * 1024);
define('MAX_AUDIO_SIZE', 25 * 1024 * 1024);
define('MAX_VIDEO_SIZE', 200 * 1024 * 1024);

// Set to true only while debugging
define('APP_DEBUG', false);
