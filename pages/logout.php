<?php
    session_start();
    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    $pokemons = new CardStorage();
    $users = new UserStorage();

    
    $auth = new Auth($users);
    
    $auth->logout();
    session_unset();
    session_destroy();
    header("Location: ../index.php");
?>