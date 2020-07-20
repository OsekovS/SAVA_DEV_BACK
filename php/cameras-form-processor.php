<?php
include 'db.php';
//подключение к БД
$conn = connectBD('cameras_dahua');
$_POST = json_decode(file_get_contents('php://input'), true);
// echo ':)';
if($_POST['need']=='settings'){
	$arraydb['object'] = getDB($conn,"SELECT id, name FROM object");
    $arraydb['cameras'] = getDB($conn,"SELECT id, name, obj, ip, login FROM cameras");
    $arraydb['registrators'] = getDB($conn,"SELECT id, name, obj, ip, login FROM registrators");

}

// if($_POST['need']=='settings'){
// 	$arraydb['objects'] = getDB($conn,"SELECT id, name FROM object_list");
//     $arraydb['cameras'] = getDB($conn,"SELECT id, name, obj, ip_cam, login FROM cameras_list");
//     $arraydb['registrators'] = getDB($conn,"SELECT id, name, obj, ip_reg, login FROM registrar_list");

// }

if(isset($_POST['addField']))
{    
    $fields = '';
    $fields= '';
    // $_POST['form']['aue'] = 'aui';
    foreach($_POST['form'] as $key => $value) {
        $fields = $fields.$key.', ';
        $values = $values."'".$value."'".', ';
    }
    
    $fields = substr($fields, 0,strlen($fields)-2);
    $values = substr($values, 0,strlen($values)-2);

    $result = pg_query($conn, "SELECT * FROM ".$_POST['addField']['tb_name']." WHERE ".$_POST['addField']["uni_type"]." = '".$_POST['addField']["uni_val"]."'");
    
	if(pg_numrows($result)>0){
		$arraydb['result'] = 'nope';
	}
	else{
        $id=get_id($conn,$_POST['addField']['tb_name']);

		pg_query($conn,"INSERT INTO ".$_POST['addField']['tb_name']." (id, ".$fields.") VALUES ($id, ".$values.")");
        $arraydb['result'] = 'done';
        $arraydb['id'] = $id;
	}


}
if(isset($_POST['delete']))
{
    if(isset($_POST['objName'])){
        $result = pg_query($conn, "DELETE FROM registrators WHERE obj = '".$_POST['objName']."'");
        $result = pg_query($conn, "DELETE FROM cameras WHERE obj = '".$_POST['objName']."'");
    }

	$result = pg_query($conn, "DELETE FROM ".$_POST['tb_name']." WHERE id = ".$_POST['id']."");

	if ($result) {
		$arraydb['result'] = 'done';
	}
	else{
		$arraydb['result'] = 'nope';
	}
}

function changeParams($conn,$_POSD) {
    

    switch ($_POSD['change']['mode']) {
        case "changObj":
            $table = 'object';
            break;
        case "changCam":
            $table = "cameras";
            break;
        case "changReg":
            $table = "registrators";
            break;
    }

    if($_POSD['change']['mode'] == 'changObj'){
        // var_dump($_POST['change']['formData']['name']);
        $name = getDB($conn,"SELECT * FROM object WHERE name='".$_POSD['change']['formData']['name']."'");
        
        $nameExist=count($name)>0;
        //изменяем имя у других
        if($nameExist){
            return 'exist';
        }
         
        $old_name = getDB($conn,"SELECT name FROM object WHERE id=".$_POSD['change']['id']."");
        // var_dump($old_name[0][0]);
        if ($old_name) {
            $old_name = $old_name[0][0];
        }

        $result1 = pg_query($conn, "UPDATE cameras SET (obj) = ('".$_POSD['change']['formData']['name']."') WHERE obj = '".$old_name."'");
        $result2 = pg_query($conn, "UPDATE registrators SET (obj) = ('".$_POSD['change']['formData']['name']."') WHERE obj = '".$old_name."'");


    }else{
        $real_old_pass = getDB($conn,"SELECT pass FROM ".$table." WHERE id=".$_POSD['change']['id']."");
        $nameExist = ($_POSD['change']["formData"]['old_pass'] == $real_old_pass[0][0]);
        // var_dump($_POST['change']["formData"]['old_pass']);
        // var_dump($real_old_pass[0][0]);
      
        unset($_POSD['change']["formData"]['old_pass']);
        if(!$nameExist){
            return 'pass_err';
        }
    }

    foreach($_POSD['change']['formData'] as $key => $value) {
        $fields = $fields.$key.', ';
        $values = $values."'".$value."'".', ';
    }
    $fields = substr($fields, 0,strlen($fields)-2);
    $values = substr($values, 0,strlen($values)-2);
    

    // $arraydb['pg'] = "UPDATE ".$table." SET (".$fields.") = (".$values.") WHERE id = ".$_POST['change']['id']."";
    $result = pg_query($conn, "UPDATE ".$table." SET (".$fields.") = (".$values.") WHERE id = ".$_POSD['change']['id']."");
    // $pass_query = pg_query($conn,"UPDATE ".$table." SET pass='".$coded_pass."' WHERE id=".$_POST['changePass']['id']."");

	if ($result) {
		return 'done';
	}
	else{
		return 'nope';
    }
    
    
}

if(isset($_POST['change'])){
    $arraydb['result'] = changeParams($conn,$_POST);
}

// if(isset($_POST['el_del'])){
//     //  $_COOKIE["el_del_res"] = system(curl -H "Content-Type: application/json" -XPOST 'http://localhost:9200/actcamrealtime/main/_delete_by_query' -d '{ "query" : { "term" : { "ip_cam" : "192.168.3.109" } } }');
// 	echo "server response";
// 	system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/firmwarevers/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
// 	system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/actcamrealtime/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
// }

pg_close($conn);
echo json_encode($arraydb);

// function changeParams($_POST) {
    

//     switch ($_POST['change']['mode']) {
//         case "changObj":
//             $table = 'object';
//             break;
//         case "changCam":
//             $table = "cameras";
//             break;
//         case "changReg":
//             $table = "registrators";
//             break;
//     }

//     if($_POST['change']['mode'] == 'changObj'){
//         // var_dump($_POST['change']['formData']['name']);
//         $name = getDB($conn,"SELECT * FROM object WHERE name='".$_POST['change']['formData']['name']."'");

//         $nameExist=count($name)>0;
//         //изменяем имя у других
//         if(!$nameExist){
         
//         $old_name = getDB($conn,"SELECT name FROM object WHERE id=".$_POST['change']['id']."");
//         // var_dump($old_name[0][0]);
//         if ($old_name) {
//             $old_name = $old_name[0][0];
//         }
//         // var_dump($old_name);
//         // var_dump($_POST['change']['formData']['name']);
//         $result1 = pg_query($conn, "UPDATE cameras SET (obj) = ('".$_POST['change']['formData']['name']."') WHERE obj = '".$old_name."'");
//         $result2 = pg_query($conn, "UPDATE registrators SET (obj) = ('".$_POST['change']['formData']['name']."') WHERE obj = '".$old_name."'");

//     }
//         // $result = pg_query($conn, "DELETE FROM registrators WHERE obj = '".$_POST['objName']."'");
//         // $result = pg_query($conn, "DELETE FROM cameras WHERE obj = '".$_POST['objName']."'");
//     }else{
//         $real_old_pass = getDB($conn,"SELECT pass FROM ".$table." WHERE id=".$_POST['change']['id']."");
//         $nameExist = !($_POST['change']["formData"]['old_pass'] == $real_old_pass[0]);
//         $arraydb['pass1'] = $_POST['change']["formData"]['old_pass'];
//         $arraydb['pass2'] = $real_old_pass[0];
      
//         unset($_POST['change']["formData"]['old_pass']);
//         if($nameExist){
//             $arraydb['result'] = 'pass_err';
//         }
//     }

//     foreach($_POST['change']['formData'] as $key => $value) {
//         $fields = $fields.$key.', ';
//         $values = $values."'".$value."'".', ';
//     }
//     $fields = substr($fields, 0,strlen($fields)-2);
//     $values = substr($values, 0,strlen($values)-2);
    
//     if(!$nameExist){
//         $arraydb['pg'] = "UPDATE ".$table." SET (".$fields.") = (".$values.") WHERE id = ".$_POST['change']['id']."";
//     $result = pg_query($conn, "UPDATE ".$table." SET (".$fields.") = (".$values.") WHERE id = ".$_POST['change']['id']."");
//     // $pass_query = pg_query($conn,"UPDATE ".$table." SET pass='".$coded_pass."' WHERE id=".$_POST['changePass']['id']."");

// 	if ($result) {
// 		$arraydb['result'] = 'done';
// 	}
// 	else{
// 		$arraydb['result'] = 'nope';
//     }
//     }
//     else {$arraydb['result'] = 'exist';}
// }