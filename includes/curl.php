<?php
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
function getDefaultHeader(array $array, string $key, string $default)
{
    if (isset($array[$key])) {
        return $array[$key];
    }
    return $default;
}

function fetchCurl(string $path, ?array $requestHeaders = null, ?array $postArr = null): ?array
{
    $url = App::getSourceUrl() . $path;
    $browserHeaders = getallheaders();
    $baseHeaders = array();

    $baseHeaders[] = 'Connection: keep-alive';
    $baseHeaders[] = 'Cache-Control: max-age=0';
    $baseHeaders[] = 'Upgrade-Insecure-Requests: 1';
    $baseHeaders[] = 'Sec-Fetch-User: ?1';
    $baseHeaders[] = 'Sec-Fetch-Mode: navigate';
    $baseHeaders[] = 'Sec-Fetch-Dest: document';

    $baseHeaders[] = 'sec-ch-ua: ' . getDefaultHeader($browserHeaders, 'sec-ch-ua', "\"Microsoft Edge\";v=\"95\", \"Chromium\";v=\"95\", \";Not A Brand\";v=\"99\"");
    $baseHeaders[] = 'sec-ch-ua-mobile: ' . getDefaultHeader($browserHeaders, 'sec-ch-ua-mobile', '?1');
    $baseHeaders[] = 'sec-ch-ua-platform: ' . getDefaultHeader($browserHeaders, 'sec-ch-ua-platform', '"Android"');

    $baseHeaders[] = 'Sec-Fetch-Site: ' . getDefaultHeader($browserHeaders, 'Site-Fetch-Site', 'none');
    $baseHeaders[] = 'Accept-Language: ' . getDefaultHeader($browserHeaders, 'Accept-Language', 'en-US,en;q=0.9');

    $baseHeaders[] = 'User-Agent: ' . getDefaultHeader($browserHeaders, 'User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36');
    $baseHeaders[] = 'Accept: ' . getDefaultHeader($browserHeaders, 'Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9');


    if ($requestHeaders != null) {
        $baseHeaders = array_merge($baseHeaders, $requestHeaders);
    }
    $response = executeCurl($url, $baseHeaders, $postArr);
    if (isset($response['headers']['location'])) {
        $ext = pathinfo(parse_url($response['headers']['location'], PHP_URL_HOST), PATHINFO_EXTENSION);
        App::getExt($ext);
        $response = executeCurl(App::getSourceUrl() . $path, $baseHeaders, $postArr);
    }
    return [$response['data'], $response['headers']];
}

function executeCurl(string $url, ?array $headers = null, ?array $postArr = null)
{
    $responseHeaders = [];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    if ($postArr != null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postArr));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt(
        $ch,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid responseHeaders
                return $len;

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        }
    );
    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [
        'code' => $httpcode,
        'data' => $server_output,
        'headers' => $responseHeaders,
    ];
}
