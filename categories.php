<?php

require_once "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "success";

/* =========================
   ADD CATEGORY
========================= */

if (isset($_POST["add_category"])) {

    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    if ($category_name == "") {

        $message = "Please enter category name.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO categories (category_name, description) VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $category_name,
            $description
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Category added successfully!";

        } else {

            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   UPDATE CATEGORY
========================= */

if (isset($_POST["update_category"])) {

    $id = intval($_POST["id"]);
    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    if ($category_name == "") {

        $message = "Please enter category name.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE categories
             SET category_name = ?, description = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $category_name,
            $description,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Category updated successfully!";

        } else {

            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================
   DELETE CATEGORY
========================= */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM categories WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    if (mysqli_stmt_execute($stmt)) {

        $message = "Category deleted successfully!";

    } else {

        $message = "Cannot delete this category.";
        $message_type = "error";
    }

    mysqli_stmt_close($stmt);
}


/* =========================
   EDIT CATEGORY DATA
========================= */

$edit_category = null;

if (isset($_GET["edit"])) {

    $id = intval($_GET["edit"]);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, category_name, description
         FROM categories
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);

    $result_edit = mysqli_stmt_get_result($stmt);

    $edit_category = mysqli_fetch_assoc($result_edit);

    mysqli_stmt_close($stmt);
}


/* =========================
   FETCH CATEGORIES
========================= */

$result = mysqli_query(
    $conn,
    "SELECT id, category_name, description, created_at
     FROM categories
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Categories</title>

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
        }

        .container {
            padding: 30px;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .update-btn {
            background: #16a34a;
        }

        .update-btn:hover {
            background: #15803d;
        }

        .cancel-btn {
            background: #6b7280;
            text-decoration: none;
            color: white;
            padding: 11px 20px;
            border-radius: 5px;
            margin-left: 8px;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .edit-btn {
            background: #f59e0b;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background: #d97706;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            padding: 7px 12px;
            border-radius: 5px;
            text-decoration: none;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #2563eb;
        }

    </style>

</head>

<body>


    <div class="header">

        <h1>Categories</h1>

    </div>


    <div class="container">


        <a class="back" href="dashboard.php">
            ← Back to Dashboard
        </a>


        <?php if ($message != ""): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- ADD / EDIT CATEGORY -->

        <div class="box">

            <?php if ($edit_category): ?>

                <h2>Edit Category</h2>

                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $edit_category["id"]; ?>"
                    >

                    <label>Category Name</label>

                    <input
                        type="text"
                        name="category_name"
                        value="<?php echo htmlspecialchars($edit_category["category_name"]); ?>"
                        required
                    >

                    <label>Description</label>

                    <textarea
                        name="description"
                    ><?php echo htmlspecialchars($edit_category["description"]); ?></textarea>

                    <button
                        type="submit"
                        name="update_category"
                        class="update-btn"
                    >
                        Update Category
                    </button>

                    <a
                        href="categories.php"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>

                </form>

            <?php else: ?>

                <h2>Add Category</h2>

                <form method="POST">

                    <label>Category Name</label>

                    <input
                        type="text"
                        name="category_name"
                        placeholder="Enter category name"
                        required
                    >

                    <label>Description</label>

                    <textarea
                        name="description"
                        placeholder="Enter description"
                    ></textarea>

                    <button
                        type="submit"
                        name="add_category"
                    >
                        Add Category
                    </button>

                </form>

            <?php endif; ?>

        </div>


        <!-- CATEGORY LIST -->

        <div class="box">

            <h2>Category List</h2>

            <table>

                <tr>

                    <th>ID</th>

                    <th>Category Name</th>

                    <th>Description</th>

                    <th>Created At</th>

                    <th>Action</th>

                </tr>


                <?php if ($result && mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?php echo $row["id"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["category_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row["description"]); ?>
                            </td>

                            <td>
                                <?php echo $row["created_at"]; ?>
                            </td>

                            <td>

                                <a
                                    href="categories.php?edit=<?php echo $row["id"]; ?>"
                                    class="edit-btn"
                                >
                                    Edit
                                </a>


                                <a
                                    href="categories.php?delete=<?php echo $row["id"]; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this category?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5">
                            No categories found.
                        </td>

                    </tr>

                <?php endif; ?>

            </table>

        </div>


    </div>

</body>

</html>