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
   SAVE SALE
========================= */

if (isset($_POST["save_sale"])) {

    $sale_date = $_POST["sale_date"];
    $product_id = (int)$_POST["product_id"];
    $quantity = (int)$_POST["quantity"];
    $price = (float)$_POST["price"];

    $total_amount = $quantity * $price;


    /* =========================
       VALIDATION
    ========================= */

    if ($product_id <= 0) {

        $error = "Please select a product.";

    } elseif ($quantity <= 0) {

        $error = "Quantity must be greater than 0.";

    } elseif ($price < 0) {

        $error = "Price cannot be negative.";

    } else {


        /* =========================
           CHECK STOCK
        ========================= */

        $stock_query = mysqli_prepare(
            $conn,
            "SELECT quantity
             FROM products
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stock_query,
            "i",
            $product_id
        );

        mysqli_stmt_execute($stock_query);

        $stock_result = mysqli_stmt_get_result($stock_query);

        $product = mysqli_fetch_assoc($stock_result);

        mysqli_stmt_close($stock_query);


        if (!$product) {

            $error = "Product not found.";

        } elseif ($quantity > $product["quantity"]) {

            $error = "Insufficient stock. Available stock: " . $product["quantity"];

        } else {


            /* =========================
               START TRANSACTION
            ========================= */

            mysqli_begin_transaction($conn);

            try {


                /* =========================
                   INSERT SALE
                ========================= */

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO sales
                    (sale_date, total_amount)
                    VALUES (?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sd",
                    $sale_date,
                    $total_amount
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to save sale.");
                }

                $sale_id = mysqli_insert_id($conn);

                mysqli_stmt_close($stmt);


                /* =========================
                   INSERT SALE ITEM
                ========================= */

                $subtotal = $quantity * $price;

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO sale_items
                    (sale_id, product_id, quantity, price, subtotal)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiidd",
                    $sale_id,
                    $product_id,
                    $quantity,
                    $price,
                    $subtotal
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to save sale item.");
                }

                mysqli_stmt_close($stmt);


                /* =========================
                   REDUCE PRODUCT STOCK
                ========================= */

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE products
                     SET quantity = quantity - ?
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

                $transaction_type = "sale";

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
                    $sale_id
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create stock transaction.");
                }

                mysqli_stmt_close($stmt);


                /* =========================
                   COMMIT
                ========================= */

                mysqli_commit($conn);

                $message = "Sale saved successfully!";


            } catch (Exception $e) {

                mysqli_rollback($conn);

                $error = $e->getMessage();
            }
        }
    }
}


/* =========================
   FETCH PRODUCTS
========================= */

$products = mysqli_query(
    $conn,
    "SELECT id, product_name, quantity, price
     FROM products
     ORDER BY product_name ASC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Sales - Inventory Management</title>

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

        .stock-box {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
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

    <h1>Sales</h1>

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


    <!-- CREATE SALE -->

    <div class="box">

        <h2>Create Sale</h2>

        <form method="POST">


            <!-- SALE DATE -->

            <label>Sale Date</label>

            <input
                type="date"
                name="sale_date"
                value="<?php echo date('Y-m-d'); ?>"
                required
            >


            <!-- PRODUCT -->

            <label>Product</label>

            <select
                name="product_id"
                id="product"
                onchange="updateProduct()"
                required
            >

                <option value="">
                    Select Product
                </option>

                <?php while ($product = mysqli_fetch_assoc($products)) { ?>

                    <option
                        value="<?php echo $product["id"]; ?>"
                        data-price="<?php echo $product["price"]; ?>"
                        data-stock="<?php echo $product["quantity"]; ?>"
                    >

                        <?php echo htmlspecialchars($product["product_name"]); ?>

                    </option>

                <?php } ?>

            </select>


            <!-- AVAILABLE STOCK -->

            <div class="stock-box">

                Available Stock:
                <strong>
                    <span id="stock">0</span>
                </strong>

            </div>


            <!-- QUANTITY -->

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


            <!-- SELLING PRICE -->

            <label>Selling Price</label>

            <input
                type="number"
                name="price"
                id="price"
                min="0"
                step="0.01"
                oninput="calculateSubtotal()"
                required
            >


            <!-- SUBTOTAL -->

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

            <button type="submit" name="save_sale">
                Save Sale
            </button>

        </form>

    </div>

</div>


<script>

function updateProduct() {

    const product =
        document.getElementById("product");

    const selectedOption =
        product.options[product.selectedIndex];


    const price =
        selectedOption.getAttribute("data-price");


    const stock =
        selectedOption.getAttribute("data-stock");


    if (price) {

        document.getElementById("price").value =
            parseFloat(price).toFixed(2);

    } else {

        document.getElementById("price").value = "";

    }


    if (stock) {

        document.getElementById("stock").innerText =
            stock;

    } else {

        document.getElementById("stock").innerText =
            "0";

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