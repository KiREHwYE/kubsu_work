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
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.
$session_started = false;
if (isset($_COOKIE[session_name()]) && session_start()) {
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

<form style="display: flex; flex-direction: column; justify-content: center; align-items: center" action="" method="post">
  <input name="login" />
  <input name="pass" />
  <input type="submit" value="Войти" />
</form>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
      if (!$session_started) {
        session_start();
      }

      $user = user;
      $pass = password;
      $db = new PDO('mysql:host=localhost;dbname=' . dbname, $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

      try {
        // Проверяем, есть ли пользователь с таким логином и паролем
        $login = $_POST['login'];
        $password = $_POST['pass'];

        $stmt = $db->prepare("SELECT personId FROM personAuthentificationData WHERE login = :login AND pass = :pass");
        $stmt->execute([':login' => $login, ':pass' => $password]);
        $authData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($authData) {
          // Пользователь существует, сохраняем данные в сессию
          $_SESSION['login'] = $login;
          $_SESSION['uid'] = $authData['personId'];

          $stmt = $db->prepare("SELECT name, phone, email, year, sex, biography FROM person WHERE personId = :personId");
          $stmt->bindParam(':personId', $authData['personId']);
          $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':year' => $year,
            ':sex' => $sex,
            ':biography' => $biography,
            ]);

          // Делаем перенаправление на главную страницу
          header('Location: ./');
          exit();
        } else {
          // Пользователь не найден, выдаем ошибку
          echo "Неверный логин или пароль.";
          exit();
        }
      } catch(PDOException $e){
        print('Error : ' . $e->getMessage());
        exit();
      }



  // Делаем перенаправление.
  header('Location: ./');
}
