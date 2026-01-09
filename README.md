# Seffaf Portal API

Ankara Buyuksehir Belediyesi Seffaf Portal API icin PHP client kutuphanesi.

## Kurulum

### Composer ile

```bash
composer require tka/seffaf-portal-api
```

### Manuel

`src/SeffafPortal.php` dosyasini projenize kopyalayin.

## Gereksinimler

- PHP 7.2+
- OpenSSL extension
- cURL extension

## Kullanim

### Temel Kullanim

```php
<?php
require_once 'vendor/autoload.php';

use SeffafPortal\Client;

$client = new Client();

// Giris yap
$user = $client->login('guest', 'Abb2021*');

if ($client->isLoggedIn()) {
    echo "Giris basarili!";
    echo "Token: " . $client->getToken();
}
```

### Katmanlari Listele

```php
$layers = $client->getLayers();

foreach ($layers as $layer) {
    echo $layer['name'] . "\n";
}
```

### Sifrelenmis API Istegi

```php
$response = $client->encryptedPost('/DataService/Endpoint', [
    'param1' => 'value1',
    'param2' => 'value2'
]);
```

## API Dokumantasyonu

### Metodlar

| Metod | Aciklama |
|-------|----------|
| `login($username, $password)` | Kullanici girisi yapar |
| `isLoggedIn()` | Giris durumunu kontrol eder |
| `getToken()` | JWT token dondurur |
| `getUser()` | Kullanici bilgilerini dondurur |
| `getLayers()` | Yetkili katmanlari listeler |
| `request($method, $endpoint, $body, $useAuth)` | Ham API istegi gonderir |
| `encryptedPost($endpoint, $data)` | Sifrelenmis POST istegi gonderir |

### Sifreleme

API, AES-256-CFB sifreleme kullanir:

1. `GetKey/{username}` endpoint'inden AES key ve IV alinir
2. Veriler JSON'a donusturulup sifrelenir
3. Base64 olarak encode edilir
4. API'ye string olarak gonderilir

## Lisans

MIT

## Katkida Bulunma

Pull request'ler memnuniyetle karsilanir.
