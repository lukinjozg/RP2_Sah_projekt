<?php 

session_start();

class IndexController
{
	public function index() 
	{
		// Samo preusmjeri na users podstranicu.
		header( 'Location: index.php?rt=users' );
	}
}; 

?>
