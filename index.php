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
    </table>

</body>
</html>