<?php require_once __DIR__ . '/_header.php'; ?>

<table>
	<tr><th>Username</th><th>Rating</th></tr>
	<?php 
		foreach( $userList as $user )
		{
			echo '<tr>' .
			     '<td>' . $user->username . '</td>' .
			     '<td>' . $user->rating . '</td>' .
			     '</tr>';
		}
	?>
</table>

<?php require_once __DIR__ . '/_footer.php'; ?>
