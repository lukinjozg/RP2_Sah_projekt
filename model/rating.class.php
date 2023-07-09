<?php

class Rating
{
	protected $id, $id_user, $rating;

	function __construct( $id, $id_user, $rating )
	{
		$this->id = $id;
		$this->id_user = $id_user;
		$this->rating = $rating;
	}

	function __get( $prop ) { return $this->$prop; }
	function __set( $prop, $val ) { $this->$prop = $val; return $this; }
}

?>
