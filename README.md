# Сделки и заявки на валютном рынке ММВБ

Было реализована загрузка и отображение сделок на валютном рынке ММВБ с сайта [moex.com](https://www.moex.com/ru/orders?historicaldata)

***

## Пройдемся по заданию 

### 1. Загрузить данные о сделках на валютном рынке с сайта [moex.com](https://www.moex.com/ru/orders?historicaldata) (Валютный рынок (csv.zip, 3,3 Мб))

Загружаем файл zip, разархивируем его и записываем его на локальную БД sqlite3

```
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
```

### 2. Отобразить данные в виде таблицы

В index.php берем данные из БД и отрображаем таблицу(Из-за больший даных идет огромная нагрузка, так что надо подождать)

### 3. Корректно вывести время сделок (см. спецификацию формата данных)

Прописал доп. условия для даты

```
$timestampString = isset($data[3]) ? rtrim($data[3], ';') : '0';
$timestamp = intval($timestampString > 0 ? date('Y-m-d H:i:s', $timestampString) : date('Y-m-d H:i:s'));
$time = $timestamp > 0 && $timestamp < PHP_INT_MAX ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
```

### 4. Предусмотреть повторную загрузку данных по команде или по кнопке

Есть кнопка для загрузки данных в index.php

<form action="load_data.php" method="post">
    <button type="submit">Загрузить данные</button>
</form>

### 5. Данные загрузить в локальную БД [sqlite3](https://www.php.net/manual/en/sqlite3.open.php)

В корневой папке под именем "moex_data.db"