<?php

function getPaginationData($pagination_div): array
{
    $result = [];
    if ($pagination_div != null) {
        preg_match('#\((.*?)\)#', $pagination_div->find('font', 0)->plaintext, $match);
        $pageData = explode('/', $match[1]);
        $result['maxPage'] = (int) $pageData[1];
        $result['currentPage'] = (int) $pageData[0];
    } else {
        $result['maxPage'] = 1;
        $result['currentPage'] = 1;
        # code...
    }
    return $result;
}

function getCategoryList($list): array
{
    $categories = [];
    foreach ($list->find('a[title]') as $listed) {
        $arr = [
            'key' => buildKey($listed->href, 'category'),
            'title' => trim($listed->title),
        ];
        $categories[] = $arr;
    }
    return $categories;
}

function getMoviesList($list, Database $database): array
{
    $movies = [];
    foreach ($list->find('a[title]') as $listed) {
        $arr = [
            'key' => buildKey($listed->href, 'movie'),
            'name' => trim($listed->title),
        ];
        insertExtraData($arr);
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
                $imagePath = 'files/images/' . str_replace([' ', '.',], ['_', ''], $arr['name'] . ' (' . $keyData[1]) . '.jpg';
            } else {
                $imagePath = 'files/images/' . str_replace([' ', '.',], ['_', ''], $arr['title']) . '.jpg';
            }
        }
        $arr['image'] = App::getSourceUrl() . $imagePath;

        $database->insertDocs($arr['key'], $arr['name'], $imagePath);
        $movies[] = $arr;
    }
    return $movies;
}

function getMovieInfo(string $key, $html_body, Database $database): ?array
{
    $info = array();
    if ($html_body == null) return null;
    $container = $html_body->find('div.video', 0);
    if ($container == null) return null;

    $info['name'] = trim($container->find('p.info', 0)->find('b', 0)->plaintext);
    insertExtraData($info);
    $info['image'] = $container->find('.imglarge', 0)->find('img', 0)->src;
    foreach ($container->find('p.black') as $pBlack) {
        $info[strtolower(str_replace([' :- ', ' '], ['', '_'], $pBlack->find('font', 0)->plaintext))] = trim($pBlack->find('font', 1)->plaintext);
    }
    foreach ($html_body->find('div.listed') as $listed) {
        $a = $listed->find('a', 0);
        if ($a == null) continue;
        $info['files'][] = [
            'path' => str_replace(App::getSourceUrl(), '', $a->href),
            'name' => trim($a->find('font', 1)->plaintext),
            'size' =>  strtoupper(str_replace(array('(', ')'), '', trim($a->find('span', 0)->plaintext))),
        ];
    }

    $database->updateDocs($key, $info['image']);
    return $info;
}

function insertExtraData(array &$info)
{
    if (strpos($info['name'], '(') !== false) {
        $titleData = explode('(', $info['name']);

        $info['name'] = trim($titleData[0]);
        if ($info['name'] == 'Guardians of the Galaxy Vol. 2') {
            $info['name'] = 'Guardians of The Galaxy Vol. 2';
        }

        $documentData = explode(')', $titleData[1]);
        $info['year'] = trim($documentData[0]);
        $info['audio'] = trim(str_replace('Movie', '', $documentData[1]));
    }
}
function buildKey(string $url, string $listType): string
{
    $blah = parse_url($url);
    $url = str_replace(App::getSourceUrl(), '', $url);
    return str_replace(["/$listType/", '/default/1.html'], '', $blah['path']);
}
