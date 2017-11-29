<?php declare(strict_types=1);

use React\Promise\Deferred;

class FetchStatusCodes extends Deferred
{
    public function __invoke(array $urls)
    {
        $multiHandle = curl_multi_init();

        $handles = $this->getHandlesForUrls($urls, $multiHandle);

        $this->executeMultiHandle($multiHandle);

        $statusCodes = $this->getStatusCodes($handles);

        curl_multi_close($multiHandle);

        $successRate = $this->calculateSuccessRate($statusCodes);

        if ($successRate > 20) {
            $this->resolve($statusCodes);
        } else {
            $this->reject('Success rate too low: ' . $successRate);
        }
    }

    private function getHandlesForUrls(array $urls, $multiHandle): array
    {
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

        return $handles;
    }

    private function executeMultiHandle($multiHandle): void
    {
        $active = 0;
        do {
            curl_multi_exec($multiHandle, $active);
            curl_multi_select($multiHandle);
        } while ($active);
    }

    private function getStatusCodes(array $handles): array
    {
        $statusCodes = [];

        foreach ($handles as $handle) {
            $result = curl_getinfo($handle);
            $statusCodes[$result['url']] = $result['http_code'];
        }

        return $statusCodes;
    }

    private function calculateSuccessRate(array $statusCodes): int
    {
        $successCodes = array_filter($statusCodes, function ($code) {
            if ($code >= 200 && $code < 300) {
                return true;
            }
            return false;
        });

        $percent = (count($successCodes) * 100) / count($statusCodes);

        return (int) round($percent);
    }
}
