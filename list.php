<?php
include('./includes/helper.php');
include('./includes/ui.php');

$page = 1;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
}
if (!isset($_GET['key'])) {
    die("here");
    // header('Location: ./');
}

$info = getList($_GET['key'], $page);

if ($info == null) {
    die("why");
    // header('Location: ./');
}

build_Header("Document Page")
?>

<div class="container">

    <br>

    <!-- search input -->
    <div class="center-div">
        <h2><?= $info['title'] ?></h2>
    </div>
    <br><br>
    <div class="article-grid">

        <?php
        if ($info['listType'] == 'movie')
            foreach ($info['list'] as $document)
                createDocument($document);
        else
            foreach ($info['list'] as $document)
                createCategory($document);
        ?>
    </div>


    <?php
    if ($info['maxPage'] > 1) {
    ?>
        <div class="center-div">
            Page: <?= $info['currentPage'] ?>/<?= $info['maxPage'] ?>
            <!-- pagination -->
        </div>
        <div class="center-div">

            <a class="link-btn" href="./list.php?key=<?= $_GET['key'] ?>">1</a>
            <a class="link-btn <?= $info['currentPage'] <= 1 ? 'disabled' : '' ?>" href="./list.php?key=<?= $_GET['key'] ?>&page=<?= $info['currentPage'] - 1 ?>">&lt;</a>
            <div style="padding: 10px;"><?= $info['currentPage'] ?></div>
            <a class="link-btn <?= $info['currentPage'] >= $info['maxPage'] ? 'disabled' : '' ?>" href="./list.php?key=<?= $_GET['key'] ?>&page=<?= $info['currentPage'] + 1 ?>">&gt;</a>
            <a class="link-btn" href="./list.php?key=<?= $_GET['key'] ?>&page=<?= $info['maxPage'] ?>"><?= $info['maxPage'] ?></a>

        </div>
    <?php
    }
    ?>

</div>
<?php
build_Footer();

function createDocument(array $data)
{
    echo <<<HTML
    <a href="./document.php?key={$data['key']}" class="doc-card">
        <img src="{$data['image']}" alt="{$data['name']}">
       
        
    </a>
    HTML;
}
function createCategory(array $data)
{
    echo <<<HTML
    <a href="./list.php?key={$data['key']}" class="link-btn">
        {$data['title']}       
    </a>
    HTML;
}
