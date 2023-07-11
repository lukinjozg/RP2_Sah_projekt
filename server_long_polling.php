<?php

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
    $array = readFromFile('status');
    for ($i = 0; $i < 16; $i++) {
        if (!isset($array[$i])) {
            return $i; // vrati par
        }
    }

    return null;
}

function getLastBoard($pairId) {
    $array = readFromFile('board');
    if (!isset($array[$pairId])) {
        return [];
    }

    return $array[$pairId];
}

function findWaitingPair() {
    $array = readFromFile('status');
    if (empty($array)) {
        return null;
    }

    foreach ($array as $pairId => $status) {
        if ($status === 'waiting') {
            return $pairId;
        }
    }

    return null;
}

function readFromFile($string) {
    $filename = '/student1/lukmila/rp2_projekt_files/' . $string . '.txt';
    
    $data = file_get_contents($filename);

    $array = unserialize($data);

    return $array;
}

function writeToFile($string, $array) {
    $filename = '/student1/lukmila/rp2_projekt_files/' . $string . '.txt';

    $data = serialize($array);

    file_put_contents($filename, $data);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'gameover') { // poziva se kad user pobjedi
    if (!isset($_POST['user'])) {
        $response = ['success' => false, 'error' => 'User empty'];
        sendJSONandExit($response);
    }
    $user = $_POST['user'];
    
    $array = readFromFile('gameid');
    $pairId = $array[$user];
    
    if (!isset($pairId)) { // user nije u igri
        $response = ['success' => false, 'error' => 'User not in game'];
        sendJSONandExit($response);
    }
    unset($array[$user]);

    $drugi = '';
    foreach ($array as $ime => $broj) {
        if ($broj == $pairId) {
            $drugi = $ime;
            break;
        }
    }
    
    if(!empty($drugi)){
        unset($array[$drugi]);
    }
    writeToFile('gameid', $array);

    $array = readFromFile('status');
    unset($array[$pairId]);
    writeToFile('status', $array);

    $array = readFromFile('last');
    unset($array[$pairId]);
    writeToFile('last', $array);

    $array = readFromFile('white');
    unset($array[$pairId]);
    writeToFile('white', $array);

    $array = readFromFile('board');
    $array[$pairId] = [];
    writeToFile('board', $array);

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
    $array = readFromFile('gameid');
    if (isset($array[$user])) {
        $response = ['success' => false, 'error' => 'User already in game.'];
        sendJSONandExit($response);
    }
    $pairId = findWaitingPair();
    if ($pairId !== null) {
        $array = readFromFile('white');
        $array[$pairId] = $user;
        writeToFile('white', $array);

        $array = readFromFile('status');
        $array[$pairId] = 'paired';
        writeToFile('status', $array);

        $array = readFromFile('gameid');
        $array[$user] = $pairId;
        writeToFile('gameid', $array);

        $array = readFromFile('last');
        $response = ['success' => true, 'turn' => 'white', 'opponent' => $array[$pairId]];
    }
    else {
        $pairId = generatePairId();
        if ($pairId !== NULL) {
            $array = readFromFile('status');
            $array[$pairId] = 'waiting';
            writeToFile('status', $array);

            $array = readFromFile('gameid');
            $array[$user] = $pairId;
            writeToFile('gameid', $array);

            $array = readFromFile('last');
            $array[$pairId] = $user;
            writeToFile('last', $array);
            
            $pollingFile = '/student1/lukmila/rp2_projekt_files/status.txt';

            $error = '';
            if (!file_exists($pollingFile))
                $error = $error . 'Datoteka ' . $pollingFile . ' ne postoji. ';
            else {
                if (!is_readable($pollingFile))
                    $error = $error . 'Ne mogu čitati iz datoteke ' . $pollingFile . '. ';

                if (!is_writable( $pollingFile ))
                    $error = $error . 'Ne mogu pisati u datoteku ' . $pollingFile . '. ';
            } 

            if ($error !== '') {
                $response = ['success' => false, 'error' => $error];
                sendJSONandExit( $response );
            }

            $lastmodif = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
            $currentmodif = filemtime($pollingFile);

            while ($currentmodif <= $lastmodif) {
                usleep(10000); // sustav malo odmori dok pri cekanju novog rezultata
                clearstatcache();
                $currentmodif = filemtime($pollingFile);
            }

            $array = readFromFile('status');
            if (!isset($array[$pairId])) {
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
    
    $array = readFromFile('gameid');
    $pairId = $array[$user];
    
    if (!isset($pairId)) {
        $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
        sendJSONandExit($response);
    }

    $pollingFile = '/student1/lukmila/rp2_projekt_files/board.txt';

    $error = '';
    if (!file_exists($pollingFile))
        $error = $error . 'Datoteka ' . $pollingFile . ' ne postoji. ';
    else {
        if (!is_readable($pollingFile))
            $error = $error . 'Ne mogu čitati iz datoteke ' . $pollingFile . '. ';

        if (!is_writable( $pollingFile ))
            $error = $error . 'Ne mogu pisati u datoteku ' . $pollingFile . '. ';
    } 

    if ($error !== '') {
        $response = ['success' => false, 'error' => $error];
        sendJSONandExit( $response );
    }

    $lastmodif = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
    $currentmodif = filemtime($pollingFile);

    while ($currentmodif <= $lastmodif) {
        usleep(10000); // sustav malo odmori dok pri cekanju novog rezultata
        clearstatcache();
        $currentmodif = filemtime($pollingFile);
    }

    $array = readFromFile('board');
    $lastBoard = $array[$pairId];
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

    $array = readFromFile('gameid');
    $pairId = $array[$_POST['user']];

    if (!isset($pairId)) {
        $response = ['success' => false, 'error' => 'Drugi igrac je izasao iz igre'];
        sendJSONandExit($response);
    }

    $array = readFromFile('board');
    $array[$pairId] = $board;
    writeToFile('board', $array);

    $array = readFromFile('last');
    $array[$pairId] = $user;
    writeToFile('last', $array);

    $response = ['success' => true];
    sendJSONandExit($response);
}

else { // netocna naredba
    $response = ['success' => false, 'error' => 'Invalid action'];
    sendJSONandExit($response);
}

?>