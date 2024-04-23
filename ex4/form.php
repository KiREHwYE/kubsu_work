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

  <select style="margin-top: 20px" name="language[]" multiple <?php if ($errors['language']) {print 'class="error"';} ?>>
    <option value="value1" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Pascal</option>
    <option value="value2" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>C</option>
    <option value="value3" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>C++</option>
    <option value="value4" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>JavaScript</option>
    <option value="value5" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>PHP</option>
    <option value="value6" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Python</option>
    <option value="value7" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Java</option>
    <option value="value8" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Haskel</option>
    <option value="value9" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Clojure</option>
    <option value="value10" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Prolog</option>
    <option value="value11" <?php if (in_array('value1', $selected_languages)) echo 'selected'; ?>>Scala</option>
  </select>

  <textarea required style="margin-top: 20px" name="biography" <?php if ($errors['biography']) {print 'class="error"';} ?> placeholder="Your biography"><?php print htmlspecialchars($values['biography']); ?></textarea>

  <p><input required type="checkbox" name="contract_agreement">I agree with the contract.</p>

  <input required type="submit" value="Submit">
</form>
</body>
</html>
