<?php

define('ROOT', __DIR__);
// Detecta automáticamente el subdirectorio: /Teatro_Punto_Partida
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

require_once ROOT . '/config/database.php';
require_once ROOT . '/routes/web.php';
