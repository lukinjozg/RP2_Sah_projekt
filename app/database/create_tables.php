<?php

// Stvaramo tablice u bazi (ako već ne postoje od ranije).
require_once __DIR__ . '/db.class.php';

create_table_users();
create_table_rating_changes();

// ------------------------------------------
function create_table_users()
{
	$db = DB::getConnection();

	// Stvaramo tablicu users.
	// Svaki user ima svoj id (automatski će se povećati za svakog novoubačenog korisnika), ime, prezime i password hash.
	try
	{
		$st = $db->prepare(
			'CREATE TABLE IF NOT EXISTS users (' .
			'id int NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'username varchar(50) NOT NULL,' .
			'password varchar(255) NOT NULL,'.
			'wins int,'.
			'losses int,'.
			'rating int)'
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error (create_table_users): " . $e->getMessage() ); }

	echo "Napravio tablicu users.<br />";
}



function create_table_rating_changes()
{
	$db = DB::getConnection();

	// Stvaramo tablicu loans.
	// Svaka posudba ima svoj id (automatski će se povećati za svaku novoubačenu knjigu), id korisnika koji je posudio
	// knjigu, id knjige koja se posuđuje, te datum isteka posudbe.
	try
	{
		$st = $db->prepare(
			'CREATE TABLE IF NOT EXISTS rating_changes (' .
			'id int NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
			'id_user INT NOT NULL,' .
			'rating INT)' 
		);

		$st->execute();
	}
	catch( PDOException $e ) { exit( "PDO error (create_table_loans): " . $e->getMessage() ); }

	echo "Napravio tablicu rating_changes.<br />";
}

?>
