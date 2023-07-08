<?php

session_start();

require_once 'db.class.php';

$db = DB::getConnection();

function generatePairId() {
    for ($i = 0; $i < 16; $i++) {
        $pairId = dechex($i);
        if (!isPairIdTaken($pairId)) {
            return $pairId; // vrati par
        }
    }

    return null;
}

function isPairIdTaken($pairId) {
    return isset($_SESSION['status'][$pairId]);
}

function storeLastBoard($pairId, $board) {
    $_SESSION['board'][$pairId] = $board;
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

if ($action == 'win') { // poziva se kad user pobjedi
    if(!isset($_POST['user'])){
        $response = ['success' => false, 'error' => 'User empty'];
        echo json_encode($response);
        exit;
    }

    // ovdje napisati kod za updateat tablice sa ratinzima
    // za svakog igraca se zasebno zove win/lose ne treba
    // updateat bodove koje drugi igrac gubi

    $response = ['success' => true];
    echo json_encode($response);
}
else if ($action == 'lose') { // poziva se kad user izgubi
    if(!isset($_POST['user'])){
        $response = ['success' => false, 'error' => 'User empty'];
        echo json_encode($response);
        exit;
    }

    // ovdje napisati kod za updateat tablice sa ratinzima
    // za svakog igraca se zasebno zove win/lose ne treba
    // updateat bodove koje drugi igrac gubi

    $response = ['success' => true];
    echo json_encode($response);
}
else if ($action == 'draw') { // poziva se kad je izjednaceno
    if(!isset($_POST['user'])){
        $response = ['success' => false, 'error' => 'User empty'];
        echo json_encode($response);
        exit;
    }

    // ovdje napisati kod za updateat tablice sa ratinzima
    // za svakog igraca se zasebno zove win/lose ne treba
    // updateat bodove koje drugi igrac gubi

    $response = ['success' => true];
    echo json_encode($response);
}
else if ($action == 'profile') { // pokazivanje profila
    if(!isset($_POST['user'])){
        $response = ['success' => false, 'error' => 'User empty'];
        echo json_encode($response);
        exit;
    }
    $user = $_POST['user'];
    $query = "SELECT * FROM rating_changes WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();
    $response = ['success' => true, 'table' => $result];
    echo json_encode($response);
}
else if ($action == 'rankings') { // pokazivanje rankingsa
    $query = "SELECT * FROM users ORDER BY rating";
    $statement = $db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $response = ['success' => true, 'table' => $result];
    echo json_encode($response);
}
else if ($action == 'login') { // ulogiraj se
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if (!isset($user) || !isset($pass)) {
        $response = ['success' => false, 'error' => 'User and password cant be empty'];
        echo json_encode($response);
        exit;
    }
    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();
    
    if ($result) {
        foreach ($result as $osoba) {
            if (password_verify($pass, $osoba['password'])) {
                $response = ['success' => true];
                echo json_encode($response);
                exit;
            }
        }
    }
    $response = ['success' => false, 'error' => 'Invalid username or password.'];
    echo json_encode($response);
}
else if ($action == 'register') { // registracija
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if(!isset($user) || !isset($pass)){
        $response = ['success' => false, 'error' => 'User and password cant be empty'];
        echo json_encode($response);
        exit;
    }
    $query = "SELECT * FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement->execute(array('username' => $user));
    $result = $statement->fetchAll();
    
    if ($result) {
        $response = ['success' => false, 'error' => 'User already exists.'];
        echo json_encode($response);
        exit;
    }

    $statement = $db->prepare('INSERT INTO users(username, password) VALUES (:username, :password)');
    $statement->execute(array('username' => $user, 'password' => password_hash($pass, PASSWORD_DEFAULT)));
    
    $response = ['success' => true];
    echo json_encode($response);
}
else if ($action === 'join') { // pokreni igru
    $pairId = findWaitingPair();
    if ($pairId !== null) {
        $_SESSION['status'][$pairId] = 'paired';
        $response = ['success' => true, 'turn' => 'white'];
    }
    else {
        $pairId = generatePairId();
        if ($pairId !== NULL) {
            $_SESSION['status'][$pairId] = 'waiting';
            $response = ['success' => true, 'turn' => 'black'];
        }
        else {
            $response = ['success' => false, 'error' => 'No available pair IDs'];
        }
    }

    echo json_encode($response);
}
else if ($action === 'reach') { // je li protivnik napravio potez
    $pairId = $_SESSION['gameid'][$_POST['user']];
    $lastBoard = getLastBoard($pairId);

    $response = ['success' => true, 'board' => $lastBoard];
    echo json_encode($response);
}
else if ($action === 'send') { // napravi potez
    $pairId = $_SESSION['gameid'][$_POST['user']];
    $player = isset($_POST['player']) ? $_POST['player'] : '';
    $board = isset($_POST['board']) ? json_decode($_POST['board']) : [];

    if (empty($player) || empty($board)) {
        $response = ['success' => false, 'error' => 'Player and board cannot be empty'];
        echo json_encode($response);
        exit;
    }

    storeLastBoard($pairId, $board);

    $response = ['success' => true];
    echo json_encode($response);
}
else { // netocna naredba
    $response = ['success' => false, 'error' => 'Invalid action'];
    echo json_encode($response);
}

?>