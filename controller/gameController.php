<?php 

require_once __DIR__ . '/../model/chessservice.class.php';

class GameController
{
	public function index() 
	{
		require_once __DIR__ . '/../view/board.php';
	}

    function sendJSONandExit( $message )
    {
        // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
        header( 'Content-type:application/json;charset=utf-8' );
        echo json_encode( $message );
        flush();
        exit( 0 );
    }

    public function generatePairId() {
        for ($i = 0; $i < 16; $i++) {
            $pairId = dechex($i);
            if (!isPairIdTaken($pairId)) {
                return $pairId; // vrati par
            }
        }
    
        return null;
    }
    
    public function isPairIdTaken($pairId) {
        return isset($_SESSION['status'][$pairId]);
    }
    
    public function storeLastBoard($pairId, $board) {
        $_SESSION['board'][$pairId] = $board;
    }
    
    public function getLastBoard($pairId) {
        if (!isset($_SESSION['board'][$pairId])) {
            return [];
        }
    
        return $_SESSION['board'][$pairId];
    }
    
    public function findWaitingPair() {
        if (!isset($_SESSION['status'])) {
            return null;
        }
    
        foreach ($_SESSION['status'] as $pairId => $status) {
            if ($status === 'waiting') {
                return $pairId;
            }
        }
    
        return null;
    }

    public function gameplay()
    {
        $cs = new ChessService();

        session_start();

        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action == 'gameover') { // poziva se kad user pobjedi
            if (!isset($_POST['user'])) {
                $response = ['success' => false, 'error' => 'User empty'];
                sendJSONandExit($response);
            }
            $user = $_POST['user'];
            
            $pairId = $_SESSION['gameid'][$user];
            
            if (!isset($_SESSION['gameid'][$user])) { // user nije u igri
                $response = ['success' => false, 'error' => 'User not in game'];
                sendJSONandExit($response);
            }
            unset($_SESSION['gameid'][$user]);

            $drugi = '';
            if (isset($_SESSION['white'][$pairId]) && $_SESSION['white'][$pairId] != $user) {
                $drugi = $_SESSION['white'][$pairId];
            }
            else if(isset($_SESSION['black'][$pairId]) && $_SESSION['black'][$pairId] != $user){
                $drugi = $_SESSION['black'][$pairId];
            }
            
            if(!empty($drugi)){
                unset($_SESSION['gameid'][$drugi]);
            }

            unset($_SESSION['status'][$pairId]);
            unset($_SESSION['last'][$pairId]);
            unset($_SESSION['white'][$pairId]);
            unset($_SESSION['black'][$pairId]);

            $_SESSION['board'][$pairId] = [];

            $ending = $_POST['ending']; // pise 'win', 'lose' ili 'draw'

            $ratings = $cs->getLastUserRating($cs->getIdByUsername($_POST['user']));
            $rating = $ratings->rating;
            //potrebno je za oba igraca updateati ratinge
            if ($ending == 'win') {
                // ovdje pozvati funkciju za dodati rating i update tablice
                $id = $cs ->getIdByUsername($user);
                $cs -> updateRating($id, $rating + 100);
            }
            else if ($ending == 'lose') {
                // ovdje pozvati funckiju za oduzeti rating i update tablice
                $id = $cs ->getIdByUsername($user);
                $cs -> updateRating($id, $rating - 100);
            }
            else if ($ending == 'draw') {
                // ovdje pozvati funkciju za update tablice
                $id = $cs ->getIdByUsername($user);
                $cs -> updateRating($id, $rating);
            }
            else {
                $response = ['success' => false, 'error' => 'Invalid game ending'];
                sendJSONandExit($response);
            }

            $response = ['success' => true];
            sendJSONandExit($response);
        }
        else if ($action === 'join') { // pokreni igru
            $user = $_POST['user'];
            if (isset($_SESSION['gameid'][$user])) {
                $response = ['success' => false, 'error' => 'User already in game.'];
                sendJSONandExit($response);
            }
            $pairId = findWaitingPair();
            if ($pairId !== null) {
                $_SESSION['white'][$pairId] = $user;
                $_SESSION['status'][$pairId] = 'paired';
                $_SESSION['gameid'][$user] = $pairId;
                $response = ['success' => true, 'turn' => 'white', 'opponent' => $_SESSION['black'][$pairId]];
            }
            else {
                $pairId = generatePairId();
                if ($pairId !== NULL) {
                    $_SESSION['black'][$pairId] = $user;
                    $_SESSION['status'][$pairId] = 'waiting';
                    $_SESSION['gameid'][$user] = $pairId;
                    $_SESSION['last'][$pairId] = $user;
                    while (isset($_SESSION['status'][$pairId]) && $_SESSION['status'][$pairId] == 'waiting') {
                        usleep(10000); // sustav se malo odmori
                    }

                    if (!isset($_SESSION['status'][$pairId])) {
                        $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
                        sendJSONandExit($response);
                    }
                    $response = ['success' => true, 'turn' => 'black', 'opponent' => $_SESSION['white'][$pairId]];
                }
                else {
                    $response = ['success' => false, 'error' => 'No available pair IDs'];
                }
            }

            sendJSONandExit($response);
        }
        else if ($action === 'reach') { // je li protivnik napravio potez
            $user = $_POST['user'];
            $pairId = $_SESSION['gameid'][$user];
            
            if (!isset($pairId)) {
                $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
                sendJSONandExit($response);
            }

            $lastPlayer = $_SESSION['last'][$pairId];

            while (isset($lastPlayer) && $lastPlayer == $user) {
                usleep(10000); // sustav malo odmori dok pri cekanju novog rezultata
                $lastPlayer = $_SESSION['last'][$pairId];
            }

            $lastBoard = $_SESSION['board'][$pairId];
            if (empty($lastBoard)) { // drugi igrac je u meduvremenu izasao iz igre
                $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
                sendJSONandExit($response);
            }
            $response = ['success' => true, 'board' => $lastBoard];
            sendJSONandExit($response);
        }
        else if ($action === 'send') { // napravi potez
            $pairId = $_SESSION['gameid'][$_POST['user']];
            $user = isset($_POST['user']) ? $_POST['user'] : '';
            $board = isset($_POST['board']) ? $_POST['board'] : [];

            if (empty($user) || empty($board)) {
                $response = ['success' => false, 'error' => 'User and board cannot be empty'];
                sendJSONandExit($response);
            }

            if (!isset($pairId)) {
                $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
                sendJSONandExit($response);
            }

            storeLastBoard($pairId, $board);
            $_SESSION['last'][$pairId] = $user;

            $response = ['success' => true];
            sendJSONandExit($response);
        }
        else { // netocna naredba
            $response = ['success' => false, 'error' => 'Invalid action'];
            sendJSONandExit($response);
        }
    }
}; 

?>