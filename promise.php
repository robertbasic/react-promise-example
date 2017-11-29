<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

require_once 'FetchStatusCodes.php';

$urls = [
    'https://example.com/',
    'https://stackoverflow.com/',
    'https://www.google.com/',
    'https://www.google.com/no-such-url',
    'https://www.google.com:81'
];

$statusCodes = new FetchStatusCodes();
$promise = $statusCodes->promise();

$secondPromise = $promise->then(
    function($statusCodes) {
        $successCodes = array_filter($statusCodes, function ($code) {
            if ($code >= 200 && $code < 300) {
                return true;
            }
            return false;
        });
        return $successCodes;
    },
    function($reason) {
        var_dump($reason);
    },
    function($progress) {
        echo "Progress: " . $progress . PHP_EOL;
    }
);

$thirdPromise = $secondPromise->then(
    function ($successCodes) {
        return json_encode($successCodes);
    }
);

$thirdPromise->done(
    function ($jsonString) {
        echo $jsonString . PHP_EOL;
    }
);

$statusCodes($urls);
