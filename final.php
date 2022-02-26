<?php
include('./includes/helper.php');
if (!isset($_GET['path'])) {
    echo json_encode(array('error' => 'access denied'));
    exit;
}

$location = getFileLink($_GET['path'], $_GET['referer'], getallheaders()['Cookie']);
// todo check location
header('Location: ' . $location);
