<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config.php';

class App
{
    private static $EXT = '';

    public static function getDatabase(): Database
    {
        return new Database(dbservername, dbuser, dbpassword, dbname);
    }

    public static function getExt(): string
    {
        if (self::$EXT == '') {
            self::$EXT = file_get_contents(__DIR__ . '/SOURCE_EXT');
        }
        return self::$EXT;
    }
    public static function setExt(string $ext): void
    {
        self::$EXT = $ext;
        file_put_contents(__DIR__ . '/SOURCE_EXT', $ext);
    }

    public static function getSourceUrl(): string
    {
        return 'https://filmyzilla.' . self::getExt() . '/';
    }

    public static function getAuthorityHeader(): string
    {
        return 'Authority: filmyzilla.' . self::getExt();
    }
}
