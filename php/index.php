<?php
// setcookie("id", '6666', time()+60*60*24*30);
// setcookie("hash", '7777', time()+60*60*24*30);
// system("echo 'pool !!! iburst '>>var/NN_SAVA/mail.ini");


// curl -H "Content-Type: application/json" -XPOST 'http://localhost:9200/firmwarevers/main/_delete_by_query' -d '{ "query" : { "term" : { "ip_cam" : "192.168.3.109" } } }'

function connectBD() 
{
    $conn = pg_connect("host=127.0.0.1 port=5432 dbname=cam_db user=cam_db_user password=test");
    if (!$conn) {
        echo "Waring!\n";
        exit;
    }
    return $conn;
}
// Страница авторизации

//  echo var_dump($_COOKIE["ip"]);
// echo $_COOKIE["ip"];
// echo "auiiii";

$conn = connectBD();

// echo var_dump($_COOKIE["id"]);
// echo var_dump($_COOKIE["idd"]);

//соединение с БД
$query = pg_query($conn, "SELECT * FROM users WHERE login = '".$_COOKIE["login"]."'");
$data = pg_fetch_assoc($query);
// echo var_dump($_COOKIE["hash"]);
// echo var_dump($data["user_hash"]);
// echo var_dump($_COOKIE["id"]);
if($_COOKIE["hash"] != $data["user_hash"] || ($_COOKIE["login"] == NULL)){
    header("Location: login.php"); exit();
}
    



// echo "eeeee";
if($_POST['exit']){
    setcookie("id", '', time()+60*60*24*30);
    setcookie("hash", '', time()+60*60*24*30);
    $new_url = 'login.php';
    header('Location: '.$new_url);
}
// $new_url = 'login.php';
// header('Location: '.$new_url);
?>

<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SAVA</title>
  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/jquery.ipmasktext.js"></script>
  <script src="js/Chart.min.js"></script>
  <script src="js/utils.js"></script>
  <script src="js/graph.js"></script>
  <script src="js/cookies.js"></script>
  <script src="js/core.js"></script>
  <script src="js/cameras_core.js"></script>

  <!-- <script src="js/index.js"></script> -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" type="image/png" href="./assets/images/favicon.png" />
</head>
 
<body>
    <div class="modal-bg modal-bg__lic">
                        <div class="modal-win">
                            <header class="modal-win__header"> 
                                <span  class="w3-button w3-display-topright">×</span>
                                <h3>Срок действия лицензии истек</h3>
                            </header> 
                </div>                      
    </div>
    <div class="wrapper">
        <header class="header">
            <img class="header__logo" src="./assets/images/header__logo.PNG" atl="logo">
            <ul  class="header__items">
                <li class="header__dashboards">
                    <a >
                        <img src="assets/images/header__item1.PNG">
                        <div>
                            <p>Панель мониторинга</p>
                            <!-- <p>Dashboards</p> -->
                        </div>
                    </a>
                </li>
                
                <li class="header__settings">
                    <a >
                        <img src="assets/images/header__item3.PNG">
                        <div>
                            <p>Настройки</p>
                            <!-- <p>Settings</p> -->
                        </div>
                    </a>
                </li>
            </ul>
            <div class="header__user-info">
                <p></p>
                <p></p>
                <form method="post">
                    <input id="exit_but" name="exit" type="submit" >
                    <label for="exit_but" class="header__button"></label>
                </form>
            </div>
            
        </header>
        <main>
            <div class="settings">
            <?php
if($_COOKIE["admin"]=="yes"){
?>
<aside class="aside-panel">
            <menu>
                <ul>
                    <li  class="aside-panel_cameras-control">
                                <img src="assets/images/aside__item6.png" width="68" height="68" alt="cameras-control ">
                                <p>Настройка камер</p>
                            </li>
                    <li class="aside-panel_users-control   aside-panel__item_active">
                        <img src="assets/images/aside__item1.png" width="59" height="59" alt="users-control">
                        <p>Управление <br>пользователями</p>
                    </li>
                    <li class="aside-panel_timezone">
                            <img src="assets/images/aside__item2.png" width="48" height="43" alt="panel_timezone">
                            <p>Часовой<br>пояс</p>
                    </li>
                    <!-- aside-panel__item_active -->
                    <li class="aside-panel_net-settings ">
                            <img src="assets/images/aside__item3.png" width="68" height="47" alt="aside-panel_net-settings">
                            <p>Сетевые<br>настройки</p>
                    </li>
                    <li class="aside-panel_notification-server">
                            <img src="assets/images/aside__item4.png" width="68" height="60" alt="notification-server">
                            <p>Сервер отправки<br>уведомлений</p>
                    </li>
                    <li  class="aside-panel_license">
                            <img src="assets/images/aside__item5.png" width="68" height="60" alt="panel_license">
                            <p>Лицензия</p>
                    </li>
                    
                </ul>
            </menu>
        </aside>
<?php } 
else{
    ?>
    <aside class="aside-panel  aside-panel_rip">
            <menu>
                <ul>
                    <li  class="aside-panel_cameras-control  aside-panel__item_active">
                                <img src="assets/images/aside__item6.png" width="68" height="68" alt="cameras-control ">
                                <p>Настройка камер</p>
                            </li>
                    <li class="aside-panel_users-control " >
                        <img src="assets/images/aside__item1.png" width="59" height="59" alt="users-control">
                        <p>Управление <br>пользователями</p>
                    </li>
                    <li class="aside-panel_timezone">
                            <img src="assets/images/aside__item2.png" width="48" height="43" alt="panel_timezone">
                            <p>Часовой<br>пояс</p>
                    </li>
                    <!-- aside-panel__item_active -->
                    <li class="aside-panel_net-settings ">
                            <img src="assets/images/aside__item3.png" width="68" height="47" alt="aside-panel_net-settings">
                            <p>Сетевые<br>настройки</p>
                    </li>
                    <li class="aside-panel_notification-server">
                            <img src="assets/images/aside__item4.png" width="68" height="60" alt="notification-server">
                            <p>Сервер отправки<br>уведомлений</p>
                    </li>
                    <li  class="aside-panel_license">
                            <img src="assets/images/aside__item5.png" width="68" height="60" alt="panel_license">
                            <p>Лицензия</p>
                    </li>
                    
                </ul>
            </menu>
        </aside>
    <?php
}
?>
                
                <div class="explorer users-control">
                    <header class="explorer__header">
                        <p>Список пользователей</p>
                    </header>

                    <div class="modal-bg modal-bg__add">
                        <div class="modal-win">
                            <header class="modal-win__header"> 
                                <span  class="w3-button w3-display-topright">×</span>
                                <h3>Добавить пользователя</h3>
                            </header>
                        <form class="explorer__creater" id="loginform" method="post">
                            
                            <p>
                                имя нового пользователя
                                <input class="input_gray" type="text" name="_login" size="15"/>
                            </p>
                            <p>
                                пароль
                                <input class="input_gray" type="password" name="_password" size="15"/>
                            </p>
                            <p>
                                повторите пароль
                                <input class="input_gray" type="password" name="_password-repeat" size="15"/>
                            </p>      
                            <p>
                                <img src="assets/images/admin.svg">
                                <label for="explorer__admin" > администратор</label>
                                <input id="explorer__admin" type="checkbox" name="_admin"/>
                            </p>
                            <input class="button_red" data="ajax" type="submit" name="addUser" value="Добавить" />
                        </form>  
                            </div>                      
                    </div>
                    <div class="modal-bg modal-bg__del">
                        <div class="modal-win">
                            <header class="modal-win__header"> 
                                <span class="w3-button w3-display-topright">×</span>
                            <h3>Удалить пользователя?</h3>
                            </header>
                            
                           
                            <form action="settings_admin.php" method="POST">
                                <input class="button_red" data="ajax" name="deletedb" type="submit" value="Удалить"> 
                                <button class="button_red" >Отмена</button>
                                <input type="hidden" name="usr_del" value="">
                            </form>
                        </div>
                    </div>
                    <div class="modal-bg modal-bg__change">
                            <div class="modal-win">
                                <header class="modal-win__header"> 
                                    <span  class="w3-button w3-display-topright">×</span>
                                <h3>Изменение пароля</h3>
                                </header>
                                <form action="settings_admin.php" method="POST">
                                    <p>Старый пароль <input type="password" class="input_gray" name="current_pass"></p>
                                    <p>Новый пароль	<input type="password" class="input_gray" name="new_pass"></p>
                                    <p>Еще раз <input type="password" class="input_gray" name="conf_pass"></p>
                                    <p><input class="button_red" name="change_pass" data="ajax"  type="submit" value="Изменить"></p>
                                    <input type="hidden" name="usr" value="admin">
                                </form>
                            </div>
                    </div>
                </div>
                <div class="timezone">
                        <form action="settings_admin.php" method="POST">
                        <span class="settings_text">Настройки часового пояса<br><br></span>
                        
                        <p>
                            <label>NTP server1:<input name="ntp_server1" type="text" value="0.ubuntu.pool.ntp.org "></label>
                        </p>
                        <p>
                            <label>NTP server2:<input name="ntp_server2" type="text" value="1.ru.pool.ntp.org "></label>
                        </p>
                        <p>
                            <label>NTP server3:<input name="ntp_server3" type="text" value="2.ubuntu.pool.ntp.org "></label>
                        </p>
                        <p>
                            <label>NTP server4:<input name="ntp_server4" type="text" value="3.ru.pool.ntp.org "></label>
                        </p>	
                            <input class="button_red" name="ntp" type="submit" value="Сохранить">
                        
                        </form>
                </div>
                <div class="net-settings">
                        <form align="center" action="settings_admin.php" method="POST">
                                <span class="settings_text">Настройки сети<br><br></span>		
                                <p>
                                    <label for="ip">
                                        IP:
                                    </label>
                                    <input  type="text" name="ip" value="192.168.3.242">
                                   
                                
                                </p>
                                <p>
                                    <label for="mask">
                                        Mask:
                                        
                                    </label>
                                    <input id="mask" name="mask" type="text" value="255.255.255.0">
                                </p>
                                <p>
                                    <label for="gw">
                                        Gateway:
                                </label>
                                <input id="gw" name="gw" type="text" value="192.168.0.191">
                                </p>	
                                <input class="button_red" name="network" type="submit" value="Сохранить">
                        </form>
                </div>
                <div class="notification-server">
                    <form align="center" action="settings_admin.php" method="POST">
                        <span class="settings_text">Настройки уведомлений<br><br></span>		
                        <p>
                            <label for="notification-server__mail-to">Адрес на который отправлять уведомление</label>
                            <input  id="notification-server__mail-to" name="to" size="40" type="text" value="shabanov.andrew@arinteg.ru">    
                        </p>
                        <p>
                            <label for="notification-server__mail-from">Адрес с которого отправлять уведомление</label>
                            <input id="notification-server__mail-from" name="from" size="40" type="text" value="shabanov.andrew@arinteg.ru">
                        </p>
                        <input class="button_red" name="smtp" type="submit" value="Сохранить">
                    </form>
                </div>
                <div class="license">

                        <?php
	
                            $lines = file("./lic/licence.lic");
                            
                            $lic = $lines[count($lines)-2];
                            
                            $index = count($lines)-2;
                            $key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
                            for($i = 1; $i<$index; $i++)
                                $key = $key.$lines[$i];
                            $key = $key."-----END PRIVATE KEY-----".PHP_EOL;

                            openssl_private_decrypt(base64_decode($lic), $result, $key);
                            // echo $result;
                            
                            $times = explode("|", $result);    
                            
                            $today = time();    
                            $day_last = ($times[1] - $today)/86400;                    
                        ?>
                        <form align=center enctype="multipart/form-data" action="settings_admin.php" method="POST">
                            <div>
                                <span class='settings_text'>Лицензия</br></br></span>	
                                <?php
                                
                                echo "<p>Дата начала лицензии: ".gmdate("Y-m-d\ H:i:s", $times[0])."</p>";
                                echo "<p>Дата окночания лицензии: ".gmdate("Y-m-d\ H:i:s", $times[1])."</p>";
                                echo "<p>Срок лицензии: ".(($times[1]-$times[0])/86400)."</p>";
                                echo "<p class=\"license__remain-time\" >Осталось дней: ".(int)$day_last."</p>";
                                ?>
                                
                                <!-- Поле MAX_FILE_SIZE должно быть указано до поля загрузки файла -->
                                <p><input type="hidden" name="MAX_FILE_SIZE" value="30000" /></p>
                                <!-- Название элемента input определяет имя в массиве $_FILES -->
                                <!-- <p>Загрузить файл лицензии: <input name="userfile" type="file" /></p> -->
                                <p>Загрузить файл лицензии:
                                    <label for="myfile" class="custom-file-input__label button_red">Выберите файл</label>
                                    <input type="file" class="custom-file-input" id="myfile" name="userfile" multiple>
                                </p>
                                <p><input class="button_red" type="submit" name="lic" value="Загрузить" /></p>
                            </div>
                        </form>
                </div>
                <div class="explorer cameras-control">
                        <header class="explorer__header">
                                <p>Список регистраторов</p>
                        </header>
                        
                        <header class="explorer__header">
                                <p>Список камер</p>
                        </header>
                        
 
                        <div class="modal-bg modal-bg__add">
                            <div class="modal-win">
                                <header class="modal-win__header"> 
                                    <span  class="w3-button w3-display-topright">×</span>
                                    <h3>Удалить камеру?</h3>
                                </header>
                                <form class="explorer__creater" method="post">
                                    <p>
                                        ip
                                    <input id="explorer__cameras-ip"  class="input_gray" type="text" name="_ip" size="15"/>
                                    <script type="text/javascript">
                                        $('#explorer__cameras-ip').ipmask();
                                    </script>
                                    </p>
                                    <p>
                                        логин
                                        <input class="input_gray" type="text" name="_login" size="15"/>
                                    </p>
                                    <p>
                                        пароль
                                        <input class="input_gray" type="password" name="_password" size="15"/>
                                    </p>
                                    <p>
                                        повторите пароль
                                        <input class="input_gray" type="password" name="_password-repeat" size="15"/> 
                                    </p>

                                    <input class="button_red" data="ajax"  type="submit" name="addCameraDb" value="Добавить" />
                                </form>  
                            </div>                      
                        </div>
                        <div class="modal-bg modal-bg__del">
                                <div class="modal-win">
                                    <header class="modal-win__header"> 
                                        <span  class="w3-button w3-display-topright">×</span>
                                    <h3>Удалить камеру?</h3>
                                    </header>
                                    
                                    <form action="settings_admin.php" method="POST">
                                        <input class="button_red" name="deletedb" data="ajax"  type="submit" value="Удалить"> 
                                        <button class="button_red" >Отмена</button>
                                        <input type="hidden" name="_ip" value="">
                                    </form>
                                </div>
                        </div>
                        <div class="modal-bg modal-bg__change">
                                <div class="modal-win">
                                    <header class="modal-win__header"> 
                                        <span  class="w3-button w3-display-topright">×</span>
                                    <h3>Изменение пароля</h3>
                                    </header>
                                    <form action="settings_admin.php" method="POST">
                                        <!-- <p>ip <input class="input_gray" name="_ip"></p>
                                        <p>Логин	<input class="input_gray" name="_login"></p> -->
                                        <p>Старый пароль <input type="password" class="input_gray" name="current_pass"></p>
                                        <p>Новый пароль	<input type="password" class="input_gray" name="new_pass"></p>
                                        <p>Еще раз <input type="password" class="input_gray" name="conf_pass"></p>
                                        <p><input class="button_red" name="change_cameras_pass" data="ajax"  type="submit" value="Изменить"></p>
                                        <input type="hidden" name="_ip" value="">
                                        <input type="hidden" name="_login" value="">
                                    </form>
                                </div>
                        </div>
                </div>
                
            </div>
           
            <div class="dashboards">
                <header class="dashboards__item-display">
                        Видеонаблюдение Dahua
                </header>
                <!-- <header class="dashboards__item-display">
                        <ul>
                            <li class="active">Kaspersky CSC</li>
                            <li>Dr. Web SSS</li>
                            <li>ESET ESMC</li>
                        </ul>
                </header> -->
                <div class="dashboards__dahua">
                    <div class="dashboards__form">
                        <p class="settings_text">
                            <!-- <span class="settings_text">Показать информацию за последние:  -->
                                <input class="select_gray" type="number"  value="7">
                                <select class="select_gray" name="time_shift" width="100px;">
                                            <option value="m">минут</option>
                                            <option value="h">часов</option>
                                            <option value="d" selected="">дней</option>
                                            <option value="w">недель</option>
                                            <option value="M">месяцев</option>
                                            <option value="y">лет</option>
                                </select>
                            <!-- </span> -->
                            <button class="button_red button_update " >Обновить</button>
                        </p>
                        <p class="settings_text">  
                            <!-- <span>
                                <span class="settings_text">Интервал обновления: 
                                </span> -->
                                <input class="select_gray" type="number"  value="1">
                                <select class="select_gray" name="time_shift" width="100px;">
                                            <option value="s">секунда</option>
                                            <option value="m" selected="">минута</option>
                                            <option value="h">час</option>
                                </select>
                            <!-- </span> -->
                            <button class="button_red button_autoupdateOn button_autoupdate">Выкл. автообновление</button>
                        </p>
                        
                    </div>
                    <div id="canvas-pie" style="width:40%">
                        <div class="chartjs-size-monitor"></div>
                        <canvas id="chart-area"></canvas>
                    </div>
                    <div id="canvas-bar" style="width:40%">
                        <canvas id="bar"></canvas>
                    </div>
                    
                <div class="dashboards__iframes-container">
                    


<!-- <iframe src="" height="650" width="1580"></iframe>
<iframe src="" height="650" width="1580"></iframe>
<iframe src="" height="650" width="1580"></iframe>
<iframe src="" height="650" width="1580"></iframe> -->


                    </div>
                </div>
            </div>
        </main>
    </div>
   
  <script src="js/app.js"></script>
</body>

</html>

        
     
<!-- ip 192.168.3.47
port 22
administrator
arinteg123!
administrator@FaceRecognition:/usr/share/kibana/src/legacy/ui/ui_render/
background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zd…MwLjgzODggMjMuNDA0MiwyNC4zMjY0IDE3LjM3NDIsMTkuOTk2OCIvPiAgPC9nPjwvc3ZnPg==); -->
<!-- background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACUAAAArCAYAAAD/lEFzAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAIrSURBVFhH7ZjPLwNBFMf9i+7OdRVXcXKRcMJRTyKpBJEIJxyEhIMmohxEqqcK8eOAyJ6Gz8bw3e3MzpS26WFf8klr+tZ+9r1nJjqSJIkZNkqpWEqpWDJS50c3ZmFi00yNVgfCXGUtvac6QEZqrlJzXtxPKII6QEbKddEgUAcopXyoA0RL7VRPTPOibZandzs+Y41oNx/N5UnLrC8cduQUoQ4QlJoZW0lvpuESI4/14+2GeXl4Nc/3r848F+oAQan6wfW3ym98vCVmfnwtk7exeGj2a/WfKvHzx3uSvmqeC3WAoBRP7ApaqXlLk1s/VUJ6dXYvXUOM95qbRx0gKNVs3H1rdEa+PUjQRiRslVKxL0nWNVdRBwhK0RJf8JnmXp220nVkaC/BKyOQz1XUAaIG3RV3t0/Op9eW0U6EeM8Y5HMt6gBBKcgPO3NT1A6qkpchEM7ngjpAlBQCPL0N5syVZ6FlCNkqs8Y1vi1CHSBKCnS2QoOr2Oowbz2XAubIBvPiyvFB9XwPog7QlRS/WNsYszHGoA7QlRQwKzZoo0uMltHuvh0zLhDRONu/TqvIZ3aztMEBHZo/dYA/SUFejODoUSEbbAu+7QDUAf4sBYjpjBUFsj3dp4rgRvpXWRS+o0Yd4N9SFt3HfDGwSin28HVF0fahDtBTKYuVY944XthGXHkWdYC+SHWLOkAp5UMdICM1lP+2D/4Ljlr4C45hoZSKpZSKpZSKZQilEvMJrVr8ueAK8ggAAAAASUVORK5CYII=");
.kibanaWelcomeText(data-error-message=i18n('common.ui.welcomeErrorMessage', { defaultMessage: 'Kibana did not load properly. Check the server output for more information.' }))
| #{i18n('common.ui.welcomeMessage', { defaultMessage: 'Loading SAVA...' })} -->
<!-- .kibanaWelcomeLogo {
    /*background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMCIgaGVpZ2h0PSIzOSIgdmlld0JveD0iMCAwIDMwIDM5Ij4gIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+ICAgIDxwb2x5Z29uIGZpbGw9IiNGMDRFOTgiIHBvaW50cz0iMCAwIDAgMzQuNTQ3IDI5LjkyMiAuMDIiLz4gICAgPHBhdGggZmlsbD0iIzM0Mzc0MSIgZD0iTTAsMTQuNCBMMCwzNC41NDY4IEwxNC4yODcyLDE4LjA2MTIgQzEwLjA0MTYsMTUuNzM4IDUuMTgwNCwxNC40IDAsMTQuNCIvPiAgICA8cGF0aCBmaWxsPSIjMDBCRkIzIiBkPSJNMTcuMzc0MiwxOS45OTY4IEwyLjcyMSwzNi45MDQ4IEwxLjQzMzQsMzguMzg5MiBMMjkuMjYzOCwzOC4zODkyIEMyNy43NjE0LDMwLjgzODggMjMuNDA0MiwyNC4zMjY0IDE3LjM3NDIsMTkuOTk2OCIvPiAgPC9nPjwvc3ZnPg==");*/
    background-image: url("data:image/png;base64,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");      background-repeat: no-repeat;
    background-size: contain;
    width: 60px;
    height: 60px;
    margin: 10px 0px 0px 20px;
    position: relative;
    left: -8px;
  } -->
