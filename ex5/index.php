<?php
/**
 * Реализовать возможность входа с паролем и логином с использованием
 * сессии для изменения отправленных данных в предыдущей задаче,
 * пароль и логин генерируются автоматически при первоначальной отправке формы.
 */

header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Массив для временного хранения сообщений пользователю.
  $messages = array();

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
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass']));
    }
  }

  // Складываем признак ошибок в массив.
  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['phone'] = !empty($_COOKIE['phone_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['year'] = !empty($_COOKIE['year_error']);
  $errors['sex'] = !empty($_COOKIE['sex_error']);
  $errors['language'] = !empty($_COOKIE['language_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);
  $errors['contract_agreement'] = !empty($_COOKIE['contract_agreement_error']);


    // Выдаем сообщения об ошибках.
    if ($errors['name']) {
      setcookie('name_error', '', 100000);
      setcookie('name_value', '', 100000);
      $messages[] = '<div class="error">Заполните имя.</div>';
    }

    if ($errors['phone']) {
        setcookie('phone_error', '', 100000);
        setcookie('phone_value', '', 100000);
        $messages[] = '<div class="error">Заполните поле номера телефона.</div>';
    }

    if ($errors['email']) {
      setcookie('email_error', '', 100000);
      setcookie('email_value', '', 100000);
      $messages[] = '<div class="error">Заполните поле email.</div>';
    }

    if ($errors['year']) {
        setcookie('year_error', '', 100000);
        setcookie('year_value', '', 100000);
        $messages[] = '<div class="error">Укажите дату рождения.</div>';
    }

    if ($errors['sex']) {
        setcookie('sex_error', '', 100000);
        setcookie('sex_value', '', 100000);
        $messages[] = '<div class="error">Заполните пол.</div>';
    }

    if ($errors['language']) {
        setcookie('language_error', '', 100000);
        setcookie('language_value', '', 100000);
        $messages[] = '<div class="error">Выберете языки.</div>';
    }

    if ($errors['biography']) {
        setcookie('biography_error', '', 100000);
        setcookie('biography_value', '', 100000);
        $messages[] = '<div class="error">Заполните поле биографии.</div>';
    }

    if ($errors['contract_agreement']) {
        setcookie('contract_agreement_error', '', 100000);
        setcookie('contract_agreement_value', '', 100000);
        $messages[] = '<div class="error">Поставьте галочку.</div>';
    }


  // Складываем предыдущие значения полей в массив, если есть.
  // При этом санитизуем все данные для безопасного отображения в браузере.
  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
  $values['phone'] = empty($_COOKIE['phone_value']) ? '' : strip_tags($_COOKIE['phone_value']);
  $values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
  $values['year'] = empty($_COOKIE['year_value']) ? '' : strip_tags($_COOKIE['year_value']);
  $values['sex'] = empty($_COOKIE['sex_value']) ? '' : strip_tags($_COOKIE['sex_value']);
  $values['language'] = empty($_COOKIE['language_value']) ? '' : strip_tags($_COOKIE['language_value']);
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : strip_tags($_COOKIE['biography_value']);
  $values['contract_agreement'] = empty($_COOKIE['contract_agreement_value']) ? '' : strip_tags($_COOKIE['contract_agreement_value']);

  // Если нет предыдущих ошибок ввода, есть кука сессии, начали сессию и
  // ранее в сессию записан факт успешного логина.
  if (empty($errors) && !empty($_COOKIE[session_name()]) &&
      session_start() && !empty($_SESSION['login'])) {
    // TODO: загрузить данные пользователя из БД
    // и заполнить переменную $values,
    // предварительно санитизовав.
    printf('Вход с логином %s, uid %d', $_SESSION['login'], $_SESSION['uid']);
  }

  // Включаем содержимое файла form.php.
  // В нем будут доступны переменные $messages, $errors и $values для вывода
  // сообщений, полей с ранее заполненными данными и признаками ошибок.
  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else {
  // Проверяем ошибки.
  $errors = FALSE;

  if (empty($_POST['name']) || !preg_match('/^([А-Яа-я\s]+|[A-Za-z\s]+)$/', $_POST['name'])) {
      setcookie('name_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['phone']) || strlen($_POST['phone']) > 32 || !preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/', $_POST['phone'])) {
      setcookie('phone_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['email']) || !preg_match('/^[\w_\.]+@([\w-]+\.)+[\w-]{2,4}$/', $_POST['email'])) {
      setcookie('email_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['year'])) {
      setcookie('year_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['sex'])) {
      setcookie('sex_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);
  }

  // if (empty($_POST['language'])) {
  //     setcookie('language_error', '1', time() + 24 * 60 * 60);
  //     $errors = TRUE;
  // } else {
  //     setcookie('language_value', $_POST['language'], time() + 30 * 24 * 60 * 60);
  // }
  if (empty($_POST['language'])) {
    setcookie('language_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  } else {
      // Преобразование массива в строку для сохранения в cookie
      $language_value = implode(',', $_POST['language']);
      setcookie('language_value', $language_value, time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['biography']) || strlen($_POST['biography']) > 256) {
      setcookie('biography_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);
  }

  if (empty($_POST['contract_agreement'])) {
      setcookie('contract_agreement_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
  } else {
      setcookie('contract_agreement_value', $_POST['contract_agreement'], time() + 30 * 24 * 60 * 60);
  }

// *************
// TODO: тут необходимо проверить правильность заполнения всех остальных полей.
// Сохранить в Cookie признаки ошибок и значения полей.
// *************

  if ($errors) {
    // При наличии ошибок перезагружаем страницу и завершаем работу скрипта.
    header('Location: index.php');
    exit();
  }
  else {
    // Удаляем Cookies с признаками ошибок.
    setcookie('name_error', '', 100000);
    setcookie('phone_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('year_error', '', 100000);
    setcookie('sex_error', '', 100000);
    setcookie('language_error', '', 100000);
    setcookie('biography_error', '', 100000);
    setcookie('contract_agreement_error', '', 100000);
  }

  // Проверяем меняются ли ранее сохраненные данные или отправляются новые.
  if (!empty($_COOKIE[session_name()]) &&
      session_start() && !empty($_SESSION['login'])) {
    // TODO: перезаписать данные в БД новыми данными,
    // кроме логина и пароля.

    $login = $_SESSION['login'];

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
    // Генерируем уникальный логин и пароль.
    // TODO: сделать механизм генерации, например функциями rand(), uniquid(), md5(), substr().
    $login = getRandString($n);
    $pass = getRandString($n);
    $md5Pass = md5($pass);
    // Сохраняем в Cookies.
    setcookie('login', $login);
    setcookie('pass', $pass);

    //Сохранение данных формы, логина и хеш md5() пароля в базу данных.
    $user = user;
    $pass = password;
    $db = new PDO('mysql:host=localhost;dbname=' . dbname, $user, $pass, [
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

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
              ':personId' => $_POST['personId'],
              ':login' => $_POST['login'],
              ':pass' => $_POST['md5Pass'],
            ]);

    }
    catch(PDOException $e){
      print('Error : ' . $e->getMessage());
      exit();
    }
  }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: ./');
}
?>
