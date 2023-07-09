<?php

// Popunjavamo tablice u bazi "probnim" podacima.
require_once __DIR__ . '/db.class.php';

seed_table_users();

// ------------------------------------------
function seed_table_users()
{
	$db = DB::getConnection();

	// Ubaci neke korisnike u tablicu users.
	// Uočimo da ne treba specificirati id koji se automatski poveća kod svakog ubacivanja.
	try
	{
		$st = $db->prepare( 'INSERT INTO users(username, password, wins, losses, rating) VALUES (:username, :password, :wins, :losses, :rating)' );

		$st->execute( array( 'username' => 'milePiletina', 'password' => password_hash( 'kratke_ruke', PASSWORD_DEFAULT ), 'wins' => 0, 'losses' => 0, 'rating' => 1500 ) );
		$st->execute( array( 'username' => 'Shtef', 'password' => password_hash( 'znojan', PASSWORD_DEFAULT ), 'wins' => 0, 'losses' => 0, 'rating' => 1500 ) );
		$st->execute( array( 'username' => 'Kosfaul', 'password' => password_hash( 'karlsen', PASSWORD_DEFAULT ), 'wins' => 0, 'losses' => 0, 'rating' => 1500 ) );
	}
	catch( PDOException $e ) { exit( "PDO error (seed_table_users): " . $e->getMessage() ); }

	echo "Ubacio korisnike u tablicu users.<br />";
}

?>
