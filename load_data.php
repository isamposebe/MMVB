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

// Функция для открытия CSV файла и возвращения его дескриптора
function openCsvFile($filePath) {
    if (($handle = fopen($filePath, "r")) === FALSE) {
        throw new Exception("Не удалось открыть CSV файл: $filePath");
    }
    // Пропускаем заголовок
    fgetcsv($handle);
    return $handle;
}

// Функция для обработки строки CSV
function processCsvRow($data) {
    $timestampString = isset($data[3]) ? rtrim($data[3], ';') : '0';
    $timestamp = intval($timestampString);
    $time = $timestamp > 0 && $timestamp < PHP_INT_MAX ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
    
    return [
        'no' => $data[0],
        'seccode' => $data[1],
        'buysell' => $data[2],
        'time' => $time,
        'orderno' => $data[4],
        'action' => $data[5],
        'price' => $data[6],
        'volume' => $data[7],
        'tradeno' => $data[8],
        'tradeprice' => $data[9]
    ];
}

// Функция для вставки данных в базу данных
function insertDataIntoDatabase($db, $rowData) {
    $stmt = $db->prepare("INSERT INTO Trades (_NO, _SECCODE, _BUYSELL, _TIME, _ORDERNO, _ACTION, _PRICE, _VOLUME, _TRADENO, _TRADEPRICE) 
                          VALUES (:_no, :_seccode, :_buysell, :_time, :_orderno, :_action, :_price, :_volume, :_tradeno, :_tradeprice)");
    
    $stmt->bindValue(':_no', $rowData['no'], SQLITE3_INTEGER);
    $stmt->bindValue(':_seccode', $rowData['seccode'], SQLITE3_TEXT);
    $stmt->bindValue(':_buysell', $rowData['buysell'], SQLITE3_TEXT);
    $stmt->bindValue(':_time', $rowData['time'], SQLITE3_TEXT);
    $stmt->bindValue(':_orderno', $rowData['orderno'], SQLITE3_INTEGER);
    $stmt->bindValue(':_action', $rowData['action'], SQLITE3_INTEGER);
    $stmt->bindValue(':_price', $rowData['price'], SQLITE3_FLOAT);
    $stmt->bindValue(':_volume', $rowData['volume'], SQLITE3_INTEGER);
    $stmt->bindValue(':_tradeno', $rowData['tradeno'], SQLITE3_TEXT);
    $stmt->bindValue(':_tradeprice', $rowData['tradeprice'], SQLITE3_TEXT);

    if (!$stmt->execute()) {
        throw new Exception("Ошибка вставки данных: " . $db->lastErrorMsg());
    }
}

// Основная функция для вставки данных из CSV в базу данных
function insertData($db, $filePath) {
    // Очищаем таблицу перед вставкой новых данных
    $db->exec("DELETE FROM Trades");

    try {
        $handle = openCsvFile($filePath);
        $db->exec('BEGIN TRANSACTION');

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $rowData = processCsvRow($data);
            insertDataIntoDatabase($db, $rowData);
        }

        $db->exec('COMMIT TRANSACTION');
        fclose($handle);
    } catch (Exception $e) {
        $db->exec('ROLLBACK TRANSACTION');
        echo $e->getMessage();
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
    try {
           // Создаем и заполняем базу данных
        $db = createDatabase();
        insertData($db, $csvFilePath);
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Перенаправляем обратно на index.php для отображения данных
header("Location: index.php");
?>
