
function message(text){
  $("#chat-result").append(text);
}

$( document ).ready(function() {

// Открываем соединение с сервером
var socket = new WebSocket("ws://localhost:8091/chat/server.php");

socket.onopen = function(){
    message("<div>Соединение установленно</div>");
}

socket.onerror = function(error) {
  message("<div>Ошибка при соединении</div>" + (error.message ? error.message: ""));

}

socket.onclose = function(){
  message("<div> Соединение закрыто </div>");
}
socket.onmessage = function(event) {

  var data = JSON.parse(event.data);
  message(
  	"<div>" + data.username + " - " + data.message + "</div>"
  	);
}



$("#chat").on('submit', function(){


	var message = {

		chat_message:$("#chat-message").val(), 
		chat_user: $("#chat-user").val(),
	}

	$("#chat-user").attr("type", "hidden");

	socket.send(JSON.stringify(message));

	//Запрещаем стандартный оброботчик событий отправки формы
	return false;


});

});