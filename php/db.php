<?php
//!!!
function connectBD($bd) 
{
	$conn = pg_connect("host=127.0.0.1 port=5432 dbname=".$bd." user=sava_user password=arinteg123!");
	if (!$conn) {
		echo "Warning!\n";
		exit;
	}
	return $conn;
}


function getDB($conn, $req) {
	$result = pg_query($conn, $req);
	// echo $req;
	if (!$result) {
		echo "Waring!\n";
		exit;
	}
	$r = 0;
	$arrayid =array();

	while ($row = pg_fetch_row($result)) {
		$arrayid[$r] = $row;
		$r++;
	}
    return $arrayid;
}

function getDB_assoc($conn, $req) {
	$result = pg_query($conn, $req);
	// echo $req;
	if (!$result) {
		echo "Waring!\n";
		exit;
	}
	$r = 0;
	$arrayid =array();

	while ($row = pg_fetch_assoc($result)) {
		$arrayid[$r] = $row;
		$r++;
	}
    return $arrayid;
}

function isPass($conn, $pass, $__login)
{
	$pass_query= pg_query($conn, "SELECT pass FROM users WHERE login='".$__login."'");
	$data =  pg_fetch_assoc($pass_query);
	$result = false;

	 if ($data['pass'] == hash('sha1' , $pass))
	{
		$result = true;
	}
	return $result;	
}

function grade_sort($x, $y) {
	return ((int)$x['id'] > (int)$y['id']);
}
function get_id($conn, $table){
	$query = pg_query($conn, "select MAX(id) from ".$table."");//".$table."
	$data = pg_fetch_assoc($query);
	
	if($data['max']==NULL)
		$max=-1;
	else
		$max=(int)$data['max'];
	// var_dump(gettype($max));
	$query = pg_query($conn, "select id from ".$table."");
	$data = pg_fetch_all($query);
	// var_dump(gettype($max));
	$id = $max+1;

	// echo var_dump( $max);
	usort ($data, grade_sort);
	// echo var_dump($data[0]["id"]);
	// echo var_dump($data[1]["id"]);
	// echo var_dump($data[2]["id"]);
	for($i=0; $i<(int)$max; $i++)
	{
		// var_dump($i);
		// var_dump($data[$i]["id"]);
		if((int)$data[$i]["id"]!=$i)
			{
				$id = $i;
				break;
			}
	}
	// var_dump($id);
	return $id;
}
function get_id2($conn, $table,$login){
	$query = pg_query($conn, "select MAX(id) from ".$table." where master='".$login."'");//".$table."
	$data = pg_fetch_assoc($query);
	
	if($data['max']==NULL)
		$max=-1;
	else
		$max=(int)$data['max'];
	// var_dump(gettype($max));
	$query = pg_query($conn, "select id from ".$table." where master='".$login."'");//".$
	$data = pg_fetch_all($query);
	// var_dump(gettype($max));
	$id = $max+1;

	// echo var_dump( $max);
	usort ($data, grade_sort);
	// echo var_dump($data[0]["id"]);
	// echo var_dump($data[1]["id"]);
	// echo var_dump($data[2]["id"]);
	for($i=0; $i<(int)$max; $i++)
	{
		// var_dump($i);
		// var_dump($data[$i]["id"]);
		if((int)$data[$i]["id"]!=$i)
			{
				$id = $i;
				break;
			}
	}
	// var_dump($id);
	return $id;
}
