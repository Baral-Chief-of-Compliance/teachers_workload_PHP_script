<?
// проверка есть ли в параметра url UID преподователя
  if (isset($_POST['name'])){
    $teachers_list_link = '<li class="breadcrumb-item"><a href="/">Список преподователей</a></li>';
    $teachers_list_link .= '<li class="breadcrumb-item active" aria-current="page">Результат поиска</li>';
  }else {
      if (isset($_GET["teacher-uid"])){
        $teachers_list_link = '<li class="breadcrumb-item"><a href="/">Список преподователей</a></li>';
        $sql = 'SELECT * FROM teachers WHERE UID = "' . htmlspecialchars($_GET['teacher-uid']) .'"';
        $result = $mysqli->query($sql);
        if ($result->num_rows > 0){
          $row = $result->fetch_assoc();
          $teacher_fio = $row['teacher'];
          $teacher_uid = $row['UID'];

          // проверка есть ли в параметрах url start_date и end_date
          if (isset($_GET['start_date']) and isset($_GET['end_date'])){
            $sql = 'SELECT * FROM periods WHERE start_date = "' . htmlspecialchars($_GET['start_date']) . '" and end_date = "' .htmlspecialchars($_GET['end_date']) .'"';
            $result = $mysqli->query($sql);

            if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $period_name = $row['pname'];
              $teachers_list_link .= '<li class="breadcrumb-item"><a href="/?teacher-uid=' . $teacher_uid . '">' .$teacher_fio .'</a></li>';
              $teachers_list_link .= '<li class="breadcrumb-item active" aria-current="page">'. $period_name .'</li>';
            }
          }else{
            $teachers_list_link .='<li class="breadcrumb-item active" aria-current="page">'. $teacher_fio .'</li>';
          }
        }
      }else{
        $teachers_list_link = '<li class="breadcrumb-item active" aria-current="page">Список преподователей</li>';
      }
  }
  echo('<nav aria-label="breadcrumb">');
  echo('  <ol class="breadcrumb">');
  echo($teachers_list_link);
  echo('  </ol>');
  echo('</nav>');
?>