<?php

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

$values = array(
    'personId' => 0,
    'name' => "",
    'phone' => "",
    'email' => "",
    'year' => "",
    'sex' => "",
    'language' => [],
    'biography' => ""
);

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
  <textarea required style="margin-top: 20px" name="personId" placeholder="personId"></textarea>

  <input required type="submit" value="Change data">
</form>

</body>

<form style="display: flex;flex-direction: column;width: 20%" action="admin.php" method="POST">
  <!-- Форма и поля ввода... -->
</form>

<?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
         isset($_POST['personId']) &&
         isset($_POST['name']) &&
         isset($_POST['email']) &&
         isset($_POST['phone']) &&
         isset($_POST['year']) &&
         isset($_POST['sex']) &&
         isset($_POST['biography']) &&
         isset($_POST['language'])
    ) {
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
    }
?>

<form style="display: flex;flex-direction: column;width: 20%" action="admin.php" method="POST">
    <textarea required style="margin-top: 20px" name="personId" placeholder="Введите personId пользователя, которого хотите удалить"></textarea>
    <input required type="submit" value="Удалить этого пользователя">
</form>

<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['personId'])) {

        try {
            $stmt = $db->prepare("DELETE FROM personLanguage WHERE personId = :personId");
            $stmt->execute([':personId' => $_POST['personId']]);

            $stmt = $db->prepare("DELETE FROM personAuthentificationData WHERE personId = :personId");
            $stmt->execute([':personId' => $_POST['personId']]);

            $stmt = $db->prepare("DELETE FROM person WHERE personId = :personId");
            $stmt->execute([':personId' => $_POST['personId']]);

            header("Location: ".$_SERVER['REQUEST_URI']);

        } catch (PDOException $e) {
            echo "Ошибка выполнения запроса: " . $e->getMessage();
        }
    }


    // Запрос для подсчета количества пользователей по языкам
    $sql = "SELECT l.title, COUNT(pl.personId) AS user_count
            FROM language l
            LEFT JOIN personLanguage pl ON l.languageId = pl.languageId
            GROUP BY l.title";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table>";
        echo "<tr><th>Язык</th><th>Количество пользователей</th></tr>";
        foreach ($results as $row) {
            echo "<tr><td>{$row['title']}</td><td>{$row['user_count']}</td></tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
?>
