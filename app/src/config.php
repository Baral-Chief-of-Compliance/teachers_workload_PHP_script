<?
// берем конфигурации для подключения к базе данных из виртуального окружения
// docker контейнера php
$host = getenv('MYSQL_HOST');
$port = getenv('MYSQL_PORT');
$login = getenv('MYSQL_LOGIN');
$pass = getenv('MYSQL_PASS');
$db = getenv('MYSQL_DB');
$mysqli = mysqli_connect($host, $login, $pass, $db, $port) or die(mysqli_error()); 
?>