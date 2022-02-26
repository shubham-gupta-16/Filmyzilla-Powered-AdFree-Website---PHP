<?php
require_once './includes/ui.php';
require_once './includes/App.php';

$database = App::getDatabase();
$database->filterImages();

$keyList = [
    '406/Popular-tv-shows-(hindi-dubbed)' => 'Popular TV Shows (Hindi Dubbed)',
    '353/Web-series-download' => 'Web Series Download',
    '274/Marvel-cinematic-universe-movies' => 'Marvel Cinematic Universe Movies',
    '204/Bollywood-full-movies' => 'Bollywood Full Movies',
    '203/Hollywood-hindi-dubbed-movies' => 'Hollywood Hindi Dubbed Movies',
    '202/Hollywood-english-movies' => 'Hollywood English Movies',
    '201/Hollywood-movie-series' => 'Hollywood Movie Series',
    '205/South-indian-hindi-dubbed-movies' => 'South Indian Hindi Dubbed Movies',
    '272/Tv-shows' => 'Latest Indian Tv Shows',
    '275/Hindi-web-series' => 'Latest Hindi Web Series',
    '273/Game-of-thrones-all-parts' => 'Game of Thrones All Parts',
];

build_Header('FZ');
// echo div
foreach ($keyList as $key => $value) {
    // anchor
    echo '<div class="center-div"><a class="link-btn" href="list.php?key=' . $key . '">' . $value . '</a></div>';
}

build_Footer();
