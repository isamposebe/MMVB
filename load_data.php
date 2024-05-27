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

// Функция для импорта CSV в SQLite
function import_csv_to_sqlite(&$pdo, $csv_path, $options = array())
{
    extract($options);
    
    if (($csv_handle = fopen($csv_path, "r")) === FALSE)
        throw new Exception('Cannot open CSV file');
        
    if(!$delimiter)
        $delimiter = ';';
        
    if(!$table)
        $table = preg_replace("/[^A-Z0-9]/i", '', basename($csv_path));
    
    if(!$fields){
        $fields = array_map(function ($field){
            return strtolower(preg_replace("/[^A-Z0-9]/i", '', $field));
        }, fgetcsv($csv_handle, 0, $delimiter));
    }
    
    $create_fields_str = join(', ', array_map(function ($field){
        return "$field TEXT NULL";
    }, $fields));
    
    $pdo->beginTransaction();
    
    $create_table_sql = "CREATE TABLE IF NOT EXISTS $table ($create_fields_str)";
    $pdo->exec($create_table_sql);

    $insert_fields_str = join(', ', $fields);
    $insert_values_str = join(', ', array_fill(0, count($fields),  '?'));
    $insert_sql = "INSERT INTO $table ($insert_fields_str) VALUES ($insert_values_str)";
    $insert_sth = $pdo->prepare($insert_sql);
    
    $inserted_rows = 0;
    while (($data = fgetcsv($csv_handle, 0, $delimiter)) !== FALSE) {
        $insert_sth->execute($data);
        $inserted_rows++;
    }
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
