<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$result = mysqli_query($conn, "SELECT * FROM menu");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Menu | RMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ecf0f1;
            margin: 0;
            padding: 30px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .menu-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
        }
        .card img {
            max-width: 100%;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
        }
        .card h4 {
            margin: 10px 0 5px 0;
        }
        .card p {
            margin: 0;
            font-weight: bold;
            color: #27ae60;
        }
        .card input[type="checkbox"] {
            margin-top: 10px;
        }
        .order-form {
            text-align: center;
            margin-top: 30px;
        }
        .order-form button {
            background: #2980b9;
            color: white;
            padding: 12px 30px;
            border: none;
            font-size: 16px;
            border-radius: 5px;
        }
        .order-form button:hover {
            background: #1f618d;
        }
    </style>
</head>
<body>
    <h2>Our Menu</h2>
    <form action="order_summary.php" method="post">
        <div class="menu-container">
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($row['image_url']); ?>" alt="<?= htmlspecialchars($row['item_name']); ?>">
                    <h4><?= htmlspecialchars($row['item_name']); ?></h4>
                    <p><?= htmlspecialchars($row['description']); ?></p>
                    <p>৳<?= number_format($row['price'], 2); ?></p>
                    <?php if ($_SESSION['role'] === 'guest') { ?>
                        <input type="checkbox" name="items[]" value="<?= $row['id']; ?>"> Select
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php if ($_SESSION['role'] === 'guest') { ?>
            <div class="order-form">
                <button type="submit">Confirm Order</button>
            </div>
        <?php } ?>
    </form>
</body>
</html>
