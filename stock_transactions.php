<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/* =========================
   FETCH STOCK TRANSACTIONS
========================= */

$transactions = mysqli_query(
    $conn,
    "SELECT
        stock_transactions.id,
        products.product_name,
        stock_transactions.transaction_type,
        stock_transactions.quantity,
        stock_transactions.reference_id
     FROM stock_transactions
     LEFT JOIN products
     ON stock_transactions.product_id = products.id
     ORDER BY stock_transactions.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Stock Transactions - Inventory Management</title>

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

        .purchase {
            color: #198754;
            font-weight: bold;
        }

        .sale {
            color: #dc3545;
            font-weight: bold;
        }

        .quantity-purchase {
            color: #198754;
            font-weight: bold;
        }

        .quantity-sale {
            color: #dc3545;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Stock Transactions</h1>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>


    <div class="box">

        <h2>Transaction History</h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Product</th>
                <th>Transaction Type</th>
                <th>Quantity</th>
                <th>Reference ID</th>

            </tr>


            <?php if ($transactions && mysqli_num_rows($transactions) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($transactions)) { ?>

                    <tr>

                        <td>
                            <?php echo $row["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["product_name"]); ?>
                        </td>

                        <td>

                            <?php if ($row["transaction_type"] == "purchase") { ?>

                                <span class="purchase">
                                    Purchase
                                </span>

                            <?php } else { ?>

                                <span class="sale">
                                    Sale
                                </span>

                            <?php } ?>

                        </td>

                        <td>

                            <?php if ($row["transaction_type"] == "purchase") { ?>

                                <span class="quantity-purchase">
                                    +<?php echo $row["quantity"]; ?>
                                </span>

                            <?php } else { ?>

                                <span class="quantity-sale">
                                    -<?php echo $row["quantity"]; ?>
                                </span>

                            <?php } ?>

                        </td>

                        <td>
                            <?php echo $row["reference_id"]; ?>
                        </td>


                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="5">
                        No transactions found.
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>