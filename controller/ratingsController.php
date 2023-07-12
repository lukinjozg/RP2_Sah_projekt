<?php 

session_start();

require_once __DIR__ . '/../model/chessservice.class.php';

class RatingsController
{
	public function index() 
	{
		$cs = new ChessService();

		$currentUsername = $_SESSION['username'];
		$title = 'users ratings';
		$userList = $cs->getAllUserRatings();

		require_once __DIR__ . '/../view/users_index.php';
	}


	public function search() 
	{
		$currentUsername = $_SESSION['username'];
		$title = 'Search for user by username';

		require_once __DIR__ . '/../view/user_search.php';
	}

	public function searchResults() 
	{
		$cs = new ChessService();


		if( !isset( $_POST['user'] ) || !preg_match( '/^[a-zA-Z ,-.]+$/', $_POST['user'] ) )
		{
			header( 'Location: index.php?rt=ratings/search');
			exit();
		}

		$currentUsername = $_SESSION['username'];

		if($cs->getIdByUsername($_POST['user']) === null){
			$title = 'No user with requested username';
			require_once __DIR__ . '/../view/user_search.php';
			return;
		}

		$title = 'Rating changes of user ' . $_POST['user'];
		$ratings = $cs->getSingleUserRatings($cs->getIdByUsername($_POST['user']));

		$ratingList = [];

		foreach($ratings as $rating){
			$ratingList[] = $rating -> rating;
		}

		print_r($ratingList);

		require_once __DIR__ . '/../view/rating_index.php';
	}
}; 

?>
