<?php 

require_once __DIR__ . '/../model/chessservice.class.php';

class LoginController
{
	public function index() 
	{
		require_once __DIR__ . '/../view/login_index.php';
	}
    public function loginVerification(){
        $user = $_POST['user'];
        $pass = $_POST['pass'];
        if (!isset($user) || !isset($pass)) {
            $response = ['success' => false, 'error' => 'User and password cant be empty'];
            echo json_encode($response);
            require_once __DIR__ . '/../view/login_index.php';
            exit;
        }

        $cs = new ChessService();
        if($cs -> loginVerification($user, $pass)){
            $response = ['success' => true];
            echo json_encode($response);
            $title = 'Users ratings';
		    $userList = $cs->getAllUsers();
            require_once __DIR__ . '/../view/users_index.php';
        }
        else{
            $response = ['success' => false, 'error' => 'Invalid username or password.'];
            echo json_encode($response);
            require_once __DIR__ . '/../view/login_index.php';
        }
    }
}; 

?>
