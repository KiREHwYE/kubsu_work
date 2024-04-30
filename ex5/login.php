<?php

define("user", "u67397");
define("password", "2392099");
define("dbname", "u67397");
/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.
$session_started = false;
if (isset($_COOKIE[session_name()])) {
  session_start();
  $session_started = true;
  if (!empty($_SESSION['login'])) {
    // Если есть логин в сессии, то пользователь уже авторизован.
    // TODO: Сделать выход (окончание сессии вызовом session_destroy()
    session_destroy();
    //при нажатии на кнопку Выход).
    // Делаем перенаправление на форму.
    header('Location: ./');
    exit();
  }
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>

<form action="" method="post">
  <input name="login" />
  <input name="pass" />
  <input type="submit" value="Войти" />
</form>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
  // TODO: Проверть есть ли такой логин и пароль в базе данных.
  // Выдать сообщение об ошибках.

  if (!$session_started) {
    session_start();
  }
  // Если все ок, то авторизуем пользователя.
  $_SESSION['login'] = $_POST['login'];

   $user = user;
   $pass = password;
   $db = new PDO('mysql:host=localhost;dbname=' . dbname, $user, $pass, [
     PDO::ATTR_PERSISTENT => true,
     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
   ]);

   try {
     // Получаем personId из таблицы personAuthentificationData
     $stmt = $db->prepare("SELECT personId FROM personAuthentificationData WHERE login = :login");
     $stmt->execute([':login' => $login]);
     $authData = $stmt->fetch(PDO::FETCH_ASSOC);
     $personId = $authData['personId'];

     $_SESSION['uid'] = $personId;
    echo strval($personId);

   } catch(PDOException $e){
     print('Error : ' . $e->getMessage());
     exit();
   }


  // Делаем перенаправление.
  header('Location: ./');
}
