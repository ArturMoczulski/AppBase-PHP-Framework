<h2>Install the application</h2>
#{validationErrors}
<form method="POST">
  <fieldset>
    <legend>Administrator user</legend>
    <label for="sEmail">Email</label>
    <input type="text" name="sEmail" />
    <fieldset>
      <label for="sPassword">New password:</label>
      <input type="password" name="sPassword" />
      <label for="sPasswordConfirm">Repeat password:</label>
      <input type="password" name="sPasswordConfirm" />
    </fieldset>      
  </fieldset>
  <input type="submit" name="submit" value="Install" />
</form>
