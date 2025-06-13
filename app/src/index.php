<?
// подключения скрипта для взаимодействия с бд
require('config.php');
//подключением скрипта с подключенными через CDN библиотеками
require('header.php');
//подключаем скрипт с графиками;
require('chart.php');

echo '<div class="container-fluid">';

// Подключаем скрипт с breadcrumb 
// https://getbootstrap.com/docs/4.6/components/breadcrumb/
require('breadcrumb.php');
error_reporting(E_ERROR);


//форма поиска
echo '<form action="/" method="post">';
echo '  <div class="input-group mb-3">';
echo '      <input required class="form-control" type="text" id="name" name="name" placeholder="ФИО преподователя...">';
echo '      <div class="input-group-append">';
echo '          <button class="btn btn-outline-secondary" type="submit">Поиск преподователя</button>';
echo '      </div>';
echo '  </div>';
echo '</form>';

// Информация о результатах поиска
$post_teacher_name = htmlspecialchars($_POST['name']);
if (strlen($post_teacher_name)>0){
    $html_search_result = '<div><h4>Результат поиска по запросу: "%s"</h4></div>';
    echo sprintf($html_search_result, $post_teacher_name);
}

//вывдо всех учетелей из базы
$sql = 'SELECT * FROM teachers';

// если пользователь ищет препода, то идет дополнения sql скрипта с условием введенных данных
if (strlen($post_teacher_name)>0){
    $sql.=' WHERE teacher LIKE "%' . $post_teacher_name .'%"';
}

// условие для отображение информации либо о всех преподах, либо о конкретном преподе
if (!htmlspecialchars($_GET["teacher-uid"])){
    $sql.=' ORDER BY teacher';
    $result = $mysqli->query($sql);
    // проверка на кол-во найденых преподователей
    if ($result->num_rows > 0) {
        // основа для таблице, в которой будет список преподователей
        echo '<div class="table-responsive" style="height: 80%; overflow-y: auto;">';
        echo '<table class="table table-hover mt-5 table-striped text-center">';
        echo '<thead><tr><th scope="col">ФИО преподователя</th></tr></thead>';
        echo '<tbody>';

        // Обходим всех преподователей, которые мы получили из нашего sql запроса
        while($row = $result->fetch_assoc()) {
            $teacher_name = $row['teacher'];
            $teacher_UID = $row['UID'];

            // используем регулярное выражение, так как в базе есть преподы с именем -----------
            // поэтому не отображаем их 
            if (preg_match('%[a-zA-Zа-яА-Я]%', $row['teacher'])){
                //на странице отображаем карточки преподов, при переходе по ним получаем инфу про препода
                $html = '<tr><th><a href="/?teacher-uid=%s"><div>%s</div></a></th></tr>'; 
                echo sprintf($html,  $teacher_UID, $teacher_name );
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="h3 mt-5 text-center text-secondary"><em>К сожалению, ни один преподователь не найден</em></p>';
    }
} else {
    $sql = 'SELECT * FROM teachers WHERE UID = "' . htmlspecialchars($_GET['teacher-uid']) .'"';
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $html_teacher_info = '<h3>Преподователь: %s</h3>';
        $teacher_uid = $row['UID'];
        $html_teacher_info = sprintf($html_teacher_info, $row['teacher']);
        // вывод информации о преподе
        echo ($html_teacher_info);

        $sql = 'SELECT * FROM periods ORDER BY end_date DESC';
        $result = $mysqli->query($sql);
        
        // условие отрисовки периодов, если они не выбраны, нет в query params
        if (!htmlspecialchars($_GET['start_date']) and !htmlspecialchars($_GET['end_date'])){
            // отрисовка таблицы с периодами обучения
            if ($result->num_rows > 0) {
                echo '<div class="table-responsive" style="height: 75%; overflow-y: auto;">';
                echo '<table class="table table-hover mt-5 table-striped text-center">';
                echo('<thead><tr><th scope="col">Нагрузка преподователя</th><th scope="col">Дата начала</th><th scope="col">Дата конца</th><th scope="col">Чет / Нечет</th></tr></thead>');
                echo('<tbody>');
                // Отрисовка периодов, которые мы достаем из базы
                while($row = $result->fetch_assoc()) {
                    $period_id = $row['pid'];
                    $period_start_date = $row['start_date'];
                    $period_end_date = $row['end_date'];
                    $period_start_date_table = explode('-',$row['start_date']);
                    $period_start_date_table = sprintf('%s.%s.%s', $period_start_date_table[2], $period_start_date_table[1], $period_start_date_table[0]);
                    $period_end_date_table = explode('-',$row['end_date']);
                    $period_end_date_table = sprintf('%s.%s.%s', $period_end_date_table[2], $period_end_date_table[1], $period_end_date_table[0]);
                    $period_kind = $row['kind'];
                    $period_name = $row['pname'];
                    $html_period_row = '<tr><td><a href="/?teacher-uid=%s&start_date=%s&end_date=%s">Нагрузка</a></td><td>%s</td><td>%s</td><td>%s</td></tr>';
                    $html_period_row = sprintf($html_period_row, $teacher_uid, $period_start_date, $period_end_date,  $period_start_date_table, $period_end_date_table , $period_kind);
                    echo($html_period_row);
                }
                echo('</tbody');
                echo('</table>');
                echo('</div>');
            } else {
                echo '<p class="h3 mt-5 text-center text-secondary"><em>К сожалению, периоды не найдены в системе.</em></p>';
            }
        } else {

            // запрос за периодом в базу
            // Вывод информации о нагрузке преподавателя за выбранный период
            $sql = 'SELECT * FROM periods WHERE start_date = "' . htmlspecialchars($_GET['start_date']) . '" and end_date = "' .htmlspecialchars($_GET['end_date']) .'"';
            $result = $mysqli->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $html_period_info = $row['kind'] == 'н' ? '<h4>Нагрузка за нечетный период: ' : '<h4>Нагрузка за четный период: ';
                $html_period_info.= $row['pname']  . '</h4>';
                echo($html_period_info);
                
                                
                // получить информацию  по парам за период
                $start_date = $row['start_date'];
                $end_date = $row['end_date'];

                //получить количество пар за период
                $sql = sprintf("select count(*) as pair_count FROM schedule where schedule.pair_date between '%s' and '%s' and schedule.UID_t = '%s'", $start_date, $end_date, $teacher_uid);
                $result = $mysqli->query($sql);
                $row = $result->fetch_assoc();
                echo(sprintf('<h4>Количество пар за период: %s</h4>',$row['pair_count']));
                

                // sql запрос по количество дисциплин за период
                $sql = sprintf("
                SELECT schedule.pair_date as s_pd, schedule.pair as s_p, schedule.day_of_week as s_dow,
                schedule.pair_type as s_pt, courses.course as cource, disciplines.disc as disc,
                rooms.room as room, rooms.room_type as room_type, buildings.building as building,
                facultees.facultee as facultee, `groups`.group as gp, `groups`.speciality as sp
                FROM schedule 
                left join courses on schedule.course_id = courses.course_id
                left join disciplines on schedule.disc_id = disciplines.disc_id
                left join rooms on schedule.room_id = rooms.room_id
                left join buildings on rooms.bui_id = buildings.bui_id
                left join facultees on schedule.fac_id = facultees.fac_id
                left join `groups` on schedule.UID_g = `groups`.UID
                where schedule.pair_date between '%s' and '%s' and schedule.UID_t = '%s' ORDER BY schedule.pair_date DESC", $start_date, $end_date, $teacher_uid);

                $result = $mysqli->query($sql);

                //Орисовать график нагрузки преподователя
                //для этого будет использована библиотека chart.js, которую мы подключали в header.php через cdn

                //график по количеству пар за дни недели периода
                $sql_count_pair_in_day_of_week = sprintf("
                select 
                count(CASE WHEN schedule.day_of_week ='ПН' THEN 1 END) as monday, 
                count(CASE WHEN schedule.day_of_week = 'Вт' THEN 1 END) as tuesday,
                count(CASE WHEN schedule.day_of_week = 'Ср' THEN 1 END) as wednesday,
                count(CASE WHEN schedule.day_of_week = 'Чт' THEN 1 END) as thursday,
                count(CASE WHEN schedule.day_of_week = 'Пт' THEN 1 END) as friday,
                count(CASE WHEN schedule.day_of_week = 'Сб' THEN 1 END) as saturday
                FROM schedule where schedule.pair_date between '%s' and '%s' and schedule.UID_t = '%s'
                ", $start_date, $end_date, $teacher_uid);

                $result_pair_in_day_of_week = $mysqli->query($sql_count_pair_in_day_of_week);
                $row_pair_in_day_of_week = $result_pair_in_day_of_week->fetch_assoc();

                echo sprintf($day_of_week_chart, $row_pair_in_day_of_week['monday'], $row_pair_in_day_of_week['tuesday'], $row_pair_in_day_of_week['wednesday'], $row_pair_in_day_of_week['thursday'], $row_pair_in_day_of_week['friday'], $row_pair_in_day_of_week['saturday']);


                //график по количеству пар по типу пар 1-7 пара
                $sql_count_type_pair = sprintf("
                select 
                count(CASE WHEN schedule.pair = 1 THEN 1 END) as 1_pair,
                count(CASE WHEN schedule.pair = 2 THEN 1 END) as 2_pair,
                count(CASE WHEN schedule.pair = 3 THEN 1 END) as 3_pair,
                count(CASE WHEN schedule.pair = 4 THEN 1 END) as 4_pair,
                count(CASE WHEN schedule.pair = 5 THEN 1 END) as 5_pair,
                count(CASE WHEN schedule.pair = 6 THEN 1 END) as 6_pair,
                count(CASE WHEN schedule.pair = 7 THEN 1 END) as 7_pair
                FROM schedule where schedule.pair_date between '%s' and '%s' and schedule.UID_t = '%s'
                ", $start_date, $end_date, $teacher_uid);

                $result_count_type_pair = $mysqli->query($sql_count_type_pair);
                $row_count_type_pair = $result_count_type_pair->fetch_assoc();

                echo sprintf($count_pair_type, $row_count_type_pair['1_pair'], $row_count_type_pair['2_pair'], $row_count_type_pair['3_pair'], $row_count_type_pair['4_pair'], $row_count_type_pair['5_pair'], $row_count_type_pair['6_pair'], $row_count_type_pair['7_pair']);


                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive" style="height: 75%; overflow-y: auto;">';
                    echo '<table class="table table-hover mt-5 table-striped text-center">';
                    echo('
                    <thead>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">День недели</th>
                            <th scope="col">Пара</th>
                            <th scope="col">Тип</th>
                            <th scope="col">Курс</th>
                            <th scope="col">Дисциплина</th>
                            <th scope="col">Кабинет</th>
                            <th scope="col">Тип Кабинета</th>
                            <th scope="col">Корпус</th>
                            <th scope="col">Факультет</th>
                            <th scope="col">Группа</th>
                            <th scope="col">Специальность</th>
                        </tr>
                    </thead>');
                    echo('<tbody>');
                    while($row = $result->fetch_assoc()) {
                        $html_row = "
                        <tr>
                            <td style='min-width: 120px'>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                        </tr>
                        ";
                        $date = explode('-',$row['s_pd']);
                        $date = sprintf('%s.%s.%s', $date[2], $date[1], $date[0]);
                        $html_row = sprintf($html_row, $date, $row['s_dow'], $row['s_p'], $row['s_pt'], $row['cource'], $row['disc'], $row['room'], $row['room_type'], $row['building'], $row['facultee'], $row['gp'], $row['sp']);
                        echo($html_row );
                    }
                    echo('</tbody');
                    echo('</table>');
                    echo('</div>');
                }else{
                    echo '<p class="h3 mt-5 text-center text-secondary"><em>Пар за этот период у данного преподавателя нет.</em></p>';
                }

            } else{
                echo '<p class="h3 mt-5 text-center text-secondary"><em>К сожалению, периоды не найдены в системе.</em></p>';
            }
        }

    } else{
        echo 'Преподователя, которого вы ищете нет в системе';
    }
}


echo '</div>';
?>