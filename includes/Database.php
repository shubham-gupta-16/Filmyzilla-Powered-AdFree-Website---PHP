<?php

class Database
{

    private $db;

    public function __construct(string $dbservername, string $dbuser, string $dbpassword, string $dbname)
    {
        $db = new mysqli($dbservername, $dbuser, $dbpassword, $dbname);
        $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, TRUE);
        $db->set_charset("utf8");
        $this->db = $db;
    }

    private function db(): mysqli
    {
        return $this->db;
    }
    // check db connected or not
    public function isConnected(): bool
    {
        $db = $this->db();
        if ($db == null) {
            return false;
        }
        if ($db->connect_error) {
            return false;
        }
        return true;
    }

    public function insertDocs(string $key, string $title, string $image)
    {
        $db = $this->db();
        if (!self::isConnected()) return false;
        $stmt = $db->prepare("INSERT INTO docs (fz_key, fz_title, fz_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $key, $title, $image);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    // update docs's image if it's not exist
    public function updateDocs(string $key, string $image)
    {
        $db = $this->db();
        if (!self::isConnected()) return false;
        $stmt = $db->prepare("UPDATE docs SET fz_image = ?, view_count = view_count + 1 WHERE fz_key = ?");
        $stmt->bind_param("ss", $image, $key);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    // get view_count and fz_image from db
    public function getDocs(string $key)
    {
        $db = $this->db();
        if (!self::isConnected()) return false;
        $stmt = $db->prepare("SELECT view_count, fz_image FROM docs WHERE fz_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($view_count, $fz_image);
        $stmt->fetch();
        $stmt->close();
        return [$view_count, $fz_image];
    }

    private function query(string $query): mysqli_result
    {
        $db = $this->db();
        $result = $db->query($query);
        return $result;
    }

    public function filterImages()
    {
        $db = $this->db();
        if (!self::isConnected()) return false;
        $result = $db->query("SELECT fz_key, fz_image FROM docs");

        while ($row = $result->fetch_assoc()) {
            $image = str_replace(['https://filmyzilla.beauty/', 'https://filmyzilla.productions/', 'https://filmyzilla.services/'], '', $row['fz_image']);
            $key = $row['fz_key'];
            $db->query("UPDATE docs SET fz_image = '$image' WHERE fz_key = '$key'");
        }
        return true;
    }

    public function close()
    {
        $this->db()->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}
