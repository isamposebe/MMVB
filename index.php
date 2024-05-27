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
            $db = new SQLite3('moex_data.db');
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $result = $db->query('SELECT * FROM Trades');
        ?>

        <table>
            <tr>
                <th>Trade Time</th>
                <th>Trade Price</th>
                <th>Trade Volume</th>
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