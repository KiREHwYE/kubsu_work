<html>
<head>
  <style>
    /* Сообщения об ошибках и поля с ошибками выводим с красным бордюром. */
    .error {
      border: 2px solid red;
    }
  </style>
</head>

<?php
if (!empty($messages)) {
  print('<div id="messages">');
// Выводим все сообщения.
foreach ($messages as $message) {
print($message);
}
print('</div>');
}


define("user", "u67397");
define("password", "2392099");
define("dbname", "u67397");

$isAdminAuth = false;

$user = user;
$pass = password;
$db = new PDO('mysql:host=localhost;dbname=' . dbname, $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$messages = array();

// Выдаем сообщение об успешном сохранении.
if (!empty($_COOKIE['save'])) {
    // Удаляем куку, указывая время устаревания в прошлом.
    setcookie('save', '', 100000);

    // Выводим сообщение пользователю.
    $messages[] = 'Спасибо, результаты сохранены.';
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


if (!empty($_SERVER['PHP_AUTH_USER']) &&
    !empty($_SERVER['PHP_AUTH_PW'])) {

    try {
        // Проверяем, есть ли пользователь с таким логином и паролем
        $admin_login = $_SERVER['PHP_AUTH_USER'];
        $admin_pass = $_SERVER['PHP_AUTH_PW'];
        $md5AdminPass = md5($admin_pass);

        $stmt = $db->prepare("SELECT * FROM adminAccount WHERE adminLogin = :adminLogin AND adminPass = :adminPass");
        $stmt->execute([':adminLogin' => $admin_login, ':adminPass' => $md5AdminPass]);
        $authData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($authData) {
            $isAdminAuth = true;
        }
    } catch(PDOException $e){
        print('Error : ' . $e->getMessage());
        exit();
    }
}



if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    !$isAdminAuth) {

  header('HTTP/1.1 401 Unanthorized');
  header('WWW-Authenticate: Basic realm="My site"');
  print('<h1>401 Требуется авторизация</h1>');
  exit();
}

echo "Вы успешно авторизовались и видите защищенные паролем данные.";


$usersDB = [];

try {
    $admin_login = $_SERVER['PHP_AUTH_USER'];
    $admin_pass = $_SERVER['PHP_AUTH_PW'];
    $md5Pass = md5($admin_pass);

    $stmt = $db->prepare("SELECT * FROM person");
    $stmt->execute();
    $authData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($authData) {
        $usersDB = $authData;
    }
} catch(PDOException $e){
    print('Error : ' . $e->getMessage());
    exit();
}

?>
<body style="display: flex; flex-direction: column; justify-content: center; align-items: center">

<h1>
    Admin Control Center
</h1>

<h3>
    List of users
</h3>

<form style="display: flex;flex-direction: column;width: 20%" action="admin.php" method="POST">
    <select name="userId">
        <?php foreach($usersDB as $option) : ?>
            <option value="<?php echo $option['personId']; ?>"><?php echo "ID: " . $option['personId'] . " "; echo "Name: " . $option['name']; ?></option>
        <?php endforeach; ?>
    </select>

    <input required type="submit" value="Выбрать этого пользователя">
</form>

<?php

$values['name'] = empty($_COOKIE['name_value']) ? '' : strip_tags($_COOKIE['name_value']);
$values['phone'] = empty($_COOKIE['phone_value']) ? '' : strip_tags($_COOKIE['phone_value']);
$values['email'] = empty($_COOKIE['email_value']) ? '' : strip_tags($_COOKIE['email_value']);
$values['year'] = empty($_COOKIE['year_value']) ? '' : strip_tags($_COOKIE['year_value']);
$values['sex'] = empty($_COOKIE['sex_value']) ? '' : strip_tags($_COOKIE['sex_value']);
$savedLanguage = empty($_COOKIE['language_value']) ? '' : $_COOKIE['language_value'];
$values['language'] = explode(',', $savedLanguage);
$values['biography'] = empty($_COOKIE['biography_value']) ? '' : strip_tags($_COOKIE['biography_value']);

$savedLanguages = $values['language'];

function isSelected($optionValue, $savedLanguages) {
    return in_array($optionValue, $savedLanguages) ? 'selected' : '';
}

// Проверяем, была ли форма отправлена и установлен ли ключ 'user'
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['userId'])) {
    $selectOption = $_POST['userId'];

    try {
        $stmt = $db->prepare("SELECT name, phone, email, year, sex, biography FROM person WHERE personId = :personId");
        $stmt->bindParam(':personId', $selectOption, PDO::PARAM_STR);
        $stmt->execute();

        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $values['personId'] = strip_tags($selectOption);
            $values['name'] = strip_tags($userData['name']);
            $values['phone'] = strip_tags($userData['phone']);
            $values['email'] = strip_tags($userData['email']);
            $values['year'] = strip_tags($userData['year']);
            $values['sex'] = strip_tags($userData['sex']);
            $values['biography'] = strip_tags($userData['biography']);
            $selectedLanguagesStmt = $db->prepare("SELECT title FROM language INNER JOIN personLanguage ON language.languageId = personLanguage.languageId WHERE personLanguage.personId = :personId");
            $selectedLanguagesStmt->execute([':personId' => $values['personId']]);
            $savedLanguages = $selectedLanguagesStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            echo 'Данные пользователя не найдены.';
        }
    } catch(PDOException $e) {
        echo 'Ошибка при загрузке данных: ' . $e->getMessage();
    }
}
?>


<h3>
  This user form
</h3>

<form style="display: flex;flex-direction: column;width: 20%" action="admin.php" method="POST">
  <input required type="text" name="name" <?php if ($errors['name']) {print 'class="error"';} ?> value="<?php print $values['name']; ?>" placeholder="Full name">
    <input required type="tel" name="phone" <?php if ($errors['phone']) {print 'class="error"';} ?> value="<?php print $values['phone']; ?>" placeholder="Phone number">
    <input required type="email" name="email" <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>" placeholder="Email">
    <input required type="date" name="year" <?php if ($errors['year']) {print 'class="error"';} ?> value="<?php print $values['year']; ?>" placeholder="Date of birth">

    <div style="flex-direction: row;margin-top: 20px">
        <input required type="radio" name="sex" <?php if ($errors['sex']) {print 'class="error"';} ?> value="M" <?php if ($values['sex'] == 'M') {print 'checked';} ?>>Male
        <input required type="radio" name="sex" <?php if ($errors['sex']) {print 'class="error"';} ?> value="F" <?php if ($values['sex'] == 'F') {print 'checked';} ?>>Female
    </div>

    <select style="margin-top: 20px" name="language[]" multiple <?php if ($errors['language']) {print 'class="error"';} ?>>
      <option value="Pascal" <?php echo isSelected('Pascal', $savedLanguages); ?>>Pascal</option>
      <option value="C" <?php echo isSelected('C', $savedLanguages); ?>>C</option>
      <option value="C++" <?php echo isSelected('C++', $savedLanguages); ?>>C++</option>
      <option value="JavaScript" <?php echo isSelected('JavaScript', $savedLanguages); ?>>JavaScript</option>
      <option value="PHP" <?php echo isSelected('PHP', $savedLanguages); ?>>PHP</option>
      <option value="Python" <?php echo isSelected('Python', $savedLanguages); ?>>Python</option>
      <option value="Java" <?php echo isSelected('Java', $savedLanguages); ?>>Java</option>
      <option value="Haskel" <?php echo isSelected('Haskel', $savedLanguages); ?>>Haskel</option>
      <option value="Clojure" <?php echo isSelected('Clojure', $savedLanguages); ?>>Clojure</option>
      <option value="Prolog" <?php echo isSelected('Prolog', $savedLanguages); ?>>Prolog</option>
      <option value="Scala" <?php echo isSelected('Scala', $savedLanguages); ?>>Scala</option>
    </select>

    <textarea required style="margin-top: 20px" name="biography" <?php if ($errors['biography']) {print 'class="error"';} ?> placeholder="Your biography"><?php print htmlspecialchars($values['biography']); ?></textarea>

  <input required type="submit" value="Change data">
</form>


</body>

<?php

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

    if (empty($_POST['language'])) {
        setcookie('language_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        $languageString = implode(',', $_POST['language']);
        setcookie('language_value', $languageString, time() + 30 * 24 * 60 * 60);
    }

    if (empty($_POST['biography']) || strlen($_POST['biography']) > 256) {
        setcookie('biography_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);
    }


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
    }



    if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
         isset($_POST['personId']) &&
         isset($_POST['name']) &&
         isset($_POST['email']) &&
         isset($_POST['phone']) &&
         isset($_POST['year']) &&
         isset($_POST['sex']) &&
         isset($_POST['biography']) &&
         isset($_POST['language'])) {
        try {


            $stmt = $db->prepare("UPDATE person SET name = :name, email = :email, phone = :phone, year = :year, sex = :sex, biography = :biography WHERE personId = :personId");
            $stmt->execute([
              ':name' => $_POST['name'],
              ':email' => $_POST['email'],
              ':phone' => $_POST['phone'],
              ':year' => $_POST['year'],
              ':sex' => $_POST['sex'],
              ':biography' => $_POST['biography'],
              ':personId' => intval($_POST['personId'])
            ]);
            echo $_POST['personId'];

            if (is_array($_POST['language'])) {
                // Обновляем данные в таблице personLanguage.
                foreach ($_POST['language'] as $selectedOption) {
                    $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
                    $languageStmt->execute([':title' => $selectedOption]);
                    $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

                    // Проверяем, существует ли уже запись для данного personId и languageId.
                    $checkStmt = $db->prepare("SELECT * FROM personLanguage WHERE personId = :personId AND languageId = :languageId");
                    $checkStmt->execute([
                      ':personId' => intval($_POST['personId']),
                      ':languageId' => $language['languageId']
                    ]);

                    if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                        // Если запись существует, обновляем ее.
                        $updateStmt = $db->prepare("UPDATE personLanguage SET languageId = :languageId WHERE personId = :personId AND languageId = :languageId");
                        $updateStmt->execute([
                          ':personId' => intval($_POST['personId']),
                          ':languageId' => $language['languageId']
                        ]);
                    } else {
                        // Если записи не существует, вставляем новую.
                        $insertStmt = $db->prepare("INSERT INTO personLanguage (personId, languageId) VALUES (:personId, :languageId)");
                        $insertStmt->execute([
                          ':personId' => intval($_POST['personId']),
                          ':languageId' => $language['languageId']
                        ]);
                    }
                }
            }
        } catch(PDOException $e){
            print('Error : ' . $e->getMessage());
            exit();
        }

        setcookie('save', '1');
}
?>
</html>
