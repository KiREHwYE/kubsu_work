<?php
/**
 * Реализовать проверку заполнения обязательных полей формы в предыдущей
 * с использованием Cookies, а также заполнение формы по умолчанию ранее
 * введенными значениями.
 */

// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // Массив для временного хранения сообщений пользователю.
  $messages = array();

  // В суперглобальном массиве $_COOKIE PHP хранит все имена и значения куки текущего запроса.
  // Выдаем сообщение об успешном сохранении.
  if (!empty($_COOKIE['save'])) {
    // Удаляем куку, указывая время устаревания в прошлом.
    setcookie('save', '', 100000);
    // Если есть параметр save, то выводим сообщение пользователю.
    $messages[] = 'Спасибо, результаты сохранены.';
  }

  // Складываем признак ошибок в массив.
  $errors = array();
  $errors['name'] = !empty($_COOKIE['name_error']);
  // TODO: аналогично все поля.
  $errors['phone'] = !empty($_COOKIE['phone_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['year'] = !empty($_COOKIE['year_error']);
  $errors['sex'] = !empty($_COOKIE['sex_error']);
  $errors['language'] = !empty($_COOKIE['language_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);

  // Выдаем сообщения об ошибках.
  if ($errors['name']) {
    // Удаляем куки, указывая время устаревания в прошлом.
    setcookie('name_error', '', 100000);
    setcookie('name_value', '', 100000);
    // Выводим сообщение.
    $messages[] = '<div class="error">Заполните имя.</div>';
  }
    // TODO: тут выдать сообщения об ошибках в других полях.
  if ($errors['phone']) {
      // Удаляем куки, указывая время устаревания в прошлом.
      setcookie('phone_error', '', 100000);
      setcookie('phone_value', '', 100000);
      // Выводим сообщение.
      $messages[] = '<div class="error">Заполните поле номера телефона.</div>';
  }
  if ($errors['email']) {
    // Удаляем куки, указывая время устаревания в прошлом.
    setcookie('email_error', '', 100000);
    setcookie('email_value', '', 100000);
    // Выводим сообщение.
    $messages[] = '<div class="error">Заполните поле email.</div>';
  }
  if ($errors['year']) {
      // Удаляем куки, указывая время устаревания в прошлом.
      setcookie('year_error', '', 100000);
      setcookie('year_value', '', 100000);
      // Выводим сообщение.
      $messages[] = '<div class="error">Укажите дату рождения.</div>';
  }
   if ($errors['sex']) {
      // Удаляем куки, указывая время устаревания в прошлом.
      setcookie('sex_error', '', 100000);
      setcookie('sex_value', '', 100000);
      // Выводим сообщение.
      $messages[] = '<div class="error">Заполните пол.</div>';
   }
  if ($errors['language']) {
     // Удаляем куки, указывая время устаревания в прошлом.
     setcookie('language_error', '', 100000);
     foreach ($_POST['language'] as $selectedOption) {
         setcookie(strval($selectedOption) + '_value', $_POST[strval($selectedOption)], time() + 30 * 24 * 60 * 60);
     }
     // Выводим сообщение.
     $messages[] = '<div class="error">Выберете любимые языки.</div>';
   }
   if ($errors['biography']) {
        // Удаляем куки, указывая время устаревания в прошлом.
        setcookie('biography_error', '', 100000);
        setcookie('biography_value', '', 100000);
        // Выводим сообщение.
        $messages[] = '<div class="error">Заполните поле биографии.</div>';
      }


  // Складываем предыдущие значения полей в массив, если есть.
  $values = array();
  $values['name'] = empty($_COOKIE['name_value']) ? '' : $_COOKIE['name_value'];
      // TODO: аналогично все поля.
  $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['year'] = empty($_COOKIE['year_value']) ? '' : $_COOKIE['year_value'];
  $values['sex'] = empty($_COOKIE['sex_value']) ? '' : $_COOKIE['sex_value'];
  $values['language'] = empty($_COOKIE['language_value']) ? '' : $_COOKIE['language_value'];
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];


  // Включаем содержимое файла form.php.
  // В нем будут доступны переменные $messages, $errors и $values для вывода
  // сообщений, полей с ранее заполненными данными и признаками ошибок.
  include('form.php');
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.
else {
  // Проверяем ошибки.
  $errors = FALSE;
  if (empty($_POST['name']) || !preg_match('/^([А-Яа-я\s]+|[A-Za-z\s]+){2, 128}$/', $_POST['name'])) {
    // Выдаем куку на день с флажком об ошибке в поле fio.
    setcookie('name_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);

  if (empty($_POST['phone']) || strlen($_POST['phone']) > 32 || !preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/', $_POST['phone'])) {
      setcookie('phone_error', '1', time() + 24 * 60 * 60);
      $errors = TRUE;
    }
    setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);

    if (empty($_POST['email']) || !preg_match('/^[\w_\.]+@([\w-]+\.)+[\w-]{2,4}$/', $_POST['email'])) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
      }
      setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);

      if (empty($_POST['year'])) {
          setcookie('year_error', '1', time() + 24 * 60 * 60);
          $errors = TRUE;
        }
        setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);


        if (empty($_POST['sex'])) {
            setcookie('sex_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
          }
          setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);



          if (empty($_POST['language'])) {
                      // Выдаем куку на день с флажком об ошибке в поле fio.
                      setcookie('language_error', '1', time() + 24 * 60 * 60);
                      $errors = TRUE;
                    }
                    // Сохраняем ранее введенное в форму значение на месяц.
                    foreach ($_POST['language'] as $selectedOption) {
                            setcookie(strval($selectedOption) + '_value', $_POST[strval($selectedOption)], time() + 30 * 24 * 60 * 60);
                    }

                    if (empty($_POST['biography']) || strlen($_POST['biography']) > 256) {
                                // Выдаем куку на день с флажком об ошибке в поле fio.
                                setcookie('biography_error', '1', time() + 24 * 60 * 60);
                                $errors = TRUE;
                              }
                              // Сохраняем ранее введенное в форму значение на месяц.
                              setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);

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
        // TODO: тут необходимо удалить остальные Cookies.
    setcookie('phone_error', '', 100000);
    setcookie('email_value', '', 100000);
    setcookie('year_value', '', 100000);
    setcookie('sex_value', '', 100000);
    setcookie('language_value', '', 100000);
    setcookie('biography_value', '', 100000);
  }

  // Сохранение в БД.
  // ...
    // Сохранение в базу данных.
    $user = 'u67397'; // Заменить на ваш логин
    $pass = '2392099'; // Заменить на пароль
    $db = new PDO('mysql:host=localhost;dbname=u67397', $user, $pass, [
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

      // Обработка каждого выбранного языка
      foreach ($_POST['language'] as $selectedOption) {
        // Получение languageId для выбранного языка
        $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
        $languageStmt->execute([':title' => $selectedOption]);
        $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

        // Вставка в personLanguage
        $stmt->execute([
          ':personId' => $personId,
          ':languageId' => $language['languageId']
        ]);
      }
    }
    catch(PDOException $e){
      print('Error : ' . $e->getMessage());
      exit();
    }

  // Сохраняем куку с признаком успешного сохранения.
  setcookie('save', '1');

  // Делаем перенаправление.
  header('Location: index.php');
}
