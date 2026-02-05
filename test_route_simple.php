<?php
// Test direct sans passer par Laravel
echo "Test de redirection...\n";

// Vérifier si on peut accéder au fichier
$url = "https://yoriinapp.com/multiservices";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "URL: {$url}\n";
echo "Status Code: {$httpCode}\n";
echo "Headers:\n{$response}\n";
