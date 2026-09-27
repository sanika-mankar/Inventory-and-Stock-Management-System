<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";


/* =========================
   SAVE PURCHASE
========================= */

if (isset($_POST["save_purchase"])) {

    $supplier_id = (int)$_POST["supplier_id"];
    $purchase_date = $_POST["purchase_date"];
    $product_id = (int)$_POST["product_id"];
    $quantity = (int)$_POST["quantity"];
    $price = (float)$_POST["price"];

    $total_amount = $quantity * $price;


    /* Validation */

    if ($supplier_id <= 0) {

        $error = "Please select a supplier.";

    } elseif ($product_id <= 0) {

        $error = "Please select a product.";

    } elseif ($quantity <= 0) {

        $error = "Quantity must be greater than 0.";

    } elseif ($price < 0) {

        $error = "Price cannot be negative.";

    } else {


        /* =========================
           START TRANSACTION
        ========================= */

        mysqli_begin_transaction($conn);

        try {


            /* =========================
               INSERT PURCHASE
            ========================= */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO purchases
                (supplier_id, purchase_date, total_amount)
                VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "isd",
                $supplier_id,
                $purchase_date,
                $total_amount
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to save purchase.");
            }

            $purchase_id = mysqli_insert_id($conn);

            mysqli_stmt_close($stmt);


            /* =========================
               INSERT PURCHASE ITEM
            ========================= */

            $subtotal = $quantity * $price;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO purchase_items
                (purchase_id, product_id, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiidd",
                $purchase_id,
                $product_id,
                $quantity,
                $price,
                $subtotal
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to save purchase item.");
            }

            mysqli_stmt_close($stmt);


            /* =========================
               UPDATE PRODUCT STOCK
            ========================= */

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET quantity = quantity + ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $quantity,
                $product_id
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update product stock.");
            }

            mysqli_stmt_close($stmt);


            /* =========================
               STOCK TRANSACTION
            ========================= */

            $transaction_type = "purchase";

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO stock_transactions
                (product_id, transaction_type, quantity, reference_id)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "isii",
                $product_id,
                $transaction_type,
                $quantity,
                $purchase_id
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create stock transaction.");
            }

            mysqli_stmt_close($stmt);


            /* =========================
               COMMIT
            ========================= */

            mysqli_commit($conn);

            $message = "Purchase saved successfully!";


        } catch (Exception $e) {

            mysqli_rollback($conn);

            $error = $e->getMessage();
        }
    }
}


/* =========================
   FETCH SUPPLIERS
========================= */

$suppliers = mysqli_query(
    $conn,
    "SELECT id, supplier_name
     FROM suppliers
     ORDER BY supplier_name ASC"
);


/* =========================
   FETCH PRODUCTS
========================= */

$products = mysqli_query(
    $conn,
    "SELECT id, product_name, price
     FROM products
     ORDER BY product_name ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Purchases - Inventory Management</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #222;
        }

        .box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        select,
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            background: #222;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #444;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .total-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .total-box h3 {
            margin: 0;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Purchases</h1>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>


    <!-- SUCCESS MESSAGE -->

    <?php if ($message != "") { ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <!-- ERROR MESSAGE -->

    <?php if ($error != "") { ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- CREATE PURCHASE -->

    <div class="box">

        <h2>Create Purchase</h2>

        <form method="POST">


            <!-- Supplier -->

            <label>Supplier</label>

            <select name="supplier_id" required>

                <option value="">
                    Select Supplier
                </option>

                <?php while ($supplier = mysqli_fetch_assoc($suppliers)) { ?>

                    <option value="<?php echo $supplier["id"]; ?>">

                        <?php echo htmlspecialchars($supplier["supplier_name"]); ?>

                    </option>

                <?php } ?>

            </select>


            <!-- Purchase Date -->

            <label>Purchase Date</label>

            <input
                type="date"
                name="purchase_date"
                value="<?php echo date('Y-m-d'); ?>"
                required
            >


            <!-- Product -->

            <label>Product</label>

            <select
                name="product_id"
                id="product"
                onchange="updatePrice()"
                required
            >

                <option value="">
                    Select Product
                </option>

                <?php while ($product = mysqli_fetch_assoc($products)) { ?>

                    <option
                        value="<?php echo $product["id"]; ?>"
                        data-price="<?php echo $product["price"]; ?>"
                    >

                        <?php echo htmlspecialchars($product["product_name"]); ?>

                    </option>

                <?php } ?>

            </select>


            <!-- Quantity -->

            <label>Quantity</label>

            <input
                type="number"
                name="quantity"
                id="quantity"
                min="1"
                value="1"
                oninput="calculateSubtotal()"
                required
            >


            <!-- Purchase Price -->

            <label>Purchase Price</label>

            <input
                type="number"
                name="price"
                id="price"
                min="0"
                step="0.01"
                oninput="calculateSubtotal()"
                required
            >


            <!-- Subtotal -->

            <label>Subtotal</label>

            <input
                type="number"
                id="subtotal"
                value="0.00"
                readonly
            >


            <!-- TOTAL -->

            <div class="total-box">

                <h3>
                    Total Amount:
                    ₹<span id="total">0.00</span>
                </h3>

            </div>


            <br>

            <button type="submit" name="save_purchase">
                Save Purchase
            </button>

        </form>

    </div>

</div>


<script>

function updatePrice() {

    const product =
        document.getElementById("product");

    const selectedOption =
        product.options[product.selectedIndex];

    const price =
        selectedOption.getAttribute("data-price");

    if (price) {

        document.getElementById("price").value =
            parseFloat(price).toFixed(2);

    } else {

        document.getElementById("price").value = "";

    }

    calculateSubtotal();
}


function calculateSubtotal() {

    const quantity =
        parseFloat(
            document.getElementById("quantity").value
        ) || 0;

    const price =
        parseFloat(
            document.getElementById("price").value
        ) || 0;

    const subtotal =
        quantity * price;

    document.getElementById("subtotal").value =
        subtotal.toFixed(2);

    document.getElementById("total").innerText =
        subtotal.toFixed(2);
}

</script>

</body>

</html>