<?php require_once __DIR__ . '/_header.php'; ?>

<form method="post" action="index.php?rt=ratings/searchResults">
	Search for user by username:
	<input type="text" name="user" />

	<button type="submit">Search</button>
</form>

<?php require_once __DIR__ . '/_footer.php'; ?>
