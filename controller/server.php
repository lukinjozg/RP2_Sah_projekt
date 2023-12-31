<?php
session_start();

require_once 'db.class.php';

$db = DB::getConnection();

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

function generatePairId() {
    for ($i = 0; $i < 16; $i++) {
        $pairId = dechex($i);
        if (!isset($_SESSION['status'][$pairId])) {
            return $pairId; // vrati par
        }
    }

    return null;
}

function getLastBoard($pairId) {
    if (!isset($_SESSION['board'][$pairId])) {
        return [];
    }

    return $_SESSION['board'][$pairId];
}

function findWaitingPair() {
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

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'gameover') { // poziva se kad user pobjedi
    $file = 'kralj.txt';
        $user = $_POST['user'];

        $data = 'gameover1';

        $handle = fopen($file, 'a');

        fwrite($handle, $data);

        fclose($handle);
    if (!isset($_POST['user'])) {
        $response = ['success' => false, 'error' => 'User empty'];
        sendJSONandExit($response);
    }
    
    $pairId = $_SESSION['gameid'][$user];
    
    if (!isset($pairId)) { // user nije u igri
        $response = ['success' => false, 'error' => 'User not in game'];
        sendJSONandExit($response);
    }
    unset($_SESSION['gameid'][$user]);

    $drugi = '';
    foreach ($_SESSION['gameid'] as $ime => $broj) {
        if ($broj == $pairId) {
            $drugi = $ime;
            break;
        }
    }
    
    if(!empty($drugi)){
        unset($_SESSION['gameid'][$drugi]);
    }

    unset($_SESSION['status'][$pairId]);
    unset($_SESSION['last'][$pairId]);
    unset($_SESSION['white'][$pairId]);

    $_SESSION['board'][$pairId] = [];

    $ending = $_POST['ending']; // pise 'win', 'lose' ili 'draw'
    //potrebno je za oba igraca updateati ratinge
    if ($ending == 'win') {
        // ovdje pozvati funkciju za dodati rating i update tablice
    }
    else if ($ending == 'lose') {
        // ovdje pozvati funckiju za oduzeti rating i update tablice
    }
    else if ($ending == 'draw') {
        // ovdje pozvati funkciju za update tablice
    }
    else {
        $response = ['success' => false, 'error' => 'Invalid game ending'];
        sendJSONandExit($response);
    }

    $response = ['success' => true];
    sendJSONandExit($response);
}
else if ($action == 'profile') { // pokazivanje profila
    if (!isset($_POST['user'])) {
        $response = ['success' => false, 'error' => 'User empty'];
        sendJSONandExit($response);
    }
    $user = $_POST['user'];
    $query = "SELECT * FROM rating_changes WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();

    $response = ['success' => true, 'table' => $result];
    sendJSONandExit($response);
}
else if ($action == 'rankings') { // pokazivanje rankingsa
    $query = "SELECT * FROM users ORDER BY rating";
    $statement = $db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    $response = ['success' => true, 'table' => $result];
    sendJSONandExit($response);
}
else if ($action == 'login') { // ulogiraj se
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if (!isset($user) || !isset($pass)) {
        $response = ['success' => false, 'error' => 'User and password cant be empty'];
        sendJSONandExit($response);
    }
    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();
    
    if ($result) {
        foreach ($result as $osoba) {
            if (password_verify($pass, $osoba['password'])) {
                $response = ['success' => true];
                sendJSONandExit($response);
            }
        }
    }
    $response = ['success' => false, 'error' => 'Invalid username or password.'];
    sendJSONandExit($response);
}
else if ($action == 'register') { // registracija
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if(!isset($user) || !isset($pass)){
        $response = ['success' => false, 'error' => 'User and password cant be empty'];
        sendJSONandExit($response);
    }
    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();
    
    if ($result) {
        $response = ['success' => false, 'error' => 'User already exists.'];
        sendJSONandExit($response);
    }

    $statement = $db->prepare('INSERT INTO users (username, password, wins, losses, rating) VALUES (:username, :password, 0, 0, 1500)');
    $statement->execute(array('username' => $user, 'password' => password_hash($pass, PASSWORD_DEFAULT)));
    
    $response = ['success' => true];
    sendJSONandExit($response);
}
else if ($action === 'join') { // pokreni igru
    $user = $_POST['user'];
    if (isset($_SESSION['gameid'][$user])) {
        $pairId = $_SESSION['gameid'][$user];
        if ($_SESSION['status'][$pairId] == 'waiting') {
            $response = ['success' => false, 'error' => 'Opponent not yet found'];
        }
        else {
            $response = ['success' => true, 'turn' => 'black', 'opponent' => $_SESSION['white'][$pairId]];
        }
        sendJSONandExit($response);
    }
    $pairId = findWaitingPair();
    if ($pairId !== null) {
        $_SESSION['white'][$pairId] = $user;
        $_SESSION['status'][$pairId] = 'paired';
        $_SESSION['gameid'][$user] = $pairId;
        $response = ['success' => true, 'turn' => 'white', 'opponent' => $_SESSION['last'][$pairId]];
    }
    else {
        $pairId = generatePairId();
        if ($pairId !== NULL) {
            $_SESSION['status'][$pairId] = 'waiting';
            $_SESSION['gameid'][$user] = $pairId;
            $_SESSION['last'][$pairId] = $user;
            $response = ['success' => false, 'error' => 'Opponent not yet found'];
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

        $file = 'kralj.txt';
        $user = $_POST['user'];

        $data = $response['error'];

        $handle = fopen($file, 'a');

        fwrite($handle, $data);

        fclose($handle);

        sendJSONandExit($response);
    }

    $lastPlayer = $_SESSION['last'][$pairId];
    if (!isset($lastPlayer) || $lastPlayer == $user) {
        $response = ['success' => false, 'error' => 'Potez nije napravljen'];
        sendJSONandExit($response);
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
    $user = isset($_POST['user']) ? $_POST['user'] : '';
    $board = isset($_POST['board']) ? $_POST['board'] : [];

    if (empty($user) || empty($board)) {
        $response = ['success' => false, 'error' => 'User and board cannot be empty'];
        sendJSONandExit($response);
    }

    $pairId = $_SESSION['gameid'][$user];

    if (!isset($pairId)) {
        $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
        sendJSONandExit($response);
    }

    $_SESSION['board'][$pairId] = $board;
    $_SESSION['last'][$pairId] = $user;

    $response = ['success' => true];
    sendJSONandExit($response);
}

else { // netocna naredba
    $response = ['success' => false, 'error' => 'Invalid action'];
    sendJSONandExit($response);
}

?>