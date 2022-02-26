<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/simple_html_dom.php';

const FZ_URL = 'https://filmyzilla.' . EXT . '/';

$database = new Database(dbservername, dbuser, dbpassword, dbname);


function getList(string $key, int $page = 1, string $letter = 'default')
{
    global $database;
    $path = "category/$key/$letter/$page.html";
    $response = fetchCurl(FZ_URL . $path, ['Authority: filmyzilla.' . EXT]);
    if ($response == null || $response[0] == '') {
        return null;
    }
    $document = str_get_html($response[0]);

    // $test = $document->find('div.listing-drdfitem', 0) ?? 'null';
    // var_dump($test);

    $info = ['key' => $key];
    $pagination = $document->find('div.path', 0);
    if ($pagination != null) {
        preg_match('#\((.*?)\)#', $pagination->find('font', 0)->plaintext, $match);
        $pageData = explode('/', $match[1]);
        $info['maxPage'] = (int) $pageData[1];
        $info['currentPage'] = (int) $pageData[0];
    } else {
        $info['maxPage'] = 1;
        $info['currentPage'] = 1;
        # code...
    }
    $head = $document->find('div.head', 0);
    if ($head != null) {
        $info['title'] = $head->plaintext;
    }
    $list = $document->find('div.list  ', 0);
    if ($list == null) {
        $list = $document->find('div.List  ', 0);
        $info['listType'] = 'category';
    } else {
        $info['listType'] = 'movie';
    }
    foreach ($list->find('a[title]') as $listed) {
        $arr = [
            'key' => urlToKey($listed->href, $info['listType']),
            'title' => trim($listed->title),
        ];
        if ($info['listType'] == 'movie') {
            addExtraData($arr);

            $docs = $database->getDocs($arr['key']);
            if ($docs != null) {
                $arr['viewCount'] = $docs[0];
            }
            $imagePath = $docs[1];
            if ($docs != null && $docs[1] != null) {
            } else {
                // image url
                $keyData = explode('(', ucwords(explode('/', str_replace(['-', '.html'], [' ', ''], $arr['key']))[1]));
                if (sizeof($keyData) > 1) {
                    $imagePath = FZ_URL . 'files/images/' . str_replace([' ', '.',], ['_', ''], $arr['name'] . ' (' . $keyData[1]) . '.jpg';
                } else {
                    $imagePath = FZ_URL . 'files/images/' . str_replace([' ', '.',], ['_', ''], $arr['title']) . '.jpg';
                }
            }
            $arr['image'] = FZ_URL . $imagePath;

            $database->insertDocs($arr['key'], $arr['title'], $arr['image']);
        }
        $info['list'][] = $arr;
    }
    return $info;
}

function getFileLink(string $path, string $referer, string $cookie): ?string
{
    // $headers = [];
    // $headers[] = 'Authority: filmyzilla.' . EXT;
    // $headers[] = 'Sec-Fetch-Site: same-origin';
    // $headers[] = 'Referer: ' . FZ_URL . $referer;
    // $headers[] = 'Cookie: ' . $cookie;

    $headers = array();
    $headers[] = 'Authority: filmyzilla.' . EXT;
    $headers[] = 'Referer: ' . FZ_URL . $referer;
    $headers[] = 'Cookie: ' . $cookie;

    $response = fetchCurl(FZ_URL . $path, $headers);
    return $response[1]['location'][0];
}

function getServerInfo(string $path): ?array
{
    $response = fetchCurl(FZ_URL . $path, ['Authority: filmyzilla.' . EXT]);
    // todo check response
    $serverPage = str_get_html($response[0]);

    $info = [];
    // header set-cookie
    $info['cookie'] = $response[1]['set-cookie'][0];
    $info['referer'] = $path;
    // header('Set-Cookie: ' . $response[1]['set-cookie'][0]);

    foreach ($serverPage->find('a.dwnLink[title]') as $listed) {
        $info['links'][] = str_replace(FZ_URL, '', $listed->href);
    }
    return $info;
}

function getDocumentInfo(string $key): ?array
{
    global $database;
    $response = fetchCurl(FZ_URL . 'movie/' . $key, ['Authority: filmyzilla.' . EXT]);
    // todo check response
    if ($response == null || $response[0] == '') {
        return null;
    }
    $documentPage = str_get_html($response[0]);

    $container = $documentPage->find('div.video', 0);
    $info = array();

    $info['title'] = trim($container->find('p.info', 0)->find('b', 0)->plaintext);
    addExtraData($info);
    $info['image'] = $container->find('.imglarge', 0)->find('img', 0)->src;

    $database->updateDocs($key, $info['image']);

    foreach ($container->find('p.black') as $pBlack) {
        $info[str_replace(' :- ', '', $pBlack->find('font', 0)->plaintext)] = trim($pBlack->find('font', 1)->plaintext);
    }
    // echo $container->outertext;

    foreach ($documentPage->find('div.listed') as $listed) {
        $a = $listed->find('a', 0);
        if ($a == null) continue;
        $info['files'][] = [
            'path' => str_replace(FZ_URL, '', $a->href),
            'name' => trim($a->find('font', 1)->plaintext),
            'size' =>  strtoupper(str_replace(array('(', ')'), '', trim($a->find('span', 0)->plaintext))),
        ];
    }
    return $info;
}

function fetchCurl(string $url, ?array $requestHeaders = null, ?array $postArr = null): ?array
{
    $browserHeaders = getallheaders();
    $responseHeaders = [];
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
        // $baseHeaders[] = 'Cookie: ' . $cookie;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    if ($postArr != null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postArr));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $baseHeaders);
    curl_setopt(
        $ch,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid responseHeaders
                return $len;

            $responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);
            return $len;
        }
    );
    $response = curl_exec($ch);
    curl_close($ch);
    return [$response, $responseHeaders];
}

function addExtraData(array &$info)
{
    $titleData = explode('(', $info['title']);

    $info['name'] = trim($titleData[0]);
    if ($info['name'] == 'Guardians of the Galaxy Vol. 2') {
        $info['name'] = 'Guardians of The Galaxy Vol. 2';
    }

    if (sizeof($titleData) <= 1) {
        return;
    }

    $documentData = explode(')', $titleData[1]);
    $info['year'] = trim($documentData[0]);
    $info['audio'] = trim(str_replace('Movie', '', $documentData[1]));
}

function urlToKey(string $url, string $listType): string
{
    $blah = parse_url($url);
    $url = str_replace(FZ_URL, '', $url);
    return str_replace(["/$listType/", '/default/1.html'], '', $blah['path']);
}

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


// SQL INSERTER
