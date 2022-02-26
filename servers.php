<?php
include('./includes/helper.php');
include('./includes/ui.php');

if (!isset($_GET['path'])) {
    die('No Path specified');
}
// load server page
$info = getServerInfo($_GET['path']);
// todo check server
// header set-cookie
header('Set-Cookie: ' . $info['cookie']);
// die(json_encode($info));
build_Header('Servers');
// echo json_encode($info);
?>

<?php
foreach ($info['links'] as $link) {
    echo <<<HTML
    <a class="link-btn" href="final.php?path={$link}&referer={$info['referer']}">DOWNLOAD</a>
    <br>
    <a class="link-btn" style="background-color: #ff6666; border: 1px solid red" href="player.php?path={$link}&referer={$info['referer']}">PLAY ONLINE</a>
    <br>

    HTML;
}
?>



<?php
build_Footer();
?>