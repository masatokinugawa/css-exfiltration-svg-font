<?php 
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 1);
session_start();

header("Content-Type:text/plain");
if(isset($_GET['leak'])){
    $_SESSION['leak']=$_GET['leak'];
}
?>