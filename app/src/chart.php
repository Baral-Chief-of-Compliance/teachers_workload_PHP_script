<?
    // основа для графика, который отображает информацию о кол-ве пар в каждыйй день недели периода
    // база графика взята с https://www.chartjs.org/docs/latest/getting-started/
    $day_of_week_chart = "
    <div>
        <canvas height='40vh' id='dayOfWeekChart'></canvas>
    </div>
    <script>
        const ctx1 = document.getElementById('dayOfWeekChart');

        new Chart(ctx1, {
            type: 'bar',
            data: {
            labels: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
            datasets: [{
                label: 'Количество пар за период по дням недели',
                data: [%d, %d, %d, %d, %d, %d],
                borderWidth: 1
            }]
            },
            options: {
            scales: {
                y: {
                beginAtZero: true
                }
            }
            }
        });
        </script>
    ";

    //график, который отображает кол-во какими парами у преподавателя происходят занятия
    $count_pair_type = "
    <div>
        <canvas height='40vh' id='countPairType'></canvas>
    </div>
    <script>
        const ctx2 = document.getElementById('countPairType');

        new Chart(ctx2, {
            type: 'bar',
            data: {
            labels: ['1 пары', '2 пары', '3 пары', '4 пары', '5 пары', '6 пары', '7 пары'],
            datasets: [{
                label: 'Количество типов пар у преподователя',
                data: [%d, %d, %d, %d, %d, %d, %d],
                borderWidth: 1
            }]
            },
            options: {
            scales: {
                y: {
                beginAtZero: true
                }
            }
            }
        });
        </script>
    ";

?>