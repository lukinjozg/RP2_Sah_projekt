<?php

require_once __DIR__ . '/../app/database/db.class.php';
require_once __DIR__ . '/user.class.php';
require_once __DIR__ . '/rating.class.php';

class ChessService
{
	function loginVerification($username, $password){
		try
		{
			$db = DB::getConnection();
			$query = "SELECT * FROM users WHERE username = :username";
			$statement = $db->prepare($query);
			$statement->execute(array('username' => $username));
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }
		$result = $statement->fetchAll();
		
		if ($result) {
			foreach ($result as $osoba) {
				if (password_verify($password, $osoba['password'])) {
					return true;
				}
			}
		}

		return false;
	}
	function getUserById( $id )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id, username, password, wins, losses, rating FROM users WHERE id=:id' );
			$st->execute( array( 'id' => $id ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return new User( $row['id'], $row['username'], $row['password'], $row['wins'], $row['losses'], $row['rating']);
	}


	function getAllUsers( )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id, username, password, wins, losses, rating FROM users ORDER BY rating DESC' );
			$st->execute();
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$arr = array();
		while( $row = $st->fetch() )
		{
			$arr[] = new User( $row['id'], $row['username'], $row['password'], $row['wins'], $row['losses'], $row['rating']);
		}

		return $arr;
	}


	function getLastUserRating( $id_user )
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id, id_user, rating FROM rating_changes WHERE id_user=:id_user AND id = (SELECT MAX(id) FROM rating_changes)' );
			$st->execute( array( 'id_user' => $id_user ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return new Rating( $row['id'], $row['id_user'], $row['rating'] );
	}


	function getAllUserRatings()
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id, id_user, rating FROM rating_changes ORDER BY id' );
			$st->execute();
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$arr = array();
		while( $row = $st->fetch() )
		{
			$arr[] = new Rating( $row['id'], $row['id_user'], $row['rating'] );
		}

		return $arr;
	}

	function getSingleUserRatings($id_user)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT id, id_user, rating FROM rating_changes WHERE id_user=:id_user ORDER BY id' );
			$st->execute( array( 'id_user' => $id_user ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$arr = array();
		while( $row = $st->fetch() )
		{
			$arr[] = new Rating( $row['id'], $row['id_user'], $row['rating'] );
		}

		return $arr;
	}

	function getIdByUsername($username)
	{
		try
		{
			$db = DB::getConnection();
			$st = $db->prepare( 'SELECT * FROM users WHERE username=:username' );
			$st->execute( array( 'username' => $username ) );
		}
		catch( PDOException $e ) { exit( 'PDO error ' . $e->getMessage() ); }

		$row = $st->fetch();
		if( $row === false )
			return null;
		else
			return $row['id'];
	}

};

?>

