<?php

	session_start();
    if(isset($_SESSION['usr_id'])){
        $sessionUser = $_SESSION['usr_id'];
    }else{
        header( "Location: /") ;  
    }
$user_id = $_GET['user_id'];

    require_once('connect.php');

    //lookup all links from the xml file if length of q>0
    if (strlen($user_id)>0) {
           $query = $connect->query("
			       INSERT INTO following(user_me, following_user) VALUES ($sessionUser, $user_id)
		   ");
      }

      header( "Location: /userProfile.php?user_id=$user_id" ) ;   
?>