<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/* =========================
   DASHBOARD COUNTS
========================= */

$product_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

$product_data = mysqli_fetch_assoc($product_result);
$total_products = $product_data["total"];


$category_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$category_data = mysqli_fetch_assoc($category_result);
$total_categories = $category_data["total"];


$supplier_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM suppliers"
);

$supplier_data = mysqli_fetch_assoc($supplier_result);
$total_suppliers = $supplier_data["total"];


$stock_result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(quantity), 0) AS total FROM products"
);

$stock_data = mysqli_fetch_assoc($stock_result);
$total_stock = $stock_data["total"];


/* =========================
   PURCHASE TOTAL
========================= */

$purchase_result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM purchases"
);

$purchase_data = mysqli_fetch_assoc($purchase_result);
$total_purchases = $purchase_data["total"];


/* =========================
   SALES TOTAL
========================= */

$sales_result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM sales"
);

$sales_data = mysqli_fetch_assoc($sales_result);
$total_sales = $sales_data["total"];

?>

<!DOCTYPE html>
<html>

<head>

    <title>Inventory Management System - Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #1f2937;
            color: white;
            padding: 25px 15px;
        }

        .logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo h2 {
            font-size: 21px;
        }

        .logo p {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 5px;
        }

        .menu a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 7px;
            border-radius: 7px;
            font-size: 15px;
        }

        .menu a:hover,
        .menu a.active {
            background: #374151;
            color: white;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 15px;
            right: 15px;
        }

        .logout a {
            display: block;
            text-align: center;
            background: #dc2626;
            color: white;
            padding: 11px;
            border-radius: 7px;
            text-decoration: none;
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .topbar h1 {
            color: #1f2937;
            font-size: 25px;
        }

        .user {
            color: #6b7280;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h2 {
            color: #1f2937;
            margin-bottom: 7px;
        }

        .welcome p {
            color: #6b7280;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .card h3 {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .card .number {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .section h2 {
            color: #1f2937;
            margin-bottom: 20px;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .module {
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 8px;
        }

        .module h3 {
            color: #374151;
            margin-bottom: 8px;
        }

        .module p {
            color: #6b7280;
            font-size: 14px;
        }

        .amount-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .amount-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .amount-card h3 {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .amount-card .amount {
            font-size: 25px;
            font-weight: bold;
            color: #1f2937;
        }

        @media (max-width: 900px) {

            .cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .modules {
                grid-template-columns: repeat(2, 1fr);
            }

        }

    </style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="logo">

        <h2>Inventory System</h2>

        <p>Management Dashboard</p>

    </div>


    <div class="menu">

        <a href="dashboard.php" class="active">
            Dashboard
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="categories.php">
            Categories
        </a>

        <a href="suppliers.php">
            Suppliers
        </a>

        <a href="purchases.php">
            Purchases
        </a>

        <a href="sales.php">
            Sales
        </a>

        <a href="stock.php">
            Stock Management
        </a>

        <a href="stock_transactions.php">
            Stock Transactions
        </a>

    </div>


    <div class="logout">

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main">


    <div class="topbar">

        <h1>Dashboard</h1>

        <div class="user">
            Welcome
        </div>

    </div>


    <div class="welcome">

        <h2>Welcome</h2>

        <p>
            Here's an overview of your inventory management system.
        </p>

    </div>


    <!-- =========================
         SUMMARY CARDS
    ========================= -->

    <div class="cards">


        <div class="card">

            <h3>Total Products</h3>

            <div class="number">
                <?php echo $total_products; ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Categories</h3>

            <div class="number">
                <?php echo $total_categories; ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Suppliers</h3>

            <div class="number">
                <?php echo $total_suppliers; ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Stock</h3>

            <div class="number">
                <?php echo $total_stock; ?>
            </div>

        </div>

    </div>


    <!-- =========================
         PURCHASE & SALES
    ========================= -->

    <div class="amount-section">


        <div class="amount-card">

            <h3>Total Purchases</h3>

            <div class="amount">
                ₹<?php echo number_format($total_purchases, 2); ?>
            </div>

        </div>


        <div class="amount-card">

            <h3>Total Sales</h3>

            <div class="amount">
                ₹<?php echo number_format($total_sales, 2); ?>
            </div>

        </div>


    </div>


    <br>


    <!-- =========================
         MODULES
    ========================= -->

    <div class="section">

        <h2>Inventory Modules</h2>


        <div class="modules">


            <div class="module">

                <h3>Products</h3>

                <p>
                    Add, update and manage inventory products.
                </p>

            </div>


            <div class="module">

                <h3>Categories</h3>

                <p>
                    Organize products into different categories.
                </p>

            </div>


            <div class="module">

                <h3>Suppliers</h3>

                <p>
                    Manage supplier information and records.
                </p>

            </div>


            <div class="module">

                <h3>Purchases</h3>

                <p>
                    Record and manage product purchases.
                </p>

            </div>


            <div class="module">

                <h3>Sales</h3>

                <p>
                    Record and manage product sales.
                </p>

            </div>


            <div class="module">

                <h3>Stock Transactions</h3>

                <p>
                    Track stock movements and transactions.
                </p>

            </div>


        </div>

    </div>


</div>

</body>

</html>