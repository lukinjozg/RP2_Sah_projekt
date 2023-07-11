<?php

// Popunjavamo tablice u bazi "probnim" podacima.
require_once __DIR__ . '/db.class.php';

seed_table_rating();

// ------------------------------------------
function seed_table_rating()
{
	$db = DB::getConnection();

	// Ubaci neke korisnike u tablicu users.
	// Uočimo da ne treba specificirati id koji se automatski poveća kod svakog ubacivanja.
	try
	{
		$st = $db->prepare( 'INSERT INTO rating_changes(id_user, rating) VALUES (:id_user, :rating)' );

		$st->execute( array( 'id_user' => 11, 'rating' => 1200 ) );
		$st->execute( array( 'id_user' => 11, 'rating' => 1400 ) );
        $st->execute( array( 'id_user' => 11, 'rating' => 1650 ) );	}
	catch( PDOException $e ) { exit( "PDO error (seed_table_users): " . $e->getMessage() ); }

	echo "Ubacio rejtinge u tablicu rating_changes.<br />";
}

?>