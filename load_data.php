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
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        fgetcsv($handle); // Пропускаем заголовок
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // Устанавливаем параметры и выполняем запрос
            $stmt->bindValue(1, $data[0]);
            $stmt->bindValue(2, $data[1]);
            $stmt->bindValue(3, $data[2]);
            $stmt->bindValue(4, $data[3]);
            $stmt->bindValue(5, $data[4]);
            $stmt->bindValue(6, $data[5]);
            $stmt->bindValue(7, $data[6]);
            $stmt->bindValue(8, $data[7]);
            $stmt->bindValue(9, $data[8]);
            $stmt->bindValue(10, $data[9]);
            
            if (!$stmt->execute()) {
                echo "Ошибка вставки данных: " . $db->lastErrorMsg() . "<br>";
                $db->exec('ROLLBACK TRANSACTION');
                return;
            }
        }
        
        // Завершаем транзакцию
        $db->exec('COMMIT TRANSACTION');
        fclose($handle);
    } else {
        echo "Не удалось открыть CSV файл: $filePath";
    }
}

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
