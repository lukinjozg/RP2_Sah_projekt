<?php 

session_start();

require_once __DIR__ . '/../model/chessservice.class.php';

class RegistrationController
{
	public function index() 
	{
		require_once __DIR__ . '/../view/registration_index.php';
	}
    public function register(){
        $user = $_POST['user'];
        $pass = $_POST['pass'];
        if (!isset($user) || !isset($pass)) {
            $response = ['success' => false, 'error' => 'User and password cant be empty'];
            echo json_encode($response);
            exit;
        }

        $cs = new ChessService();
        if($cs -> registrationAvailable($user)){
            $cs -> register($user, $pass);
            $response = ['success' => true];
            echo json_encode($response);
            $_SESSION['username'] = $user;
            $currentUsername = $user;
            $title = 'Users ratings';
		    $userList = $cs->getAllUsers();
            require_once __DIR__ . '/../view/users_index.php';
        }
        else{
            $response = ['success' => false, 'error' => 'Username already exists.'];
            echo json_encode($response);
        }
    }
}; 

?>
