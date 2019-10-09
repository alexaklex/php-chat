<?php
define('PORT',"8091");


require_once ("class/Chat.php");
require_once ("class/database.php");

$db = new Database(); 
$chat = new Chat();


$socket = socket_create(AF_INET, SOCK_STREAM,SOL_TCP);

socket_set_option($socket, SOL_SOCKET,SO_REUSEADDR, 1);
socket_bind($socket,0, PORT);

socket_listen($socket);

//Записываем все новые сокеты клиентов
$clientsocketArray = array($socket);


while(true) {

	//Цикл вайл слушает все сокеты входящие, обробатывает входящий сокет и удаляет из цикла

	$nullA = array();

	$newSocketArray = $clientsocketArray;
	//Проверяем есть ли изменения в новых сокетах
	socket_select($newSocketArray, $nullA, $nullA, 0, 10);

	if(in_array($socket, $newSocketArray)) {
		//получаем подключеный сокет
	    $newSocket = socket_accept($socket);
	    // print_r($newSocket);
	    $clientsocketArray[] = $newSocket;
	    $header = socket_read($newSocket, 1024);
	    $chat->sendHeaders($header,$newSocket,'localhost/chat',PORT);
	    // Конец рукопожатия

	    //Получаем ip адрес нового сокета
	    socket_getpeername($newSocket, $client_ip_address);


        //Выводим последнее сообщение пользователя только у текущего пользователя который присоединился

        
        $last_message = $db->lastDbMessageChat();
        // print_r($last_message);

        //Делаем нужный формат для отправки последних сообщений в чат текущему пользователю
        $lastDbdecode = $chat->dblasttext($last_message);

        //Отправляем в чат последнее сообщения из базы данных
        $lastDbdecodeMessage = $chat->DbSend($lastDbdecode, $newSocket);



	    //преобразуем ip с текстом в нужный формат чтобы отправить клиенту
	    $connectACK = $chat->newConnectionACK($client_ip_address);

	
	    //Отправляем в чат что такой пользователь подсоединился
	    $chat->send($connectACK, $clientsocketArray);

	    //Ищем обработанный сокет в массиве
	    $newSocketIndex = array_search($socket, $newSocketArray);
	    unset($newSocketArray[$newSocketIndex]);

	}

	//Проходимся по изменненыи масивам
	foreach ($newSocketArray as $newSocketArrayResource) {

		while (socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1) {
			//Получаем и преобразуем json сообщение клиента в последовательность байт
			$socketMessage = $chat -> unseal($socketData);
			//Декодируем в массив или объект
			$messageObj = json_decode($socketMessage);

			$user_chat = $messageObj->chat_user;

			$chatMessage = $chat->createChatMessage($user_chat, $messageObj->chat_message);

			
        	// Записываем в базу данных юзера и сообщение
			$db->createDataDb($user_chat, $messageObj->chat_message);

			$chat->send($chatMessage, $clientsocketArray);

			break 2;

		}



		

	}

    

}


socket_close($socket);