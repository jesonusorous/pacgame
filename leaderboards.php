<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] == 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch top 10 leaderboard data
$query = "SELECT u.username, l.score, l.date_achieved 
          FROM leaderboards l 
          JOIN users u ON l.user_id = u.id 
          ORDER BY l.score DESC
          LIMIT 10";
$result = mysqli_query($conn, $query);

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
            margin-top: 30px;
        }

        .card {
            background: #062544;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            width: 500px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #003b88;
            padding: 12px;
            font-size: 18px;
        }

        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #234;
            font-size: 16px;
        }

        tr:hover {
            background: #0a355e;
        }

        .btn-back {
            margin-top: 20px;
            padding: 12px 25px;
            background: #003b88;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 18px;
        }

        .btn-back:hover {
            background: #0055cc;
        }
    </style>
</head>
<body>

<div class="header">LEADERBOARDS</div>

<div class="content">
    <div class="card">
        <table>
            <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Score</th>
            </tr>
            <?php 
            $rank = 1;
            while($row = mysqli_fetch_assoc($result)) { 
            ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo $row['score']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <button class="btn-back" onclick="window.location='dashboard.php'">Back</button>
    </div>
</div>

</body>
</html>
