<?php

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'chat_V3'); // Change to the version that you want but latest is recommended
define('DB_USER', $_ENV['DB_USER'] ?? ''); // Your database username
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? ''); // Your database password
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
