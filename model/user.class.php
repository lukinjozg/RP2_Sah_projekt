<?php

class User
{
	protected $id, $username, $password, $wins, $losses, $rating;

	function __construct( $id, $username, $password, $wins, $losses, $rating )
	{
		$this->id = $id;
		$this->username = $username;
		$this->wins = $wins;
		$this->password = $password;
		$this->losses = $losses;
		$this->rating = $rating;
	}

	function __get( $prop ) { return $this->$prop; }
	function __set( $prop, $val ) { $this->$prop = $val; return $this; }
}

?>

