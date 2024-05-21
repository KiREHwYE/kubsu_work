<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

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

$db = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPassword, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Функция для проверки CSRF токена
function checkCsrfToken($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Функция для очистки пользовательского ввода
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

if (isset($_COOKIE[session_name()]) && session_status() != PHP_SESSION_NONE) {
    if (!empty($_SESSION['login'])) {
        if (isset($_POST['logout'])) {
            session_destroy();
            header('Location: ./login.php');
            exit();
        }

        // Делаем перенаправление на форму.
        header('Location: ./');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Массив для временного хранения сообщений пользователю.
  $messages = array();

  // Функция для очистки вывода
  function sanitizeOutput($data) {
    return htmlspecialchars($data);
  }

  // Выдаем сообщение об успешном сохранении.
  if (!empty($_COOKIE['save'])) {
    // Удаляем куку, указывая время устаревания в прошлом.
    setcookie('save', '', 100000);
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);

    // Выводим сообщение пользователю.
    $messages[] = 'Спасибо, результаты сохранены.';

    // Если в куках есть пароль, то выводим сообщение.
    if (!empty($_COOKIE['pass'])) {
      $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
        и паролем <strong>%s</strong> для изменения данных.',
        sanitizeOutput($_COOKIE['login']),
        sanitizeOutput($_COOKIE['pass']));
    }
  }

    // Складываем признак ошибок в массив.
    $errors = array();
    $error_fields = ['name', 'phone', 'email', 'year', 'sex', 'language', 'biography', 'contract_agreement'];
    foreach ($error_fields as $field) {
      $errors[$field] = !empty($_COOKIE[$field . '_error']);
    }


    // Выдаем сообщения об ошибках.
    foreach ($errors as $key => $value) {
      if ($value) {
        setcookie($key . '_error', '', time() - 10000);
        setcookie($key . '_value', '', time() - 10000);
        $messages[] = '<div class="error">Заполните поле ' . sanitizeOutput($key) . '.</div>';
       }
    }


  // Складываем предыдущие значения полей в массив, если есть.
  // При этом санитизуем все данные для безопасного отображения в браузере.
  $values = array();
  $value_fields = ['name', 'phone', 'email', 'year', 'sex', 'biography', 'contract_agreement'];
  foreach ($error_fields as $field) {
      $values[$field] = empty($_COOKIE[$field . '_value']) ? '' : sanitizeOutput($_COOKIE[$field . '_value']);
  }
  $savedLanguage = empty($_COOKIE['language_value']) ? '' : $_COOKIE['language_value'];
  $values['language'] = explode(',', $savedLanguage);

  $savedLanguages = $values['language'];

  function isSelected($optionValue, $savedLanguages) {
        return in_array($optionValue, $savedLanguages) ? 'selected' : '';
  }

  if (session_status() != PHP_SESSION_NONE && !empty($_SESSION['login'])) {

    try {
      $stmt = $db->prepare("SELECT name, phone, email, year, sex, biography FROM person WHERE personId = :personId");
      $stmt->bindParam(':personId', $_SESSION['uid'], PDO::PARAM_INT);
      $stmt->execute();

      $userData = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($userData) {
        // Заполняем массив $values данными пользователя
        $values['name'] = strip_tags($userData['name']);
        $values['phone'] = strip_tags($userData['phone']);
        $values['email'] = strip_tags($userData['email']);
        $values['year'] = strip_tags($userData['year']);
        $values['sex'] = strip_tags($userData['sex']);
        $values['biography'] = strip_tags($userData['biography']);

        $personId = $_SESSION['uid'];

        $selectedLanguagesStmt = $db->prepare("SELECT title FROM language INNER JOIN personLanguage ON language.languageId = personLanguage.languageId WHERE personLanguage.personId = :personId");
        $selectedLanguagesStmt->execute([':personId' => $personId]);
        $savedLanguages = $selectedLanguagesStmt->fetchAll(PDO::FETCH_COLUMN, 0);

      } else {
        echo 'Данные пользователя не найдены.';
      }
    } catch(PDOException $e) {
      error_log('Ошибка при загрузке данных: ' . $e->getMessage());
      echo 'Произошла ошибка при загрузке данных.';
    }
  } else {
    echo 'Пользователь не вошел в систему.';
  }

  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Проверяем CSRF токен
    if (!checkCsrfToken($_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

  // Проверяем ошибки.
  $errors = FALSE;

  $error_fields = [
      'name' => '/^([А-Яа-я\s]+|[A-Za-z\s]+)$/',
      'phone' => '/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/',
      'email' => '/^[\w_\.]+@([\w-]+\.)+[\w-]{2,4}$/',
      'year' => '',
      'sex' => '',
      'biography' => '/^.{0,256}$/',
      'contract_agreement' => ''
    ];

  foreach ($error_fields as $field => $pattern) {
      if (empty($_POST[$field]) || ($pattern && !preg_match($pattern, $_POST[$field]))) {
        setcookie($field . '_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
      } else {
        setcookie($field . '_value', sanitizeInput($_POST[$field]), time() + 30 * 24 * 60 * 60);
      }
  }

  if (empty($_POST['language'])) {
        setcookie('language_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
  } else {
        $languageString = implode(',', $_POST['language']);
        setcookie('language_value', $languageString, time() + 30 * 24 * 60 * 60);
  }

  if ($errors) {
    header('Location: index.php');
    exit();
  }
  else {
    // Удаляем Cookies с признаками ошибок.
    foreach ($error_fields as $field => $pattern) {
      setcookie($field . '_error', '', 100000);
    }
    setcookie('language_error', '', 100000);
  }

  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (!empty($_COOKIE[session_name()]) &&
      session_start() && !empty($_SESSION['login'])) {

    $login = $_SESSION['login'];

    try {
      // Получаем personId из таблицы personAuthentificationData
      $stmt = $db->prepare("SELECT personId FROM personAuthentificationData WHERE login = :login");
      $stmt->execute([':login' => $login]);
      $authData = $stmt->fetch(PDO::FETCH_ASSOC);
      $personId = $authData['personId'];

      // Теперь обновляем данные в таблице person.
      $stmt = $db->prepare("UPDATE person SET name = :name, email = :email, phone = :phone, year = :year, sex = :sex, biography = :biography WHERE personId = :personId");
      $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'],
        ':year' => $_POST['year'],
        ':sex' => $_POST['sex'],
        ':biography' => $_POST['biography'],
        ':personId' => $personId
      ]);

      // Обновляем данные в таблице personLanguage.
      foreach ($_POST['language'] as $selectedOption) {
        $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
        $languageStmt->execute([':title' => $selectedOption]);
        $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

        // Проверяем, существует ли уже запись для данного personId и languageId.
        $checkStmt = $db->prepare("SELECT * FROM personLanguage WHERE personId = :personId AND languageId = :languageId");
        $checkStmt->execute([
          ':personId' => $personId,
          ':languageId' => $language['languageId']
        ]);

        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
          // Если запись существует, обновляем ее.
          $updateStmt = $db->prepare("UPDATE personLanguage SET personId = :personId, languageId = :languageId WHERE personId = :personId AND languageId = :languageId");
          $updateStmt->execute([
            ':personId' => $personId,
            ':languageId' => $language['languageId']
          ]);
        } else {
          // Если записи не существует, вставляем новую.
          $insertStmt = $db->prepare("INSERT INTO personLanguage (personId, languageId) VALUES (:personId, :languageId)");
          $insertStmt->execute([
            ':personId' => $personId,
            ':languageId' => $language['languageId']
          ]);
        }
      }
    } catch(PDOException $e){
      error_log('Error : ' . $e->getMessage());
      exit();
    }
  }

  else {

    $n=10;

    function getRandString($n) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';

      for ($i = 0; $i < $n; $i++) {
          $index = rand(0, strlen($characters) - 1);
          $randomString .= $characters[$index];
      }

      return $randomString;
    }

    $login = getRandString($n);
    $pass = getRandString($n);
    $md5Pass = md5($pass);
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);

    try {
      $stmt = $db->prepare("INSERT INTO person (name, email, phone, year, sex, biography) VALUES (:name, :email, :phone, :year, :sex, :biography)");
      $stmt->execute([
        ':name' => $_POST['name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':year' => $_POST['year'],
        ':sex' => $_POST['sex'],
        ':biography' => $_POST['biography']
      ]);

      $personId = $db->lastInsertId();

      $stmt = $db->prepare("INSERT INTO personLanguage (personId, languageId) VALUES (:personId, :languageId)");

      foreach ($_POST['language'] as $selectedOption) {
        $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
        $languageStmt->execute([':title' => $selectedOption]);
        $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

        $stmt->execute([
          ':personId' => $personId,
          ':languageId' => $language['languageId']
        ]);
      }

      $stmt = $db->prepare("INSERT INTO personAuthentificationData (personId, login, pass) VALUES (:personId, :login, :pass)");

      $stmt->execute([
        ':personId' => $personId,
        ':login' => $login,
        ':pass' => $md5Pass
      ]);
    }
    catch(PDOException $e){
      error_log('Ошибка обновления данных.' . $e->getMessage());
      echo 'Ошибка обновления данных.';
      exit();
    }
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: ./');
}
?>
