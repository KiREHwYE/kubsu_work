<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (!empty($_GET['save'])) {
    print('Спасибо, результаты сохранены.');
  }
  include('index.html');
  exit();
}

$errors = FALSE;
// if (empty($_POST['name']) || strlen($_POST['name']) > 128) {
//   print('Заполните корректно имя.<br/>');
//   $errors = TRUE;
// }

// if (empty($_POST['phone']) || !preg_match('/^\+?\d{10,11}$/', $_POST['phone'])) {
//   print('Заполните корректно номер телефона.<br/>');
//   $errors = TRUE;
// }

// if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
//   print('Заполните корректно адрес электронной почты.<br/>');
//   $errors = TRUE;
// }
//
// if (empty($_POST['sex']) || !in_array($_POST['sex'], ['Male', 'Female'])) {
//   print('Заполните корректно пол.<br/>');
//   $errors = TRUE;
// }
//
// if (strlen($_POST['biography']) > 256) {
//   print('Заполните корректно биографию.<br/>');
//   $errors = TRUE;
// }

// // Проверка языка программирования
// if (empty($_POST['language']) || !preg_match('/^value\d+$/', $_POST['language'])) {
//   print('Выберите язык программирования.<br/>');
//   $errors = TRUE;
// }

if ($errors) {
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
  $stmt = $db->prepare("INSERT INTO person (name, phone, year, sex, biography) VALUES (:name, :phone, :year, :sex, :biography)");
  $stmt->execute([
    ':name' => $_POST['name'],
    ':phone' => $_POST['phone'],
    ':year' => $_POST['year'],
    ':sex' => $_POST['sex'],
    ':biography' => $_POST['biography']
  ]);
  $personId = $db->lastInsertId();

//   $stmt = $db->prepare("INSERT INTO personAbility (personId, languageId) VALUES (:personId, :languageId)");
//   $stmt->execute([
//     ':personId' => $personId,
//     ':languageId' => $_POST['language']
//   ]);
}
catch(PDOException $e){
  print('Error : ' . $e->getMessage());
  exit();
}

header('Location: ?save=1');
?>
