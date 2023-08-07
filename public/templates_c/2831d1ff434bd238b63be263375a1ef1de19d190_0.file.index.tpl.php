<?php
/* Smarty version 4.3.2, created on 2023-08-07 11:54:07
  from '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.2',
  'unifunc' => 'content_64d0db5f6e2e22_44479079',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2831d1ff434bd238b63be263375a1ef1de19d190' => 
    array (
      0 => '/home/apetrova/avortep/amo_2nd_task/resources/views/index.tpl',
      1 => 1691409236,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_64d0db5f6e2e22_44479079 (Smarty_Internal_Template $_smarty_tpl) {
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
<form action="/api/add" method="post">
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
        <option value="w">женский</option>
        <option value="m">мужской</option>
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
    <button style="font-size: 16px; padding: 10px">Добавить контакт в AmoCRM</button>
  </div>
</form>
</body>
</html><?php }
}
