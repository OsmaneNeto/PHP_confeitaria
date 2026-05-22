<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../controllers/ControleValidadeController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new ControleValidadeController($db);
$controller->handle();
