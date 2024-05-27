<?php
    // Функция для скачивания файла по URL
    function downloadFile($url, $path) {
        $newfname = $path;
        $file = fopen($url, 'rb');
        if ($file) {
            $newf = fopen($newfname, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
    }


    // Функция для создания базы данных и таблицы, если они не существуют
    function createDatabase() {
        $db = new SQLite3('moex_data.db');
        $db->exec("CREATE TABLE IF NOT EXISTS Trades (
            TradeTime TEXT,
            TradePrice REAL,
            TradeVolume INTEGER
        )");
        return $db;
    }

?>