<?php
/* Smarty version 4.3.2, created on 2023-08-04 14:38:43
  from '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.2',
  'unifunc' => 'content_64cd0d73788e83_69850750',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2831d1ff434bd238b63be263375a1ef1de19d190' => 
    array (
      0 => '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl',
      1 => 1691159915,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64cd0d73788e83_69850750 (Smarty_Internal_Template $_smarty_tpl) {
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
</head>
<body>
<form action="/api/auth" method="post">
  <div class="first">
    <label>Имя:
      <input type="text">
    </label>
  </div>
  <div class="container">
    <label>Фамилия:
      <input type="text">
    </label>
  </div>
  <div class="container">
    <label>Возраст:
      <input type="number">
    </label>
  </div>
  <div class="container">
    <label>Пол:
      <select name="sex">
        <option value="w">женский</option>
        <option value="m">мужской</option>
      </select>
    </label>
  </div>
  <div class="container">
    <label>Телефон:
      <input type="text">
    </label>
  </div>
  <div class="container">
    <label>Email:
      <input type="email">
    </label>
  </div>
  <div class="button">
    <button style="font-size: 16px; padding: 10px">Добавить контакт в AmoCRM</button>
  </div>
</form>
</body>
</html><?php }
}
