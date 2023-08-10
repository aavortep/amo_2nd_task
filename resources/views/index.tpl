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
        <select name="sex" id="sex">
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
      <button style="font-size: 16px; padding: 10px" id="btn">Добавить контакт в AmoCRM</button>
    </div>
  </form>

  <script>
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
  </script>

</body>

</html>