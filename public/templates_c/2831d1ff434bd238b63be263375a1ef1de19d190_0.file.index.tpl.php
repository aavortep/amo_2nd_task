<?php
/* Smarty version 4.3.2, created on 2023-08-10 07:53:52
  from '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.2',
  'unifunc' => 'content_64d49790ca2638_88853946',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2831d1ff434bd238b63be263375a1ef1de19d190' => 
    array (
      0 => '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl',
      1 => 1691653996,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64d49790ca2638_88853946 (Smarty_Internal_Template $_smarty_tpl) {
?><html>

<head>
  <title>Форма ввода данных</title>
  <style>
    input:invalid {
      border: 2px solid red;
    }

    input:valid {
      border: 2px solid black;
    }

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
</head>

<body>
  <form action="/api/add" method="post">
    <div class="first">
      <label>Имя:
        <input type="text" name="name" id="name" required>
      </label>
    </div>
    <div class="container">
      <label>Фамилия:
        <input type="text" name="surname" id="surname" required>
      </label>
    </div>
    <div class="container">
      <label>Возраст:
        <input type="number" name="age" id="age" required>
      </label>
    </div>
    <div class="container">
      <label>Пол:
        <select name="sex" id="sex">
          <option value="женский">женский</option>
          <option value="мужской">мужской</option>
        </select>
      </label>
    </div>
    <div class="container">
      <label>Телефон:
        <input type="text" name="phone" id="phone" required>
      </label>
    </div>
    <div class="container">
      <label>Email:
        <input type="email" name="email" id="email" required>
      </label>
    </div>
    <div class="button">
      <button style="font-size: 16px; padding: 10px" id="btn">Добавить контакт в AmoCRM</button>
    </div>
  </form>

  <?php echo '<script'; ?>
>
    document.getElementById("btn").addEventListener("click", sendJSON);

    function sendJSON(event) {
      event.preventDefault();

      let name = document.querySelector('#name');
      let surname = document.querySelector('#surname');
      let age = document.querySelector('#age');
      let select = document.getElementById("sex");
      let sex = select.value;
      let phone = document.querySelector('#phone');
      let email = document.querySelector('#email');

      let xhr = new XMLHttpRequest();
      let url = "/api/add";
      xhr.open("POST", url, true);
      xhr.setRequestHeader("Content-Type", "application/json");

      let data = {
        "name": name.value,
        "surname": surname.value,
        "age": age.value,
        "sex": sex,
        "phone": phone.value,
        "email": email.value
      };

      var json_data = JSON.stringify(data);

      xhr.onload = function() {
        alert("Контакт успешно добавлен в amoCRM");
      };

      xhr.onerror = function(err) {
        alert(this.status);
      };

      xhr.send(json_data);
    }
  <?php echo '</script'; ?>
>

</body>

</html><?php }
}
