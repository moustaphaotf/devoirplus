<?php
date_default_timezone_set('UTC');

$upload_dir = "devoirs";
$devoirs = array(
    "all" => array(
        "nom" => "Choisissez une option",
        "deadline" => "N/A",
    ),
    "intrusion-metasploitable" => array(
        "nom" => "Test d'intrusion Metasploitable",
        "deadline" => "2024-04-28 23:59:59",
    ),
    "intrusion-windows" => array(
        "nom" => "Test d'intrusion Windows",
        "deadline" => "2024-05-12 17:00:00",
    ),
    "audit-web" => array(
        "nom" => "Audit Site Web",
        "deadline" => "2024-05-12 17:00:00",
    ),
);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$hostname = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $hostname;

// should show links to assignment to admin or not ?
$shoulShowLink = true;