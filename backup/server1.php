<?php
session_start();

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

$_SESSION['treba'] = '';

$message = [];
$message[ 'rezultat' ] = 1;

if(isset($_GET['player2'])){
    $_SESSION['t2'] = 1;

    if(isset($_SESSION['krenula'])) unset($_SESSION['krenuala']);
    if(isset($_SESSION['move'])) unset($_SESSION['move']);
}

if(isset($_SESSION['t2'])){
    if(isset($_GET['player1'])){
        unset($_SESSION['t']);
        unset($_SESSION['t2']);
        $_SESSION['krenula'] = 1;
        $_SESSION['move'] = 'w';
        sendJSONandExit( $message );
    }
}

else{
    if($_SESSION['move'] == 'w'){
        if(isset($_GET['igracw'])){
            $_SESSION['poslao'] = 'w';
            $_SESSION['ploca'] = $_GET['board'];
        }

        if(isset($_GET['trebamb'])){
            $_SESSION['treba'] = 'b';
        }

        if($_SESSION['treba'] == 'b' && $_SESSION['poslao'] == 'w'){
            $ploca = [];
            $ploca['ploca'] = $_SESSION['ploca'];
            $_SESSION['move'] = 'b';
            sendJSONandExit($ploca);
        }
    }

    if($_SESSION['move'] == 'b'){
        if(isset($_GET['igracb'])){
            $_SESSION['poslao'] = 'b';
            $_SESSION['ploca'] = $_GET['board'];
        }

        if(isset($_GET['trebamw'])){
            $_SESSION['treba'] = 'w';
        }

        if($_SESSION['treba'] == 'w' && $_SESSION['poslao'] == 'b'){
            $ploca = [];
            $ploca['ploca'] = $_SESSION['ploca'];
            $_SESSION['move'] = 'w';
            sendJSONandExit($ploca);
        }
    }
}

?>