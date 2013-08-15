<h2>Install the application</h2>
#{validationErrors}
<form method="POST">
  <fieldset>
    <legend>Administrator user</legend>
    <input type="text" placeholder="Email" name="sEmail" />
    <fieldset>
      <input type="password" placeholder="Password" name="sPassword" />
      <input type="password" placeholder="Confirm password" name="sPasswordConfirm" />
    </fieldset>
  </fieldset>
  <input type="submit" name="submit" class="btn btn-large btn-primary" value="Install" />
</form>
