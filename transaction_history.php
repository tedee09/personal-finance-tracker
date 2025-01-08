<?php
//task bonus => Heri
require_once 'db/db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize input
    $delete_query = "DELETE FROM transactions WHERE id = $delete_id";
    if (mysqli_query($koneksi, $delete_query)) {
        $_SESSION['message'] = "Transaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting transaction. Please try again.";
    }
    header("Location: transaction_history.php");
    exit();
}

// Handle sorting and filtering
$filter_query = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $transaction_type = $_POST['transaction_type'] ?? '';

    if ($from_date && $to_date) {
        $filter_query .= " AND transaction_date BETWEEN '$from_date' AND '$to_date'";
    }
    if ($transaction_type) {
        $filter_query .= " AND tt.name = '$transaction_type'"; 
    }
}

// Sorting logic (default: by date descending)
$order_by = "transaction_date DESC";
if (isset($_GET['sort_order']) && $_GET['sort_order'] === 'asc') {
    $order_by = "transaction_date ASC"; 
}

// Fetch transactions
$transactions = mysqli_query($koneksi, "
    SELECT 
        t.id,
        t.transaction_date, 
        t.amount, 
        t.description, 
        tt.name AS type_name, -- Mengambil nama tipe transaksi ('income' atau 'expense')
        (SELECT name FROM categories WHERE id = t.category_id) AS category_name
    FROM 
        transactions t
    JOIN 
        transaction_types tt ON t.type_id = tt.id -- JOIN untuk mendapatkan nama tipe transaksi
    WHERE 
        1 $filter_query
    ORDER BY 
        $order_by
");

if (!$transactions) {
    $_SESSION['error'] = "Error fetching transactions. Please try again.";
}

// Fetch income and expense data for the charts
$income_chart_query = mysqli_query($koneksi, "
    SELECT 
        DATE(transaction_date) as transaction_date, 
        SUM(amount) as total_income 
    FROM 
        transactions 
    WHERE 
        type_id = 1
    GROUP BY 
        DATE(transaction_date)
    ORDER BY 
        DATE(transaction_date) ASC
");

$income_chart_data = [];
while ($row = mysqli_fetch_assoc($income_chart_query)) {
    $income_chart_data[] = $row;
}

$expense_chart_query = mysqli_query($koneksi, "
    SELECT 
        DATE(transaction_date) as transaction_date, 
        SUM(amount) as total_expense 
    FROM 
        transactions 
    WHERE 
        type_id = 2
    GROUP BY 
        DATE(transaction_date)
    ORDER BY 
        DATE(transaction_date) ASC
");

$expense_chart_data = [];
while ($row = mysqli_fetch_assoc($expense_chart_query)) {
    $expense_chart_data[] = $row;
}

// Fetch data for the summary chart (Income vs Expense)
$chart_data_query = mysqli_query($koneksi, "
    SELECT 
        tt.name AS category, -- Mengambil nama dari tabel transaction_types
        SUM(t.amount) AS total 
    FROM 
        transactions t
    JOIN 
        transaction_types tt ON t.type_id = tt.id
    GROUP BY 
        tt.name
");


$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_data_query)) {
    $chart_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History with Charts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .income-row { background-color: #d4edda; } /* Light green */
        .expense-row { background-color: #f8d7da; } /* Light red */
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

        <!-- Display Error or Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php elseif (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h2>Transaction History</h2>

        <!-- Filter Form -->
        <form method="POST" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select id="transaction_type" name="transaction_type" class="form-control">
                        <option value="">All Transactions</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary form-control">Filter</button>
                </div>
            </div>
        </form>

        <!-- Sorting Links -->
        <div class="mb-3">
            <a href="transaction_history.php?sort_order=asc" class="btn btn-info">Sort by Date (Ascending)</a>
            <a href="transaction_history.php?sort_order=desc" class="btn btn-info">Sort by Date (Descending)</a>
        </div>

        <!-- Transaction Table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Transaction Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                    <tr class="<?php echo $row['type_name'] === 'income' ? 'income-row' : 'expense-row'; ?>">
                        <td><?php echo $row['transaction_date']; ?></td>
                        <td><?php echo $row['type_name']; ?></td>
                        <td><?php echo ucfirst($row['type_name']); ?></td>
                        <td>Rp <?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <a href="transaction_history.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Transaction Summary Chart -->
        <div class="mt-5">
            <h3 class="text-center">Transaction summary (Income & Expense)</h3>
            <canvas id="transactionChart" width="400" height="200"></canvas>
        </div>

        <!-- Income and Expense Charts -->
        <div class="mt-5">
            <h3 class="text-center">Income and Expense Charts</h3>

            <!-- Income Chart -->
            <h5>Income Chart</h5>
            <canvas id="incomeChart" width="400" height="200"></canvas>

            <!-- Expense Chart -->
            <h5 class="mt-5">Expense Chart</h5>
            <canvas id="expenseChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = <?php echo json_encode($chart_data); ?>;
        const labels = chartData.map(data => data.category.charAt(0).toUpperCase() + data.category.slice(1));
        const dataValues = chartData.map(data => parseFloat(data.total));

        const ctx = document.getElementById('transactionChart').getContext('2d');
        const transactionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Amount',
                    data: dataValues,
                    backgroundColor: ['hsl(180, 94.40%, 79.00%)', 'rgb(255, 15, 15)'],
                    borderColor: ['rgb(255, 75, 75)', 'rgb(231, 43, 84)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Transaction Summary'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Income Chart
        const incomeData = <?php echo json_encode($income_chart_data); ?>;
        const incomeLabels = incomeData.map(data => data.transaction_date);
        const incomeValues = incomeData.map(data => parseFloat(data.total_income));

        const ctxIncome = document.getElementById('incomeChart').getContext('2d');
        new Chart(ctxIncome, {
            type: 'line',
            data: {
                labels: incomeLabels,
                datasets: [{
                    label: 'Total Income (per Date)',
                    data: incomeValues,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Income by Date'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Income'
                        }
                    }
                }
            }
        });

        // Expense Chart
        const expenseData = <?php echo json_encode($expense_chart_data); ?>;
        const expenseLabels = expenseData.map(data => data.transaction_date);
        const expenseValues = expenseData.map(data => parseFloat(data.total_expense));

        const ctxExpense = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctxExpense, {
            type: 'line',
            data: {
                labels: expenseLabels,
                datasets: [{
                    label: 'Total Expense (per Date)',
                    data: expenseValues,
                    backgroundColor: 'rgb(255, 99, 133)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Expense by Date'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Expense'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
