<!DOCTYPE html>
<html>
<head>
    <title>MOEX Trades</title>
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

    <h1>Trades from MOEX</h1>

    <?php
    try {
        // Подключаемся к базе данных SQLite
        $db = new SQLite3('moex_data.db');
        // Выполняем запрос для получения всех данных из таблицы Trades
        $results = $db->query('SELECT * FROM Trades');
        if (!$results) {
            die("Error executing query: " . $db->lastErrorMsg());
        }
    } catch (Exception $e) {
        die("Error connecting to database: " . $e->getMessage());
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
