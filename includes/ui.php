<?php

function build_Header(string $title)
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title ?></title>
    </head>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        img {
            vertical-align: middle;
        }

        body {
            font-family: 'Calibri', sans-serif;
        }

        a {
            text-decoration: none;
        }

        footer {
            padding-top: 20px;
            background-color: #eeeeee;
            height: 90px;
            overflow: hidden;
        }

        .container {
            display: block;
            margin-left: auto;
            margin-right: auto;
            min-height: calc(100vh - 90px);
            padding: 0 15px;
            padding-bottom: 30px;
            max-width: 1200px;
        }

        .center-div {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .article-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            grid-gap: 20px;
        }

        @media screen and (max-width: 456px) {
            .article-grid {
                grid-gap: 10px;
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }

        /* article card */

        .doc-card {
            display: block;
            background-color: white;
            overflow: hidden;
            position: relative;
            border: 1px;
            background-color: #dddeee;
            padding-top: 150%;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        .doc-card > img{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-card header {
            position: absolute;
            display: none;
            bottom: 0;
        }

        .doc-card img {
            width: 100%;
            margin: 0;
            height: unset;
        }

        img.aligncenter {
            width: 100%;
            max-width: 700px;
        }

        a.download-btn {
            text-decoration: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            background-color: #a82711;
        }

        .search-div form {
            margin-bottom: 40px;
            display: grid;
            grid-template-columns: auto auto;
            gap: 5px;
        }

        /* search input */
        .search-div input {
            padding: 6px;
            font-size: 17px;
            outline: none;
            border-radius: 5px;
        }

        .search-div input[type=text] {
            border: 2px solid #ccc;
            width: 100%;
        }

        .search-div input[type=submit] {
            background-color: #0088ee;
            border: 2px solid #0088ee;
            color: white;
            cursor: pointer;
        }

        /* added later */

        .link-btn {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid #0031ca;
            border-radius: 5px;
            margin: 5px;
            background-color: #3d5afe;
            color: #ffffff;
        }

        .link-btn.disabled {
            border: 1px solid #77aaff;
            background-color: #99ccff;
            pointer-events: none;
            cursor: default;
        }
    </style>

    <body>
        <div class="container">


        <?php
    }

    function build_Footer()
    { ?>
        </div>

        <footer>
            <center>
                <h4>This website was created for educational purpose. It uses the data of Filmyzialla. We never promote piracy of copyright content.</h4><br><br><br>
            </center>
        </footer>
    </body>

    </html>

<?php
    }
