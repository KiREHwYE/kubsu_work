<!--<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Exercise 2</title>
</head>

<body style="display: flex; flex-direction: column; justify-content: center; align-items: center">

<h1>
  Form
</h1>

<form style="display: flex;flex-direction: column;width: 20%">

  <input required type="text" name="name" placeholder="Full name">
  <input required type="tel" name="phone" placeholder="Phone number">
  <input required type="email" name="email" placeholder="Email">
  <input required type="date" name="year" placeholder="Date of birth">

  <div style="flex-direction: row;margin-top: 20px">
    <input required type="radio" name="sex" value="Male">Male
    <input required type="radio" name="sex" value="Female">Female
  </div>

  <select style="margin-top: 20px" name="language">
    <option value="value1">Pascal</option>
    <option value="value2">C</option>
    <option value="value3">C++</option>
    <option value="value4">JavaScript</option>
    <option value="value5">PHP</option>
    <option value="value6">Python</option>
    <option value="value7">Java</option>
    <option value="value8">Haskel</option>
    <option value="value9">Clojure</option>
    <option value="value10">Prolog</option>
    <option value="value11">Scala</option>
  </select>

  <textarea required style="margin-top: 20px" name="biography" placeholder="Your biography"></textarea>

  <p><input required type="checkbox" name="contract_agreement" value="yes">I agree with the contract.</p>

  <input required type="submit" value="Submit">

</form>

</body>

</html>-->


<html>
<head>
  <style>
    /* Сообщения об ошибках и поля с ошибками выводим с красным бордюром. */
    .error {
      border: 2px solid red;
    }
  </style>
</head>
<body>

<?php
if (!empty($messages)) {
  print('<div id="messages">');
// Выводим все сообщения.
foreach ($messages as $message) {
print($message);
}
print('</div>');
}

// Далее выводим форму отмечая элементы с ошибками классом error
// и задавая начальные значения элементов ранее сохраненными.
?>

<form style="display: flex;flex-direction: column;width: 20%" action="" method="POST">
  <input required type="text" name="name" <?php if ($errors['name']) {print 'class="error"';} ?> value="<?php print $values['name']; ?>" placeholder="Full name">
  <input required type="tel" name="phone" <?php if ($errors['phone']) {print 'class="error"';} ?> value="<?php print $values['phone']; ?>" placeholder="Phone number">
  <input required type="email" name="email" <?php if ($errors['email']) {print 'class="error"';} ?> value="<?php print $values['email']; ?>" placeholder="Email">
  <input required type="date" name="year" <?php if ($errors['year']) {print 'class="error"';} ?> value="<?php print $values['year']; ?>" placeholder="Date of birth">

  <div style="flex-direction: row;margin-top: 20px">
      <input required type="radio" name="sex" <?php if ($errors['sex']) {print 'class="error"';} ?> value="M" <?php if ($values['sex'] == 'M') {print 'checked';} ?>>Male
      <input required type="radio" name="sex" <?php if ($errors['sex']) {print 'class="error"';} ?> value="F" <?php if ($values['sex'] == 'F') {print 'checked';} ?>>Female
  </div>

  <select style="margin-top: 20px" name="language" multiple <?php if ($errors['language']) {print 'class="error"';} ?>>
    <option value="value1">Pascal</option>
    <option value="value2">C</option>
    <option value="value3">C++</option>
    <option value="value4">JavaScript</option>
    <option value="value5">PHP</option>
    <option value="value6">Python</option>
    <option value="value7">Java</option>
    <option value="value8">Haskel</option>
    <option value="value9">Clojure</option>
    <option value="value10">Prolog</option>
    <option value="value11">Scala</option>
  </select>

  <textarea required style="margin-top: 20px" name="biography" <?php if ($errors['biography']) {print 'class="error"';} ?> placeholder="Your biography"><?php print htmlspecialchars($values['biography']); ?></textarea>

  <p><input required type="checkbox" name="contract_agreement" <?php if ($errors['contract_agreement']) {print 'class="error"';} ?> value="Yes" <?php if ($values['contract_agreement'] == 'Yes') {print 'checked';} ?>>I agree with the contract.</p>

  <input required type="submit" value="Submit">
</form>
</body>
</html>
