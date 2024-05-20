<?php

$env = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $env);

foreach ($lines as $line) {
    if (strpos($line, '=') !== false) {
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = trim($value, "\" \r");
    }
}

$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbName = $_ENV['DB_NAME'];

// Функция для проверки CSRF токена
function checkCsrfToken($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

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
    //при нажатии на кнопку Выход).
    if (isset($_POST['logout'])) {
        session_destroy();
        echo "Вы вышли из сессии.";
        header('Location: ./');
        exit();
    }

    // Делаем перенаправление на форму.
    header('Location: ./');
    exit();
  }
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>

<body style="display: flex; flex-direction: column; justify-content: center; align-items: center">

<h1>
  Login
</h1>

<form action="" method="post">
  <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
  <input name="login" />
  <input name="pass" />
  <input type="submit" value="Войти" />
</form>

<input type="submit" name = "logout" value="Выйти" />

</body>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {

      if (!checkCsrfToken($_POST['csrf_token'])) {
          die('CSRF token validation failed.');
      }

      if (!$session_started) {
        session_start();
      }

      $db = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPassword, [
          PDO::ATTR_PERSISTENT => true,
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      ]);

      try {
        // Проверяем, есть ли пользователь с таким логином и паролем
        $login = $_POST['login'];
        $password = $_POST['pass'];
        $md5Pass = md5($password);

        $stmt = $db->prepare("SELECT personId FROM personAuthentificationData WHERE login = :login AND pass = :pass");
        $stmt->execute([':login' => $login, ':pass' => $md5Pass]);
        $authData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($authData) {
          // Пользователь существует, сохраняем данные в сессию
          $_SESSION['login'] = $login;
          $_SESSION['uid'] = $authData['personId'];

          // Делаем перенаправление на главную страницу
          header('Location: ./');
          exit();
        } else {
          // Пользователь не найден, выдаем ошибку
          echo "Неверный логин или пароль.";
          echo $md5Pass;
          exit();
        }
      } catch(PDOException $e){
        print('Error : ' . $e->getMessage());
        exit();
      }



  // Делаем перенаправление.
  header('Location: ./');
}
