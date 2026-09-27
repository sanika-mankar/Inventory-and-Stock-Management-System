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
   ADD SUPPLIER
========================= */

if (isset($_POST["add_supplier"])) {

    $supplier_name = trim($_POST["supplier_name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    if ($supplier_name == "") {

        $error = "Supplier name is required.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO suppliers
            (supplier_name, phone, email, address)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $supplier_name,
            $phone,
            $email,
            $address
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Supplier added successfully!";

        } else {

            $error = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   UPDATE SUPPLIER
========================= */

if (isset($_POST["update_supplier"])) {

    $id = (int)$_POST["id"];
    $supplier_name = trim($_POST["supplier_name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);

    if ($supplier_name == "") {

        $error = "Supplier name is required.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE suppliers
             SET supplier_name = ?,
                 phone = ?,
                 email = ?,
                 address = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $supplier_name,
            $phone,
            $email,
            $address,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Supplier updated successfully!";

        } else {

            $error = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   DELETE SUPPLIER
========================= */

if (isset($_GET["delete"])) {

    $id = (int)$_GET["delete"];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM suppliers WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    if (mysqli_stmt_execute($stmt)) {

        $message = "Supplier deleted successfully!";

    } else {

        $error = "Supplier cannot be deleted.";
    }

    mysqli_stmt_close($stmt);
}


/* =========================
   GET SUPPLIER FOR EDIT
========================= */

$edit_supplier = null;

if (isset($_GET["edit"])) {

    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, supplier_name, phone, email, address
         FROM suppliers
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $edit_result = mysqli_stmt_get_result($stmt);

    $edit_supplier = mysqli_fetch_assoc($edit_result);

    mysqli_stmt_close($stmt);
}


/* =========================
   FETCH SUPPLIERS
========================= */

$suppliers = mysqli_query(
    $conn,
    "SELECT id, supplier_name, phone, email, address
     FROM suppliers
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Suppliers - Inventory Management</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
        }

        .container {
            padding: 30px;
            max-width: none;
            margin: 0;
        }

        h1 {
            color: #222;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            width: 100%;
        }

        .box h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #222;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        /* BLUE ADD BUTTON */

        .add-btn {
            background: #2563eb !important;
            color: white !important;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #1d4ed8 !important;
        }

        /* GREEN UPDATE BUTTON */

        .update-btn {
            background: #16a34a;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #15803d;
        }

        /* GREY CANCEL BUTTON */

        .cancel-btn {
            background: #6b7280;
            color: white;
            padding: 11px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-left: 8px;
            display: inline-block;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        /* SUCCESS MESSAGE */

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        /* ERROR MESSAGE */

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        /* TABLE */

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

        /* EDIT BUTTON */

        .edit-btn {
            background: #f59e0b;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-block;
        }

        .edit-btn:hover {
            background: #d97706;
        }

        /* DELETE BUTTON */

        .delete-btn {
            background: #dc2626;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        /* BACK BUTTON */

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Suppliers</h1>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>


    <!-- =========================
         MESSAGES
    ========================= -->

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


    <!-- =========================
         ADD / EDIT SUPPLIER
    ========================= -->

    <div class="box">

        <?php if ($edit_supplier) { ?>

            <h2>Edit Supplier</h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $edit_supplier["id"]; ?>"
                >


                <label>Supplier Name</label>

                <input
                    type="text"
                    name="supplier_name"
                    value="<?php echo htmlspecialchars($edit_supplier["supplier_name"]); ?>"
                    required
                >


                <label>Phone</label>

                <input
                    type="text"
                    name="phone"
                    value="<?php echo htmlspecialchars($edit_supplier["phone"]); ?>"
                >


                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($edit_supplier["email"]); ?>"
                >


                <label>Address</label>

                <textarea
                    name="address"
                ><?php echo htmlspecialchars($edit_supplier["address"]); ?></textarea>

<button
    type="submit"
    name="add_supplier"
    style="background-color: #2563eb !important; color: white !important; border: none !important; padding: 11px 20px !important; border-radius: 6px !important; cursor: pointer !important;"
>
    Add Supplier
</button>

                <a
                    href="suppliers.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>

            </form>


        <?php } else { ?>

            <h2>Add Supplier</h2>

            <form method="POST">

                <label>Supplier Name</label>

                <input
                    type="text"
                    name="supplier_name"
                    placeholder="Enter supplier name"
                    required
                >


                <label>Phone</label>

                <input
                    type="text"
                    name="phone"
                    placeholder="Enter phone number"
                >


                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter email address"
                >


                <label>Address</label>

                <textarea
                    name="address"
                    placeholder="Enter supplier address"
                ></textarea>


                <button
                    type="submit"
                    name="add_supplier"
                    class="add-btn"
                >
                    Add Supplier
                </button>

            </form>

        <?php } ?>

    </div>


    <!-- =========================
         SUPPLIER LIST
    ========================= -->

    <div class="box">

        <h2>Supplier List</h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Supplier Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Action</th>

            </tr>


            <?php if ($suppliers && mysqli_num_rows($suppliers) > 0) { ?>

                <?php while ($supplier = mysqli_fetch_assoc($suppliers)) { ?>

                    <tr>

                        <td>
                            <?php echo $supplier["id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($supplier["supplier_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($supplier["phone"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($supplier["email"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($supplier["address"]); ?>
                        </td>

                        <td>

                            <a
                                href="suppliers.php?edit=<?php echo $supplier["id"]; ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>

                            <a
                                href="suppliers.php?delete=<?php echo $supplier["id"]; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this supplier?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="6">
                        No suppliers found.
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>

</html>
        