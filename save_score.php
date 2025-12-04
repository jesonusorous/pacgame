<?php
session_start();
include 'db.php';

if(!isset($_SESSION['username']) || $_SESSION['role'] == 'admin'){
    exit();
}

if(isset($_POST['score'])){
    $score = intval($_POST['score']);
    $username = $_SESSION['username'];

    // Get user id
    $query = "SELECT id FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($row = mysqli_fetch_assoc($result)){
        $user_id = $row['id'];

        // Check if user already has a score
        $check = "SELECT score FROM leaderboards WHERE user_id=$user_id LIMIT 1";
        $res = mysqli_query($conn, $check);

        if(mysqli_num_rows($res) > 0){
            $existing = mysqli_fetch_assoc($res);
            // Update only if new score is higher
            if($score > $existing['score']){
                $update = "UPDATE leaderboards SET score=$score, date_achieved=NOW() WHERE user_id=$user_id";
                mysqli_query($conn, $update);
            }
        } else {
            // Insert new score if no previous entry
            $insert = "INSERT INTO leaderboards (user_id, score) VALUES ($user_id, $score)";
            mysqli_query($conn, $insert);
        }
    }
}
?>
