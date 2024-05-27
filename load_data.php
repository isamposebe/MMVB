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

    // Функция для распаковки ZIP архива
    function unzipFile($zipFile, $extractTo) {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    // Функция для вставки данных из CSV в базу данных
    function insertData($db, $filePath) {
        // Очищаем таблицу перед вставкой новых данных
        $db->exec("DELETE FROM Trades");

        // Открываем CSV файл и читаем его построчно
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            fgetcsv($handle); // Пропускаем заголовок
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $tradeTime = SQLite3::escapeString($data[0]);
                $tradePrice = (float)$data[1];
                $tradeVolume = (int)$data[2];

                // Вставляем данные в таблицу Trades
                $query = "INSERT INTO Trades (TradeTime, TradePrice, TradeVolume) VALUES ('$tradeTime', '$tradePrice', '$tradeVolume')";
                $db->exec($query);
            }
            fclose($handle);
        }
    }

    $url = "https://www.moex.com/ru/orders?historicaldata"; // URL для скачивания данных
    $zipFilePath = "data.zip"; // Путь к ZIP файлу
    $csvFilePath = "Trades.csv"; // Путь к CSV файлу

    // Скачиваем и распаковываем данные
    downloadFile($url, $zipFilePath);
    unzipFile($zipFilePath, __DIR__);

    // Создаем и заполняем базу данных
    $db = createDatabase();
    insertData($db, $csvFilePath);

    // Перенаправляем обратно на index.php для отображения данных
    header("Location: index.php");
?>