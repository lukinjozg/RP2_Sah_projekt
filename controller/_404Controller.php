<?php 

require_once __DIR__ . '/../model/chessservice.class.php';

class _404Controller
{
	public function index() 
	{
		$title = 'Page not found.';

		require_once __DIR__ . '/../view/404_index.php';
	}
}; 

?>
