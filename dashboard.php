<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
require_once 'db/db.php'; // Pastikan file koneksi database tersedia

// Query untuk laporan bulanan
$monthly_report_query = mysqli_query($koneksi, "
    SELECT 
        DATE_FORMAT(transaction_date, '%Y-%m') AS month, 
        SUM(CASE WHEN type_id = 1 THEN amount ELSE 0 END) AS total_income, 
        SUM(CASE WHEN type_id = 2 THEN amount ELSE 0 END) AS total_expense
    FROM transactions
    GROUP BY month
    ORDER BY month DESC
");

if (!$monthly_report_query) {
    die('Query Error: ' . mysqli_error($koneksi));
}

$monthly_data = [];
while ($row = mysqli_fetch_assoc($monthly_report_query)) {
    $monthly_data[] = $row;
}

// Calculate totals for the past month
$start_date = date('Y-m-d', strtotime('-1 month'));
$end_date = date('Y-m-d');

$total_income_query = mysqli_query($koneksi, "
    SELECT SUM(amount) as total_income 
    FROM transactions 
    WHERE type_id = 1 AND transaction_date BETWEEN '$start_date' AND '$end_date'
");
$total_expense_query = mysqli_query($koneksi, "
    SELECT SUM(amount) as total_expense 
    FROM transactions 
    WHERE type_id = 2 AND transaction_date BETWEEN '$start_date' AND '$end_date'
");

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Personal Finance Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Selamat Datang, <?php echo htmlspecialchars($username); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard -->
    <div class="container mt-5">
        <!-- Summary Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">Dashboard</h3>
                    </div>
                    <div class="card-body">
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

        <!-- Monthly Report Section -->
        <div class="mt-5">
            <h4 class="text-center">Monthly Financial Report</h4>
            <table class="table table-bordered table-striped mt-4">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Income/pemasukan</th>
                        <th>Total Expense/pengeluaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_data as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data['month']); ?></td>
                            <td>Rp <?php echo number_format($data['total_income'], 2, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($data['total_expense'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Chart Section -->
        <div class="mt-5">
            <h4 class="text-center">Income vs Expense by Month</h4>
            <canvas id="monthlyChart" width="200" height="100"></canvas>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 Personal Finance Tracker. All rights reserved.</p>
    </footer>

    <script>
        const monthlyData = <?php echo json_encode($monthly_data); ?>;

        const labels = monthlyData.map(data => data.month);
        const incomeData = monthlyData.map(data => parseFloat(data.total_income));
        const expenseData = monthlyData.map(data => parseFloat(data.total_expense));

        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(0, 107, 150, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Expense',
                        data: expenseData,
                        backgroundColor: 'rgba(190, 1, 42, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Income vs Expense Over Time'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
