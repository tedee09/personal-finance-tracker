<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

require_once 'db/db.php'; // Ensure you have the database connection

// Calculate totals for the past month
$start_date = date('Y-m-d', strtotime('-1 month'));
$end_date = date('Y-m-d');

$total_income_query = mysqli_query($koneksi, "SELECT SUM(amount) as total_income FROM transactions WHERE user_id = $user_id AND type_id = 1 AND transaction_date BETWEEN '$start_date' AND '$end_date'");
$total_expense_query = mysqli_query($koneksi, "SELECT SUM(amount) as total_expense FROM transactions WHERE user_id = $user_id AND type_id = 2 AND transaction_date BETWEEN '$start_date' AND '$end_date'");

if (!$total_income_query || !$total_expense_query) {
    die('Query Error: ' . mysqli_error($koneksi));
}

$total_income = mysqli_fetch_assoc($total_income_query)['total_income'] ?? 0;
$total_expense = mysqli_fetch_assoc($total_expense_query)['total_expense'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Personal Finance Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($username); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">Dashboard</h3>
                    </div>
                    <div class="mt-5">
                            <h5 class="text-center">Financial Summary for the Last Month</h5>
                            <div class="d-flex justify-content-around mt-4">
                                <div>
                                    <h5>Total Income</h5>
                                    <p>Rp <?php echo number_format($total_income, 2, ',', '.'); ?></p>
                                </div>
                                <div>
                                    <h5>Total Expense</h5>
                                    <p>Rp <?php echo number_format($total_expense, 2, ',', '.'); ?></p>
                                </div>
                                </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-around mt-4">
                            <a href="add_income.php" class="btn btn-success btn-lg">Add Income</a>
                            <a href="add_expense.php" class="btn btn-danger btn-lg">Add Expense</a>
                            <a href="transaction_history.php" class="btn btn-secondary btn-lg">Transaction History</a>
                            <a href="manage_categories.php" class="btn btn-info btn-lg">Manage Categories</a>
                        </div>
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-primary text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2024 Personal Finance Tracker. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
