<?php

use App\Core\Session;

require "vendor/autoload.php";

$session = new Session;
// $session -> set('name', 'Justice');
// $session -> delete('name');
$session -> destroy();
echo $session->get('name');