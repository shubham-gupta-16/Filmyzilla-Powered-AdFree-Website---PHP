<?php
require_once __DIR__ . '/App.php';
require_once __DIR__ . '/simple_html_dom.php';
require_once __DIR__ . '/curl.php';
require_once __DIR__ . '/content_decoder.php';

$database = App::getDatabase();

function getList(string $key, int $page = 1, string $letter = 'default'): ?array
{
    global $database;
    $path = "category/$key/$letter/$page.html";
    $response = fetchCurl(App::getSourceUrl() . $path, [App::getAuthorityHeader()]);
    if ($response == null || $response[0] == '') {
        return null;
    }
    $document = str_get_html($response[0]);
    if ($document == null) return null;
    $result = ['key' => $key];
    $pagination = getPaginationData($document->find('div.path', 0));
    $result['maxPage'] = $pagination['maxPage'];
    $result['currentPage'] = $pagination['currentPage'];
    $head = $document->find('div.head', 0);
    if ($head != null) {
        $result['title'] = $head->plaintext;
    }
    $list = $document->find('div.list  ', 0);
    if ($list == null) {
        $list = $document->find('div.List  ', 0);
        $result['listType'] = 'category';
        $result['list'] = getCategoryList($list);
    } else {
        $result['listType'] = 'movie';
        $result['list'] = getMoviesList($list, $database);
    }
    return $result;
}

function getDocumentInfo(string $key): ?array
{
    global $database;
    $response = fetchCurl(App::getSourceUrl() . 'movie/' . $key, [App::getAuthorityHeader()]);
    if ($response == null || $response[0] == '') {
        return null;
    }
    $documentPage = str_get_html($response[0]);
    return getMovieInfo($key, $documentPage, $database);
}

function getFileLink(string $path, string $referer, string $cookie): ?string
{
    $headers = array();
    $headers[] = App::getAuthorityHeader();
    $headers[] = 'Referer: ' . App::getSourceUrl() . $referer;
    $headers[] = 'Cookie: ' . $cookie;

    $response = fetchCurl(App::getSourceUrl() . $path, $headers);
    return $response[1]['location'][0];
}

function getServerInfo(string $path): ?array
{
    $response = fetchCurl(App::getSourceUrl() . $path, ['Authority: filmyzilla.' . App::getExt()]);
    // todo check response
    $serverPage = str_get_html($response[0]);

    $info = [];
    // header set-cookie
    $info['cookie'] = $response[1]['set-cookie'][0];
    $info['referer'] = $path;
    // header('Set-Cookie: ' . $response[1]['set-cookie'][0]);

    foreach ($serverPage->find('a.dwnLink[title]') as $listed) {
        $info['links'][] = str_replace(App::getSourceUrl(), '', $listed->href);
    }
    return $info;
}