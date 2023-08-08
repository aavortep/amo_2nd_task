<?php
/* Smarty version 4.3.2, created on 2023-08-08 16:13:50
  from '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.2',
  'unifunc' => 'content_64d269be949646_03997219',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2831d1ff434bd238b63be263375a1ef1de19d190' => 
    array (
      0 => '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl',
      1 => 1691511199,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64d269be949646_03997219 (Smarty_Internal_Template $_smarty_tpl) {
?><html>
<head>
<title>Форма ввода данных</title>
<style>
    .first {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10%;
        margin-bottom: 15px;
        margin-left: 15px;
        margin-right: 15px;
    }
    .container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 15px;
    }
    .button {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 30px;
    }
</style>
<?php echo '<script'; ?>
 src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
  function sendJSON() {
    let name = document.querySelector('#name');
    let surname = document.querySelector('#surname');
    let age = document.querySelector('#age');
    let sex = document.querySelector('#sex');
    let phone = document.querySelector('#phone');
    let email = document.querySelector('#email');

    let result = document.querySelector('.result');

    let xhr = new XMLHttpRequest();
    let url = "http://localhost:8000/api/add";
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    var data = JSON.stringify({ "name": name.value,
                                "surname": surname.value,
                                "age": age.value,
                                "sex": sex.value,
                                "phone": phone.value,
                                "email": email.value });
    xhr.send(data);
  }
<?php echo '</script'; ?>
>
</head>
<body>
<form>
  <div class="first">
    <label>Имя:
      <input type="text" name="name" id="name">
    </label>
  </div>
  <div class="container">
    <label>Фамилия:
      <input type="text" name="surname" id="surname">
    </label>
  </div>
  <div class="container">
    <label>Возраст:
      <input type="number" name="age" id="age">
    </label>
  </div>
  <div class="container">
    <label>Пол:
      <select name="sex">
        <option value="женский">женский</option>
        <option value="мужской">мужской</option>
      </select>
    </label>
  </div>
  <div class="container">
    <label>Телефон:
      <input type="text" name="phone" id="phone">
    </label>
  </div>
  <div class="container">
    <label>Email:
      <input type="email" name="email" id="email">
    </label>
  </div>
  <div class="button">
    <button style="font-size: 16px; padding: 10px" onclick="sendJSON()">Добавить контакт в AmoCRM</button>
  </div>
</form>
</body>
</html><?php }
}
