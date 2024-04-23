<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();

  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Спасибо, результаты сохранены.';
  }

  $errors = array();

  $errors['name'] = !empty($_COOKIE['name_error']);
  $errors['phone'] = !empty($_COOKIE['phone_error']);
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['year'] = !empty($_COOKIE['year_error']);
  $errors['sex'] = !empty($_COOKIE['sex_error']);
  $errors['language'] = !empty($_COOKIE['language_error']);
  $errors['biography'] = !empty($_COOKIE['biography_error']);

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


  $values = array();
  $values['language'] = empty($_COOKIE['language_value']) ? '' : $_COOKIE['language_value'];
  $values['name'] = empty($_COOKIE['name_value']) ? '' : $_COOKIE['name_value'];
  $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['year'] = empty($_COOKIE['year_value']) ? '' : $_COOKIE['year_value'];
  $values['sex'] = empty($_COOKIE['sex_value']) ? '' : $_COOKIE['sex_value'];
  $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];



  include('form.php');
}

else {
    $errors = FALSE;

    if (empty($_POST['name']) || !preg_match('/^([А-Яа-я\s]+|[A-Za-z\s]+)$/', $_POST['name'])) {
        setcookie('name_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
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
//         setcookie('language_error', '1', time() + 24 * 60 * 60);
//         $errors = TRUE;
//     } else {
//         setcookie('language_value', $_POST['language'], time() + 30 * 24 * 60 * 60);
//     }


        if (!empty($_POST['language'])) {
            // Store the selected languages in a cookie for 30 days
            setcookie('selected_languages', serialize($_POST['language']), time() + (86400 * 30), "/");
        }
        if (isset($_COOKIE['selected_languages']) && !empty($_COOKIE['selected_languages'])) {
            // Unserialize the cookie to get the array of selected languages
            $selected_languages = unserialize($_COOKIE['selected_languages']);
        } else {
            // Initialize an empty array if the cookie is not set
            $selected_languages = array();
        }

        if (empty($_POST['biography']) || strlen($_POST['biography']) > 256) {
            setcookie('biography_error', '1', time() + 24 * 60 * 60);
            $errors = TRUE;
        } else {
            setcookie('biography_value', $_POST['biography'], time() + 30 * 24 * 60 * 60);
        }

  if ($errors) {
    header('Location: index.php');
    exit();
  }
  else {
    setcookie('name_error', '', 100000);
    setcookie('phone_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('year_error', '', 100000);
    setcookie('sex_error', '', 100000);
    setcookie('language_error', '', 100000);
    setcookie('biography_error', '', 100000);
  }

    $user = 'u67397';
    $pass = '2392099';
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

      foreach ($selected_languages as $selectedOption) {
        $languageStmt = $db->prepare("SELECT languageId FROM language WHERE title = :title");
        $languageStmt->execute([':title' => $selectedOption]);
        $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

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

  setcookie('save', '1');

  header('Location: index.php');
}
