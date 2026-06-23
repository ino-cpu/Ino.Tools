<?php
header('Content-Type: application/json');

// Cek parameter
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter url wajib diisi']);
    exit;
}

$targetUrl = 'https://api.azbry.com' . $_GET['url'];

// Inisialisasi cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Cek error curl
if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal terhubung ke API', 'detail' => $curlError]);
    exit;
}

// Cek HTTP status
if ($httpCode !== 200) {
    http_response_code($httpCode);
    // Coba ambil pesan error dari response (bisa HTML)
    $clean = substr(strip_tags($response), 0, 300);
    echo json_encode([
        'error' => 'API mengembalikan status ' . $httpCode,
        'detail' => $clean ?: 'Tidak ada detail'
    ]);
    exit;
}

// Parse JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Response bukan JSON',
        'raw' => substr($response, 0, 500)
    ]);
    exit;
}

// ========== GANTI BRAND ==========
array_walk_recursive($data, function (&$value) {
    if (is_string($value)) {
        $value = str_ireplace('azbry', 'Ino Digital', $value);
        $value = str_ireplace('Azbry', 'Ino Digital', $value);
        $value = str_ireplace('AZBRY', 'INO DIGITAL', $value);
        $value = str_ireplace('FebryWesker', 'Ino Digital', $value);
    }
});

if (isset($data['creator'])) $data['creator'] = 'Ino Digital';
if (isset($data['source']) && stripos($data['source'], 'azbry') !== false) {
    $data['source'] = 'Ino Digital - Powered by Azbry';
}

http_response_code($httpCode);
echo json_encode($data);
?>
