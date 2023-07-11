<!DOCTYPE html>
<html>
<head>
	<meta charset="utf8">
	<title>Advanced chess</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">
<form method="post" action="index.php?rt=login/loginVerification">
	Log in validation:
	<br><input type="text" name="user" /><br>
    <input type="password" name="pass" /><br>

	<button type="submit">Log in</button><br>
</form>
</div>

<div class="reg_button">
<form method="post" action="index.php?rt=registration/index">
	<button type="submit">register</button>
</form>
</div>

</body>
</html>
