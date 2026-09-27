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
   ADD PRODUCT
========================= */

if (isset($_POST["add_product"])) {

    $product_name = trim($_POST["product_name"]);
    $category_id = (int)$_POST["category_id"];
    $quantity = (int)$_POST["quantity"];
    $price = (float)$_POST["price"];
    $description = trim($_POST["description"]);

    if ($product_name == "") {

        $error = "Product name is required.";

    } elseif ($category_id <= 0) {

        $error = "Please select a category.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO products
            (product_name, category_id, quantity, price, description)
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "siids",
            $product_name,
            $category_id,
            $quantity,
            $price,
            $description
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Product added successfully!";

        } else {

            $error = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   UPDATE PRODUCT
========================= */

if (isset($_POST["update_product"])) {

    $id = (int)$_POST["id"];
    $product_name = trim($_POST["product_name"]);
    $category_id = (int)$_POST["category_id"];
    $quantity = (int)$_POST["quantity"];
    $price = (float)$_POST["price"];
    $description = trim($_POST["description"]);

    if ($product_name == "") {

        $error = "Product name is required.";

    } elseif ($category_id <= 0) {

        $error = "Please select a category.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE products
             SET product_name = ?,
                 category_id = ?,
                 quantity = ?,
                 price = ?,
                 description = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "siidsi",
            $product_name,
            $category_id,
            $quantity,
            $price,
            $description,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Product updated successfully!";

        } else {

            $error = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   DELETE PRODUCT
========================= */

if (isset($_GET["delete"])) {

    $id = (int)$_GET["delete"];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM products WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    if (mysqli_stmt_execute($stmt)) {

        $message = "Product deleted successfully!";

    } else {

        $error = "Product cannot be deleted.";
    }

    mysqli_stmt_close($stmt);
}


/* =========================
   GET PRODUCT FOR EDIT
========================= */

$edit_product = null;

if (isset($_GET["edit"])) {

    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, product_name, category_id, quantity, price, description
         FROM products
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $edit_result = mysqli_stmt_get_result($stmt);

    $edit_product = mysqli_fetch_assoc($edit_result);

    mysqli_stmt_close($stmt);
}


/* =========================
   FETCH CATEGORIES
========================= */

$categories = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);


/* =========================
   FETCH PRODUCTS
========================= */

$products = mysqli_query(
    $conn,
    "SELECT products.id,
            products.product_name,
            products.category_id,
            categories.category_name,
            products.quantity,
            products.price,
            products.description
     FROM products
     LEFT JOIN categories
     ON products.category_id = categories.id
     ORDER BY products.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Products - Inventory Management</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .header {
            background: #1f2937;
            color: white;
            padding: 20px 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 25px;
        }

        .container {
            padding: 30px;
            max-width: none;
            margin: 0;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
            font-size: 15px;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            box-sizing: border-box;
        }

        .box h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #1f2937;
        }

        label {
            display: block;
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        textarea {
            height: 85px;
            resize: vertical;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .update-btn {
            background: #2563eb;
        }

        .update-btn:hover {
            background: #1d4ed8;
        }

        .cancel-btn {
            display: inline-block;
            background: #6b7280;
            color: white;
            padding: 11px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-left: 8px;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            color: #374151;
            font-weight: bold;
        }

        tr:hover {
            background: #f9fafb;
        }

        .edit-btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
            font-size: 13px;
        }

        .edit-btn:hover {
            background: #1d4ed8;
        }

        .delete-btn {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

    </style>

</head>

<body>


    <!-- Header -->

    <div class="header">

        <h1>Products</h1>

    </div>


    <div class="container">


        <!-- Back -->

        <a class="back" href="dashboard.php">
            ← Back to Dashboard
        </a>


        <!-- Messages -->

        <?php if ($message != "") { ?>

            <div class="success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <?php if ($error != "") { ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <!-- Add / Edit Product -->

        <div class="box">

            <?php if ($edit_product) { ?>

                <h2>Edit Product</h2>

                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $edit_product["id"]; ?>"
                    >


                    <label>Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        value="<?php echo htmlspecialchars($edit_product["product_name"]); ?>"
                        required
                    >


                    <label>Category</label>

                    <select name="category_id" required>

                        <option value="">
                            Select Category
                        </option>

                        <?php while ($category = mysqli_fetch_assoc($categories)) { ?>

                            <option
                                value="<?php echo $category["id"]; ?>"
                                <?php
                                if ($category["id"] == $edit_product["category_id"]) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($category["category_name"]); ?>
                            </option>

                        <?php } ?>

                    </select>


                    <label>Quantity</label>

                    <input
                        type="number"
                        name="quantity"
                        min="0"
                        value="<?php echo $edit_product["quantity"]; ?>"
                        required
                    >


                    <label>Price</label>

                    <input
                        type="number"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?php echo $edit_product["price"]; ?>"
                        required
                    >


                    <label>Description</label>

                    <textarea name="description"><?php echo htmlspecialchars($edit_product["description"]); ?></textarea>


                    <button
                        type="submit"
                        name="update_product"
                        class="update-btn"
                    >
                        Update Product
                    </button>


                    <a
                        href="products.php"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                </form>


            <?php } else { ?>

                <h2>Add Product</h2>

                <form method="POST">

                    <label>Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        placeholder="Enter product name"
                        required
                    >


                    <label>Category</label>

                    <select name="category_id" required>

                        <option value="">
                            Select Category
                        </option>

                        <?php while ($category = mysqli_fetch_assoc($categories)) { ?>

                            <option value="<?php echo $category["id"]; ?>">
                                <?php echo htmlspecialchars($category["category_name"]); ?>
                            </option>

                        <?php } ?>

                    </select>


                    <label>Quantity</label>

                    <input
                        type="number"
                        name="quantity"
                        min="0"
                        value="0"
                        required
                    >


                    <label>Price</label>

                    <input
                        type="number"
                        name="price"
                        min="0"
                        step="0.01"
                        placeholder="Enter price"
                        required
                    >


                    <label>Description</label>

                    <textarea
                        name="description"
                        placeholder="Enter product description"
                    ></textarea>


                    <button
                        type="submit"
                        name="add_product"
                    >
                        Add Product
                    </button>

                </form>

            <?php } ?>

        </div>


        <!-- Product List -->

        <div class="box">

            <h2>Product List</h2>

            <div class="table-container">

                <table>

                    <tr>

                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Action</th>

                    </tr>


                    <?php if ($products && mysqli_num_rows($products) > 0) { ?>

                        <?php while ($product = mysqli_fetch_assoc($products)) { ?>

                            <tr>

                                <td>
                                    <?php echo $product["id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product["product_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product["category_name"]); ?>
                                </td>

                                <td>
                                    <?php echo $product["quantity"]; ?>
                                </td>

                                <td>
                                    ₹<?php echo number_format($product["price"], 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product["description"]); ?>
                                </td>

                                <td>

                                    <a
                                        href="products.php?edit=<?php echo $product["id"]; ?>"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="products.php?delete=<?php echo $product["id"]; ?>"
                                        class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="7">
                                No products found.
                            </td>

                        </tr>

                    <?php } ?>

                </table>

            </div>

        </div>


    </div>

</body>

</html>