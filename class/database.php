<?php



class Database{
  /**
   * Здесь храниться экземпляр PDO
   */
  private $link;

  /**
   * Конструктор который вызывается функцию сразу при создании экземпляра
   */
  public function __construct()
  {
     $this->connect();
  }

  /**
   * Подключение к бд
   */
  private function connect()
  {
    $config = require_once 'config.php';
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['db_name'].';charset='.$config['charset'];
    $this->link = new PDO($dsn, $config['username'], $config['password']);

    return $this;

  }

  /**
   * Запросы к бд и внесение в нее данных
   */
  public function execute($sql)
  {
    $sth = $this->link->prepare($sql);
    return $sth->execute();

  }

  /**
   * Работа с выбраным данными из базы
   */
  public function query($sql)
  {
    $sth = $this->link->prepare($sql);
    $sth->execute();
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);

    if($result === false){
      return [];
    }

    return $result;
  }

  public function createDataDb($username, $message)

  {
    $this->execute("INSERT INTO `chat2` SET `username` = '$username', `message` = '$message'");

  }

   public function lastDbMessageChat() {

        $execute = $this->query("SELECT * FROM `chat2`");
        $counts = count($execute);
        $last = $execute[$counts-1];

        // преобразуем наш массив в json строку

        $lastJson = json_encode($last);
        // print_r($lastJson);

        return $last;


   }

}
// $db = new Database();

// // $db->execute("INSERT INTO `user1` SET `username`='Bair', `password`='1203', `date`=".time());
// $users = $db->query("SELECT * FROM `user1`");
// print_r($users);


