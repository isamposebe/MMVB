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
        th {
            cursor: pointer;
        }
        #searchInput {
            margin-bottom: 10px;
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <h1>Валютный рынок (csv.zip, 3,3 Мб)</h1>

    <form action="load_data.php" method="post">
        <button type="submit">Загрузить данные</button>
    </form>

    <input type="text" id="searchInput" placeholder="Поиск...">

    <?php
    // Подключаемся к базе данных SQLite
    try {
        $db = new SQLite3('moex_data.db');
    } catch (Exception $e) {
        die("Error connecting to database: " . $e->getMessage());
    }
    // Выполняем запрос для получения всех данных из таблицы Trades
    $results = $db->query('SELECT * FROM Trades LIMIT 100');
    if (!$results) {
        die("Error executing query: " . $db->lastErrorMsg());
    } else {
        echo "Запрос выполнен успешно<br>";
    }

    $rows = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    if (empty($rows)) {
        echo "Данные не найдены<br>";
    } else {
        echo "Найдено данных: " . count($rows) . "<br>";
    }
    ?>

    <table id="dataTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">NO</th>
                <th onclick="sortTable(1)">SECCODE</th>
                <th onclick="sortTable(2)">BUYSELL</th>
                <th onclick="sortTable(3)">TIME</th>
                <th onclick="sortTable(4)">ORDERNO</th>
                <th onclick="sortTable(5)">ACTION</th>
                <th onclick="sortTable(6)">PRICE</th>
                <th onclick="sortTable(7)">VOLUME</th>
                <th onclick="sortTable(8)">TRADENO</th>
                <th onclick="sortTable(9)">TRADEPRICE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['_NO']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_SECCODE']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_BUYSELL']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_TIME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_ORDERNO']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_ACTION']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_PRICE']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_VOLUME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_TRADENO']) . "</td>";
                echo "<td>" . htmlspecialchars($row['_TRADEPRICE']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('dataTable');
            var tr = table.getElementsByTagName('tr');

            for (var i = 1; i < tr.length; i++) {
                tr[i].style.display = 'none';
                var td = tr[i].getElementsByTagName('td');
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        if (td[j].innerHTML.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            break;
                        }
                    }
                }
            }
        });

        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("dataTable");
            switching = true;
            dir = "asc"; 

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>
