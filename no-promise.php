<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

$urls = [
    'https://example.com/',
    'https://stackoverflow.com/',
    'https://www.google.com/',
    'https://www.google.com/no-such-url',
    'https://www.google.com:81'
];

$multiHandle = curl_multi_init();
$handles = [];

foreach ($urls as $url) {
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_NOBODY, 1);
    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);

    $handles[] = $handle;
    curl_multi_add_handle($multiHandle, $handle);
}

do {
    curl_multi_exec($multiHandle, $active);
    curl_multi_select($multiHandle);
} while ($active);

$statusCodes = [];

foreach ($handles as $handle) {
    $result = curl_getinfo($handle);

    $statusCodes[$result['url']] = $result['http_code'];
}

curl_multi_close($multiHandle);

print_r($statusCodes);
