<?php
$_POST = json_decode(file_get_contents('php://input'), true);
// $filename = $_GET['filename'];
// $str = '{ "params": [ { "timerange": { "starttime": "2018/01/18 00:00:00", "endtime": "2020/01/24 14:24:35" }, "filter": { "event": [ "Зарегистрирован проход.", "Зарегистрирован проход, санкционированный с кнопки." ], "object": [ "Администрация"], "person": [ "Виталий Трубной", "-" ] }, "grouping": { "name": "по объектам", "argument": "obj" }, "type": "Пользовательская выборка 1" }, { "timerange": { "starttime": "2020/01/23 00:00:00", "endtime": "2020/01/25 23:59:59" }, "filter": { "event": [ "Доступ запрещен." ], "object": [ "Администрация" ] }, "grouping": { "name": "по объектам", "argument": "obj" }, "type": "Пользовательская выборка 2" } ] }';

// $str = $_POST['str'];
// $arraydb["resp"] = system("python3 report.py ".$str."");//$str;//system("python3 report.py ".$str."");
// exec("python3", $arraydb["1"], $arraydb["2"]);

// $str = ('Лохпидр');
// mb_substr($str, 0, 7, "UTF-8")

// $command = escapeshellcmd('/var/www/html/php/report.py');
// $output = shell_exec($command);
// echo utf8_encode(':)');
// echo mb_detect_encoding($str);
// putenv('LANG=en_US.UTF-8');
// echo shell_exec('LANG=\"ru_RU.UTF-8\" '.$command);

// $str = $_POST['str'];
// unlink('./report.pdf');
// $arraydb['str'] =  $_POST;
// $python =  shell_exec("LANG=\"ru_RU.UTF-8\" /usr/bin/python3 /var/www/html/php/report.py '".$str."' 2>&1");

/////////////////////////////////////////////////////////////////////////////////
// unlink('./report.pdf');
// unlink('./old.xlsx');
$arraydb['str'] =  $_POST;
// if($_POST['pdf'])
    $msg = $_POST['str'];
// else {
//     $excelObj = json_decode($_POST['str']);
//     return;
// }
// $msg = '{"operation":"create PDF","params":[{"timerange":{"starttime":"2019/04/16 00:00:00","endtime":"2020/04/16 11:40:00"},"filter":{"object":["Дет.сад вишенка"]},"grouping":{"name":"по объектам","argument":"device"},"type":"Отчет с 16.04.2019 00:00 по 16.04.2020 11:40","indexName":"acs_castle_ep2_event"}]}';

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, 'localhost', 9310);

$len = strlen($msg);
//envio informacion a socket
$sendMsg = socket_send($socket, $msg, $len, MSG_DONTROUTE);
 //now you can read from...
$python = trim(socket_read($socket, 100));
//cierro conexion iniciada
socket_close($socket);
/////////////////////////////////////////////////////////////////////////////////

// echo $python;
$arraydb['python'] = $python;
echo json_encode($arraydb);

// {
//     "operation":"create PDF",
//     "params":[
//            {
//                "timerange":{
//                    "starttime":"2019/04/16 00:00:00",
//                    "endtime":"2020/04/16 11:40:00"
//                },
//                "filter":{
//                    "object":["Дет.сад вишенка"]
//                },
//                "grouping":{
//                    "name":"по объектам","argument":"device"
//                },
//                "type":"Отчет с 16.04.2019 00:00 по 16.04.2020 11:40",
//                "indexName":"acs_castle_ep2_event"
//            }
//        ]
//    }
//    "operation":"create EXCEL",	// команда - Создай эксель (стандартно для всех команд)
//        "params":{			// параметры команды
//            "paramstable":[		// параметры таблиц которые мы хотим внести в отчет
//                {
//                "timerange":{"starttime":"2019/04/16 00:00:00","endtime":"2020/04/16 11:40:00"},	// временные рамки (обязательно)
//                "filter":{"person":["Артем Артишев"]},	// фильтры (НЕобязательно, можно оставить "filter":{})
//                "field":["object","pass_number","time","route","person","significance","event","ip_device","device"],	// поля, которые нужно указать в таблице
//                "grouping":{"field":"time", "trend":"asc"},	// групировка данных в таблице, по какому полю и в каком направление
//                "pagename":"Отчет с 16.04.2019",	// название страницы в эксель файле
//                "indexname":"acs_castle_ep2_event"	// из какой таблицы вобще данные
//                }
//            ],
//            "filename":"Отчет с 20.01.2020" // название файла
//        }
   

?>
