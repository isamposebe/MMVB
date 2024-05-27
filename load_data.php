<?php

set_time_limit(0);

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
            fclose($newf);
        } else {
            echo "Не удалось открыть файл для записи: $newfname";
        }
        fclose($file);
    } else {
        echo "Не удалось открыть URL: $url";
    }
}

// Функция для распаковки ZIP архива
function unzipFile($zipFile, $extractTo) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        return true;
    } else {
        echo "Не удалось открыть ZIP архив: $zipFile";
        return false;
    }
}

// Функция для создания базы данных и таблицы, если они не существуют
function createDatabase() {
    $db = new SQLite3('moex_data.db');
    $db->exec("CREATE TABLE IF NOT EXISTS Trades (
        myNo INTEGER,
        SECCODE TEXT,
        BUYSELL TEXT,
        myTIME TEXT,
        ORDERNO INTEGER,
        myACTION INTEGER,
        PRICE REAL,
        VOLUME INTEGER,
        TRADENO TEXT,
        TRADEPRICE REAL
    )");
    return $db;
}

// Функция для вставки данных из CSV в базу данных
function insertData($db, $filePath) {
    // Очищаем таблицу перед вставкой новых данных
    $db->exec("DELETE FROM Trades");

    // Открываем CSV файл и читаем его построчно
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Пропускаем заголовок
        fgetcsv($handle);
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $no = SQLite3::escapeString($data[0]);
            $seccode = SQLite3::escapeString($data[1]);
            $buysell = SQLite3::escapeString($data[2]);
            $time = SQLite3::escapeString($data[3]);
            $orderno = SQLite3::escapeString($data[4]);
            $action = SQLite3::escapeString($data[5]);
            $price = SQLite3::escapeString($data[6]);
            $volume = SQLite3::escapeString($data[7]);
            $tradeno = SQLite3::escapeString($data[8]);
            $tradeprice = SQLite3::escapeString($data[9]);

            // Вставляем данные в таблицу Trades
            $query = "INSERT INTO Trades (myNo, SECCODE, BUYSELL, myTIME, ORDERNO, myACTION, PRICE, VOLUME, TRADENO, TRADEPRICE) 
                      VALUES ('$no', '$seccode', '$buysell', '$time', '$orderno', '$action', '$price', '$volume', '$tradeno', '$tradeprice')";
            $db->exec($query);
        }
        fclose($handle);
    } else {
        echo "Не удалось открыть CSV файл: $filePath";
    }
}

$url = "https://fs.moex.com/files/18307"; // URL для скачивания данных
$zipFilePath = "data.zip"; // Путь к ZIP файлу
$csvFilePath = "Trades.csv"; // Путь к CSV файлу

// Скачиваем и распаковываем данные
downloadFile($url, $zipFilePath);
if (unzipFile($zipFilePath, __DIR__)) {
    // Создаем и заполняем базу данных
    $db = createDatabase();
    insertData($db, $csvFilePath);
}

// Перенаправляем обратно на index.php для отображения данных
header("Location: index.php");
?>
