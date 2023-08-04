<html>
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
</html>