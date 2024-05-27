<!DOCTYPE html>
<html>
<head>
    <title>Валютный рынок (csv.zip, 3,3 Мб)</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>

    <h1>Валютный рынок (csv.zip, 3,3 Мб)</h1>

    <form action="load_data.php" method="post">
        <button type="submit">Загрузить данные</button>
    </form>
    
    <?php
    // Подключаемся к базе данных SQLite
    try {
        $db = new SQLite3('moex_data.db');
    } catch (Exception $e) {
        die("Error connecting to database: " . $e->getMessage());
    }

    // Выполняем запрос для получения всех данных из таблицы Trades
    $results = $db->query('SELECT * FROM Trades');
    if (!$results) {
        die("Error executing query: " . $db->lastErrorMsg());
    }
    ?>

    <table>
        <tr>
            <th>Trade Time</th>
            <th>Trade Price</th>
            <th>Trade Volume</th>
        </tr>
        <tbody>
            <?php
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
