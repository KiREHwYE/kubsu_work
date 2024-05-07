<?php

/**
 * Задача 6. Реализовать вход администратора с использованием
 * HTTP-авторизации для просмотра и удаления результатов.
 **/

// Пример HTTP-аутентификации.
// PHP хранит логин и пароль в суперглобальном массиве $_SERVER.
// Подробнее см. стр. 26 и 99 в учебном пособии Веб-программирование и веб-сервисы.

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

  header('HTTP/1.1 401 Unauthorized');
  header('WWW-Authenticate: Basic realm="My site"');
  print('<h1>401 Требуется авторизация</h1>');
  exit();
}

echo "Вы успешно авторизовались и видите защищенные паролем данные.";

// *********
// Здесь нужно прочитать отправленные ранее пользователями данные и вывести в таблицу.
// Реализовать просмотр и удаление всех данных.
// *********

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

$usersDB = [];

try {
    // Проверяем, есть ли пользователь с таким логином и паролем
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
    <select name="user">
        <?php foreach($usersDB as $option) : ?>
            <option value="<?php echo $option['name']; ?>"><?php echo $option['name']; ?></option>
        <?php endforeach; ?>
    </select>

    <input required type="submit" value="Выбрать этого пользователя">
</form>

<?php

    $values = array();
    $values['personId'] = 0;
    $values['name'] = "";
    $values['phone'] = "";
    $values['email'] = "";
    $values['year'] = "";
    $values['sex'] = "";
    $values['language'] = [];
    $values['biography'] = "";

    $savedLanguages = $values['language'];

    function isSelected($optionValue, $savedLanguages) {
        return in_array($optionValue, $savedLanguages) ? 'selected' : '';
    }

    $selectOption = $_POST['user'];

    try {

        $stmt = $db->prepare("SELECT personId, name, phone, email, year, sex, biography FROM person WHERE name = :name");
        $stmt->bindParam(':name', $selectOption, PDO::PARAM_STR);
        $stmt->execute();

        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {

            $values['personId'] = strip_tags($userData['personId']);
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

?>

<h3>
  This user form
</h3>

<form style="display: flex;flex-direction: column;width: 20%" action="admin.php" method="POST">
  <input required type="text" name="name" value="<?php print $values['name']; ?>" placeholder="Full name">
  <input required type="tel" name="phone" value="<?php print $values['phone']; ?>" placeholder="Phone number">
  <input required type="email" name="email" value="<?php print $values['email']; ?>" placeholder="Email">
  <input required type="date" name="year" value="<?php print $values['year']; ?>" placeholder="Date of birth">

  <div style="flex-direction: row;margin-top: 20px">
      <input required type="radio" name="sex" value="M" <?php if ($values['sex'] == 'M') {print 'checked';} ?>>Male
      <input required type="radio" name="sex" value="F" <?php if ($values['sex'] == 'F') {print 'checked';} ?>>Female
  </div>

<select style="margin-top: 20px" name="language[]" multiple>
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

  <textarea required style="margin-top: 20px" name="biography" placeholder="Your biography"><?php print htmlspecialchars($values['biography']); ?></textarea>

  <input required type="submit" value="Change data">
</form>

</body>

<?php

    try {
        // Теперь обновляем данные в таблице person.
        $stmt = $db->prepare("UPDATE person SET name = :name, email = :email, phone = :phone, year = :year, sex = :sex, biography = :biography WHERE personId = :personId");
        $stmt->execute([
          ':name' => $_POST['name'],
          ':email' => $_POST['email'],
          ':phone' => $_POST['phone'],
          ':year' => $_POST['year'],
          ':sex' => $_POST['sex'],
          ':biography' => $_POST['biography'],
          ':personId' => $values['personId']
        ]);

        // Обновляем данные в таблице personLanguage.
        foreach ($_POST['language'] as $selectedOption) {
          $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
          $languageStmt->execute([':title' => $selectedOption]);
          $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

          // Проверяем, существует ли уже запись для данного personId и languageId.
          $checkStmt = $db->prepare("SELECT * FROM personLanguage WHERE personId = :personId AND languageId = :languageId");
          $checkStmt->execute([
            ':personId' => $values['personId'],
            ':languageId' => $language['languageId']
          ]);

          if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            // Если запись существует, обновляем ее.
            $updateStmt = $db->prepare("UPDATE personLanguage SET personId = :personId, languageId = :languageId WHERE personId = :personId AND languageId = :languageId");
            $updateStmt->execute([
              ':personId' => $values['personId'],
              ':languageId' => $language['languageId']
            ]);
          } else {
            // Если записи не существует, вставляем новую.
            $insertStmt = $db->prepare("INSERT INTO personLanguage (personId, languageId) VALUES (:personId, :languageId)");
            $insertStmt->execute([
              ':personId' => $values['personId'],
              ':languageId' => $language['languageId']
            ]);
          }
        }
      } catch(PDOException $e){
        print('Error : ' . $e->getMessage());
        exit();
      }
}
