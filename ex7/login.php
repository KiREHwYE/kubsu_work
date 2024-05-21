<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Загрузка конфигурации из файла .env
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

header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['csrf_token_login'])) {
    $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
}

$csrfTokenLogin = $_SESSION['csrf_token_login'];

function checkCsrfToken($token) {
    return !empty($_SESSION['csrf_token_login']) && hash_equals($_SESSION['csrf_token_login'], $token);
}

// Если пользователь уже залогинен, перенаправляем на index.php
if (!empty($_SESSION['login'])) {
    header('Location: ./index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!checkCsrfToken($_POST['csrf_token_login'])) {
        die('CSRF token validation failed.');
    }

    try {
        $db = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPassword, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $login = $_POST['login'];
        $password = $_POST['pass'];
        $md5Pass = md5($password);

        $stmt = $db->prepare("SELECT personId FROM personAuthentificationData WHERE login = :login AND pass = :pass");
        $stmt->execute([':login' => $login, ':pass' => $md5Pass]);
        $authData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($authData) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $authData['personId'];
            header('Location: ./index.php');
            exit();
        } else {
            echo "Неверный логин или пароль.";
            exit();
        }
    } catch(PDOException $e){
        error_log('Error : ' . $e->getMessage());
        exit();
    }
}
?>

<html>
<head>
    <style>
        .error {
            border: 2px solid red;
        }
    </style>
</head>
<body style="display: flex; flex-direction: column; justify-content: center; align-items: center">
    <h1>Login</h1>
    <form action="" method="post">
        <input type="hidden" name="csrf_token_login" value="<?php echo $csrfTokenLogin; ?>">
        <input name="login" value="<?php echo htmlspecialchars($login ?? ''); ?>" />
        <input name="pass" value="<?php echo htmlspecialchars($password ?? ''); ?>" />
        <input type="submit" value="Войти" />
    </form>
</body>
</html>
