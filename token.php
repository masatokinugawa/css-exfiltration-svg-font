<?php
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 1);
session_start();

header('Content-Type: text/plain');
header('X-Content-Type-Options:nosniff');
header('Cache-Control: no-cache');
if(isset($_SESSION['leak'])){
    echo $_SESSION['leak'];
}