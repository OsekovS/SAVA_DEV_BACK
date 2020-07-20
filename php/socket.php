<?php
//!!!
function socketConnect($msg) 
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($socket, 'localhost', 9310);

    $len = strlen($msg);
    //envio informacion a socket
    $sendMsg = socket_send($socket, $msg, $len, MSG_DONTROUTE);
    //now you can read from...
    $receive = trim(socket_read($socket, 100));
    socket_close($socket);
	return $receive;
}