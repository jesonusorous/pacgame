<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] == 'admin') {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background: #031226;
            color: white;
            font-family: Arial;
            margin: 0;
        }

        .header {
            background: #003b88;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }

        .card {
            background: #062544;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            width: 350px;
        }

        .btn {
            padding: 17px 25px;
            background: #003b88;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 25px;
            width: 80%;
            margin-top: 12px;
        }

        .btn:hover {
            background: #0055cc;
        }
    </style>
</head>

<body>



<div class="content">
    <div class="card">
        <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

        <button class="btn" onclick="window.location='pacmangame.php'">PLAY GAME</button>
        <button class="btn" onclick="window.location='leaderboards.php'">LEADERBOARDS</button>
        <button class="btn" onclick="window.location='logout.php'">Log Out</button>
    </div>
</div>

</body>
</html>

