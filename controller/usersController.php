<?php 

require_once __DIR__ . '/../model/chessservice.class.php';

class UsersController
{
	public function index() 
	{
		$cs = new ChessService();

		$title = 'Users ratings';
		$userList = $cs->getAllUsers();

		require_once __DIR__ . '/../view/users_index.php';
	}
}; 

?>
