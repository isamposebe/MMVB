<!DOCTYPE html>
<html>
<head>
    <title>Moex Data Loader</title>
</head>
<body>
    <h1>Загрузить данные о сделках на валютном рынке с сайта https://www.moex.com/ru/orders?historicaldata (Валютный рынок (csv.zip, 3,3 Мб))</h1>
    <form action="load_data.php" method="post">
        <button type="submit">Загрузить данные</button>
    </form>
    <table border="1">
        <thead>
            <tr>
                <th>Время сделки</th>
                <th>Цена сделки</th>
                <th>Объем сделки</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // Подключаемся к базе данных SQLite
                $db = new SQLite3('moex_data.db');
                // Выполняем запрос для получения всех данных из таблицы Trades
                $results = $db->query('SELECT * FROM Trades');
                // Отображаем результаты в таблице
                while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['TradeTime']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['TradePrice']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['TradeVolume']) . "</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>

</body>
</html>