<?php
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

//header("Location: index.php"); exit();
setcookie("login", '', time()+60*60*24*30);
setcookie("hash", '', time()+60*60*24*30);
setcookie("admin", '', time()+60*60*24*30);


$conn = connectBD();
$need_login = false;
// echo var_dump($_COOKIE["id"]);
// echo var_dump($_COOKIE["idd"]);
if($_COOKIE["login"] != NULL){
    //соединение с БД
    $query = pg_query($conn, "SELECT * FROM users WHERE login = '".$_COOKIE["login"]."'");
    $data = pg_fetch_assoc($query);
    // echo var_dump($_COOKIE["hash"]);
    // echo var_dump($data["user_hash"]);
    if($_COOKIE["hash"] == $data["user_hash"]){
        header("Location: index.php"); exit();
    }
    
}

    # Функция для генерации случайной строки

    $login_error_message ='';

    function generateCode($length=6) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";

        $code = "";

        $clen = strlen($chars) - 1;  
        while (strlen($code) < $length) {

                $code .= $chars[mt_rand(0,$clen)];  
        }

        return $code;
    }
    


    if(isset($_POST['submit']))

    {
        $login =  $_POST['login'];
        $password = $_POST['password'];
        # Вытаскиваем из БД запись, у которой логин равняется введенному

        //$query = mysqli_query($link, "SELECT user, pass, id FROM users WHERE user='".mysqli_escape_string($link, $_POST['login'])."' LIMIT 1");
        $query = pg_query($conn, "SELECT * FROM users WHERE login = '".$login."'");
        
        $data = pg_fetch_assoc($query);
        // echo var_dump($data);
        # Соавниваем пароли

        if($data['pass'] === hash('sha1', $password))

        {
            # Генерируем случайное число и шифруем его

            $hash = md5(generateCode(10));   
            // echo var_dump($hash);
            if(!@$_POST['not_attach_ip'])
            {
                # Если пользователя выбрал привязку к IP

                # Переводим IP в строку

                $insip = ", user_ip=INET_ATON('".$_SERVER['REMOTE_ADDR']."')";
            }

            

            # Записываем в БД новый хеш авторизации и IP
            // pg_query($conn, "UPDATE users SET user_hash='".$hash."' ".$insip." WHERE id='".$data['id']."'");
            pg_query($conn, "UPDATE users SET user_hash='".$hash."' WHERE login='".$data['login']."'");


            $query = pg_query($conn, "SELECT * FROM users WHERE login = '".$login."'");

            $data = pg_fetch_assoc($query);
            // echo var_dump($data);


            // echo var_dump($data["user_hash"]===$hash);

            # Ставим куки

            setcookie("login", $data['login'], time()+60*60*24*30);

            setcookie("hash", $hash, time()+60*60*24*30);
            if($data['rights']=="")
            setcookie("admin", "nope", time()+60*60*24*30);
            else
            setcookie("admin", "yes", time()+60*60*24*30);
            

            # Переадресовываем браузер на страницу проверки нашего скрипта

            header("Location: index.php"); exit();

        }

        else

        {

            $login_error_message = "<br><span class='error'>Вы ввели неправильный логин/пароль</br>";

        }

    } else {
        //print "херня какаято";
    }
   
    pg_close($conn);

    ?>

    <!DOCTYPE html>
    <html lang="ru">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAVA авторизация</title>
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/jquery.ipmasktext.js"></script>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/png" href="./assets/images/favicon.png" />
    <style>
        body{
            background: #f2f2f2;
        }
        
        </style>
    </head>


    <body>
    

    <div class="login-win">
        <form class="login-win__form" method="POST">
            <p>
                <span class="sava">SAVA</span>
                <img src="assets/images/header__logo.PNG" width="164" height="48">
            </p>
            <p>
                <span class="login">ЛОГИН:</span>			
                <input class="input_gray" name="login" type="text">
            </p>
            <p>
                <span class="login">ПАРОЛЬ:</span>
                <input class="input_gray" name="password" type="password">
            </p>
            <p>
                <input class="button_red" name="submit" type="submit" value="Войти" >
                <?php
                    if (strlen($login_error_message) > 5)
                    echo $login_error_message;
                ?>
            </p>            
        </form>
    </div>


    <!-- <table>
        <tr>
            <td width=100px>
                <span class="sava">SAVA.</span>
            </td>
            <td width=300px align=left>
                <img src="assets/images/header__logo.PNG">
            </td>
        </tr>
        <tr  style="vertical-align: baseline;">
            <td>
                <form method="POST">

                <span class="login">ЛОГИН: &nbsp;&nbsp;</span>			
                
            </td>
            <td>
                <input class="input_text" name="login" type="text">
            </td>
        </tr>
        <tr  style="vertical-align: baseline;">
            <td>
                
                <span class="login">ПАРОЛЬ:  </span>
            </td>
            <td>
                <input class="input_text" name="password" type="password">
            </td>
         
            
        <tr>
            <td>
            </td>
            <td>
                <input class="knopka" name="submit" type="submit" value="Отправить" >
                </form>
          
            </td>
        </tr>
  
    </tr>
    <table> -->
    </div>
    </body>
    </html>
