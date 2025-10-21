<?php

error_reporting(0);

$_SESSION['lang'] = $_GET['l'];
$base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'];
header('Location: ' . $base_url);
