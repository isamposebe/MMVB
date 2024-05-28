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
        _NO INTEGER,
        _SECCODE TEXT,
        _BUYSELL TEXT,
        _TIME TEXT,
        _ORDERNO INTEGER,
        _ACTION INTEGER,
        _PRICE REAL,
        _VOLUME INTEGER,
        _TRADENO TEXT,
        _TRADEPRICE TEXT
    )");
    return $db;
}

// Функция для вставки данных из CSV в базу данных
function insertData($db, $filePath) {
    // Очищаем таблицу перед вставкой новых данных
    $db->exec("DELETE FROM Trades");

    // Открываем CSV файл и читаем его построчно
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Начинаем транзакцию
        $db->exec('BEGIN TRANSACTION');
        
        $stmt = $db->prepare("INSERT INTO Trades (_NO, _SECCODE, _BUYSELL, _TIME, _ORDERNO, _ACTION, _PRICE, _VOLUME, _TRADENO, _TRADEPRICE) 
                              VALUES (:_no, :_seccode, :_buysell, :_time, :_orderno, :_action, :_price, :_volume, :_tradeno, :_tradeprice)");
        
        fgetcsv($handle); // Пропускаем заголовок
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // Проверяем, есть ли значение для каждого столбца
            $no = isset($data[0]) ? SQLite3::escapeString($data[0]) : '';
            $seccode = isset($data[1]) ? SQLite3::escapeString($data[1]) : '';
            $buysell = isset($data[2]) ? SQLite3::escapeString($data[2]) : '';
            
            // Преобразование временной метки
            $timestampString = isset($data[3]) ? SQLite3::escapeString($data[3]) : '';
            $timestamp = intval(rtrim($timestampString, ';'));
            $time = $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

            $orderno = isset($data[4]) ? SQLite3::escapeString($data[4]) : '';
            $action = isset($data[5]) ? SQLite3::escapeString($data[5]) : '';
            $price = isset($data[6]) ? SQLite3::escapeString($data[6]) : '';
            $volume = isset($data[7]) ? SQLite3::escapeString($data[7]) : '';
            $tradeno = isset($data[8]) ? SQLite3::escapeString($data[8]) : '';
            $tradeprice = isset($data[9]) ? SQLite3::escapeString($data[9]) : '';
        
            // Вставляем данные в таблицу Trades
            $query = "INSERT INTO Trades (_NO, _SECCODE, _BUYSELL, _TIME, _ORDERNO, _ACTION, _PRICE, _VOLUME, _TRADENO, _TRADEPRICE) 
                      VALUES ('$no', '$seccode', '$buysell', '$time', '$orderno', '$action', '$price', '$volume', '$tradeno', '$tradeprice')";
        
            if (!$db->exec($query)) {
                echo "Ошибка вставки данных: " . $db->lastErrorMsg() . "<br>";
                $db->exec('ROLLBACK TRANSACTION');
                return;
            }
        }
        // Привязка значений к параметрам запроса
        $stmt->bindValue(':no', $no, SQLITE3_INTEGER);
        $stmt->bindValue(':seccode', $seccode, SQLITE3_TEXT);
        $stmt->bindValue(':buysell', $buysell, SQLITE3_TEXT);
        $stmt->bindValue(':time', $time, SQLITE3_TEXT);
        $stmt->bindValue(':orderno', $orderno, SQLITE3_INTEGER);
        $stmt->bindValue(':action', $action, SQLITE3_INTEGER);
        $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
        $stmt->bindValue(':volume', $volume, SQLITE3_INTEGER);
        $stmt->bindValue(':tradeno', $tradeno, SQLITE3_TEXT);
        $stmt->bindValue(':tradeprice', $tradeprice, SQLITE3_TEXT);
        
        // Завершаем транзакцию
        $db->exec('COMMIT TRANSACTION');
        fclose($handle);
    } else {
        echo "Не удалось открыть CSV файл: $filePath";
    }
}

// Поиск CSV файлов
function findCsvFiles($directory) {
    $csvFiles = []; // Массив для хранения найденных файлов

    // Проверяем, существует ли указанный каталог
    if (!is_dir($directory)) {
        echo "Указанный каталог не существует.";
        return $csvFiles;
    }

    // Рекурсивно обходим все файлы и каталоги в указанном каталоге
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        // Проверяем, является ли текущий элемент файлом и имеет ли расширение .csv
        if ($file->isFile() && $file->getExtension() == 'csv') {
            // Добавляем путь к найденному файлу в массив
            $csvFiles[] = $file->getPathname();
        }
    }

    return $csvFiles;
}
//$directory = ''; // Укажите путь к каталогу, в котором нужно искать файлы
$url = "https://fs.moex.com/files/18307"; // URL для скачивания данных
$zipFilePath = "data.zip"; // Путь к ZIP файлу
$csvFilePath = "OrderLog20181229.csv"; // Путь к CSV файлу

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
