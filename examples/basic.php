<?php
/**
 * Temel Kullanim Ornegi
 */

require_once __DIR__ . '/../src/SeffafPortal.php';

use SeffafPortal\Client;

$client = new Client();

// Giris yap
$user = $client->login('guest', 'Abb2021*');

if ($user) {
    echo "Giris basarili!\n";
    echo "Kullanici: " . $user['Name'] . " " . $user['LastName'] . "\n";
    echo "Organizasyon: " . $user['OrganizationName'] . "\n\n";

    // Katmanlari listele
    echo "Yetkili Katmanlar:\n";
    foreach ($client->getLayers() as $layer) {
        echo "  - " . $layer['name'] . " (" . $layer['path'] . ")\n";
    }
} else {
    echo "Giris basarisiz!\n";
}
