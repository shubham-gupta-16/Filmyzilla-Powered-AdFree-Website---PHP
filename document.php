<?php
include('./includes/helper.php');
include('./includes/ui.php');

if (!isset($_GET['key'])) {
    die('No URI specified');
}

$info = getDocumentInfo($_GET['key']);
if ($info == null) {
    die("Server not found");
}

echo json_encode($info, JSON_PRETTY_PRINT);
build_Header('Servers');
?>

<?php
foreach ($info['files'] as $files) {
    echo <<<HTML
    <a class="link-btn" href="servers.php?path={$files['path']}">{$files['name']} - {$files['size']}</a>
    <br>
    HTML;
}

?>


<?php
build_Footer();
?>