<?php

define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/helpers/session_helper.php';
require_once ROOT_PATH . '/app/helpers/url_helper.php';
require_once ROOT_PATH . '/app/helpers/validation_helper.php';
require_once ROOT_PATH . '/app/helpers/qr_helper.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/App.php';

$app = new App();
