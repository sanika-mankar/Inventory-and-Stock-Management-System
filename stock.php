<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/* =========================
   FETCH STOCK
========================= */

$stock = mysqli_query(
    $conn,
    "SELECT
        products.id,
        products.product_name,
        categories.category_name,
        products.quantity,
        products.price
     FROM products
     LEFT JOIN categories
     ON products.category_id = categories.id
     ORDER BY products.product_name ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Stock Management - Inventory Management</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            color: #222;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .stock {
            font-weight: bold;
        }

        .available {
            color: #198754;
        }

        .low {
            color: #d97706;
        }

        .out {
            color: #dc3545;
        }

        .status {
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Stock Management</h1>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>


    <div class="box">

        <h2>Current Stock</h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Current Stock</th>
                <th>Price</th>
                <th>Status</th>

            </tr>


            <?php if ($stock && mysqli_num_rows($stock) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($stock)) { ?>

                    <tr>

                        <td>
                            <?php echo $row["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["product_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["category_name"]); ?>
                        </td>

                        <td class="stock">

                            <?php echo $row["quantity"]; ?>

                        </td>

                        <td>
                            ₹<?php echo number_format($row["price"], 2); ?>
                        </td>

                        <td class="status">

                            <?php

                            if ($row["quantity"] == 0) {

                                echo '<span class="out">Out of Stock</span>';

                            } elseif ($row["quantity"] <= 5) {

                                echo '<span class="low">Low Stock</span>';

                            } else {

                                echo '<span class="available">Available</span>';

                            }

                            ?>

                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="6">
                        No products found.
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>