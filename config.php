<?php
$upload_dir = "devoirs";
$devoirs = array(
    "all" => array(
        "nom" => "Choisissez une option",
        "deadline" => "N/A",
    ),
    "intrusion-metasploitable" => array(
        "nom" => "Test d'intrusion Metasploitable",
        "deadline" => "2024-04-27 23:59:59",
    ),
    "intrusion-windows" => array(
        "nom" => "Test d'intrusion Windows",
        "deadline" => "2024-05-04 23:59:59",
    ),
    "audit-web" => array(
        "nom" => "Audit Site Web",
        "deadline" => "2024-05-04 23:59:59",
    ),
);

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$hostname = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $hostname;