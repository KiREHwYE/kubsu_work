<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (!empty($_GET['save'])) {
    print('Спасибо, результаты сохранены.');
    exit();
  }
  include('index.html');
  exit();
}

$errors = FALSE;
if (empty($_POST['name']) || strlen($_POST['name']) > 128) {
  print('Заполните корректно имя.<br/>');
  $errors = TRUE;
}

if (empty($_POST['phone']) || strlen($_POST['phone']) > 32 || !preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/', $_POST['phone'])) {
  print('Заполните корректно номер телефона.<br/>');
  $errors = TRUE;
}

if (empty($_POST['email']) || strlen($_POST['email']) > 64 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  print('Заполните корректно адрес электронной почты.<br/>');
  $errors = TRUE;
}

if (strlen($_POST['biography']) > 256) {
  print('Заполните корректно биографию.<br/>');
  $errors = TRUE;
}

if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  print('Заполните корректно адрес электронной почты.<br/>');
  $errors = TRUE;
}

if ($errors) {
    header('Location: ?error=1');
  exit();
}

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

header('Location: ?save=1');
?>
