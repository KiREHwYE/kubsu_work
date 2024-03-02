<?php
// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // В суперглобальном массиве $_GET PHP хранит все параметры, переданные в текущем запросе через URL.
  if (!empty($_GET['save'])) {
    // Если есть параметр save, то выводим сообщение пользователю.
    print('Спасибо, результаты сохранены.');
  }
  // Включаем содержимое файла form.php.
  include('index.html');
  // Завершаем работу скрипта.
  exit();
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в XML-файл.

// Проверяем ошибки.
$errors = FALSE;
if (empty($_POST['name']) || strlen($_POST['name'] > 128) {
  print('Заполните корректно имя.<br/>');
  $errors = TRUE;
}

if (empty($_POST['year']) || !is_numeric($_POST['year']) || !preg_match('/^\d+$/', $_POST['year']) || strlen((string)$_POST['year']) > 10) {
  print('Заполните корректно год.<br/>');
  $errors = TRUE;
}

if (empty($_POST['phone']) || !is_numeric($_POST['phone']) || !preg_match('/^\d+$/', $_POST['phone']) || strlen((string)$_POST['phone']) != 11) {
  print('Заполните корректно номер телефона.<br/>');
  $errors = TRUE;
}

if (empty($_POST['email']) || !preg_match('^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$', $_POST['email']) || strlen((string)$_POST['email']) != 11) {
  print('Заполните корректно номер телефона.<br/>');
  $errors = TRUE;
}

if (empty($_POST['sex']) || strlen($_POST['sex']) != 1) {
  print('Заполните корректно пол.<br/>');
  $errors = TRUE;
}

if (strlen($_POST['biography']) > 256) {
  print('Заполните корректно биографию.<br/>');
  $errors = TRUE;
}


// *************
// Тут необходимо проверить правильность заполнения всех остальных полей.
// *************

if ($errors) {
  // При наличии ошибок завершаем работу скрипта.
  exit();
}

// Сохранение в базу данных.

// $user = 'u67397'; // Заменить на ваш логин uXXXXX
// $pass = '2392099'; // Заменить на пароль, такой же, как от SSH
// $db = new PDO('mysql:host=kubsu-dev.ru;dbname=u67397', $user, $pass,
//   [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // Заменить test на имя БД, совпадает с логином uXXXXX
//
// // Подготовленный запрос. Не именованные метки.
// try {
//   $stmt = $db->prepare("INSERT INTO application SET name = ?");
//   $stmt->execute([$_POST['fio']]);
// }
// catch(PDOException $e){
//   print('Error : ' . $e->getMessage());
//   exit();
// }

//  stmt - это "дескриптор состояния".
 
//  Именованные метки.
//$stmt = $db->prepare("INSERT INTO test (label,color) VALUES (:label,:color)");
//$stmt -> execute(['label'=>'perfect', 'color'=>'green']);
 
//Еще вариант
/*$stmt = $db->prepare("INSERT INTO users (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
$stmt->bindParam(':firstname', $firstname);
$stmt->bindParam(':lastname', $lastname);
$stmt->bindParam(':email', $email);
$firstname = "John";
$lastname = "Smith";
$email = "john@test.com";
$stmt->execute();
*/

// Делаем перенаправление.
// Если запись не сохраняется, но ошибок не видно, то можно закомментировать эту строку чтобы увидеть ошибку.
// Если ошибок при этом не видно, то необходимо настроить параметр display_errors для PHP.
header('Location: ?save=1');
