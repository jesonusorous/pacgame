<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// UPDATE STATUS
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    // Get specific user
    $q = mysqli_query($conn, "SELECT status FROM users WHERE id=$id AND role='user'");
    $row = mysqli_fetch_assoc($q);

    if ($row) {
        // FIXED: Change $u to $row
        $newStatus = ($row['status'] === 'activated') ? 'restricted' : 'activated';
        mysqli_query($conn, "UPDATE users SET status='$newStatus' WHERE id=$id");
    }

    header("Location: accman.php");
    exit();
}

// DELETE USER + LEADERBOARD
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM leaderboards WHERE user_id=$id");
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");

    header("Location: accman.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Management</title>
    <style>
        body { background:#021833; font-family: Arial; color:white; margin:0; }
        .header { background:#0055aa; padding:15px; text-align:center; font-size:26px; font-weight:bold; }
        .content { padding:25px; }

        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:12px; text-align:center; font-size:18px; }
        th { background:#003b88; }
        tr:nth-child(even) { background:#062544; }
        tr:nth-child(odd) { background:#07305a; }

        .status-btn {
            padding:8px 15px;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
            color:white;
        }
        .activated { background:#00aa4f; }
        .restricted { background:#aa0000; }

        .delete-btn {
            padding:8px 15px;
            background:#aa0000;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
        }
        .delete-btn:hover { background:#cc0000; }

        .btn-back {
            padding:15px 25px;
            background:#003b88;
            color:white;
            border:none;
            border-radius:7px;
            cursor:pointer;
            font-size:20px;
            margin-top:20px;
        }
        .btn-back:hover { background:#0055cc; }

        /* NEW MODAL DESIGN (does NOT change original layout) */
        .modal-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            display: none;
            justify-content: center;
            align-items: center;
        }
        .modal-box {
            background: #07305a;
            padding: 25px;
            border-radius: 10px;
            width: 350px;
            text-align: center;
            box-shadow: 0 0 15px #000;
        }
        .modal-btn {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .confirm-del { background:#aa0000; color:white; }
        .cancel-del { background:#003b88; color:white; }
    </style>

    <script>
        function confirmDelete(id) {
            document.getElementById("modal-bg").style.display = "flex";
            document.getElementById("deleteLink").href = "accman.php?delete=" + id;
        }

        function closeModal() {
            document.getElementById("modal-bg").style.display = "none";
        }
    </script>
</head>
<body>

<div class="header">ACCOUNT MANAGEMENT</div>

<div class="content">

    <table border="1">
        <tr>
            <th>ID Number</th>
            <th>Username</th>
            <th>Status</th>
            <th>Delete</th>
        </tr>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY id ASC");
        while ($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['username']; ?></td>

            <td>
                <button class="status-btn <?php echo $row['status']; ?>"
                    onclick="window.location='accman.php?toggle=<?php echo $row['id']; ?>'">
                    <?php echo ucfirst($row['status']); ?>
                </button>
            </td>

            <td>
                <button class="delete-btn" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

    <button class="btn-back" onclick="window.location='admin_dashboard.php'">Back</button>

</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="modal-bg" class="modal-bg">
    <div class="modal-box">
        <h3>Are you sure you want to Delete?</h3>

        <a id="deleteLink" class="modal-btn confirm-del">Yes, Delete</a>
        <button class="modal-btn cancel-del" onclick="closeModal()">Cancel</button>
    </div>
</div>

</body>
</html>
