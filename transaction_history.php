<?php
//Dwi Nur Arifin & Riccy
require_once 'db/db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Delete the transaction
    $delete_query = "DELETE FROM transactions WHERE id = $delete_id";
    if (mysqli_query($koneksi, $delete_query)) {
        $_SESSION['message'] = "Transaction deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting transaction. Please try again.";
    }
    header("Location: transaction_history.php"); // Redirect after delete
    exit();
}

// Handle sorting and filtering
$filter_query = "";

// Date range filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $transaction_type = $_POST['transaction_type'] ?? ''; // 'income' or 'expense'

    if ($from_date && $to_date) {
        $filter_query .= " AND transaction_date BETWEEN '$from_date' AND '$to_date'";
    }
    if ($transaction_type) {
        $filter_query .= " AND type_id = " . ($transaction_type === 'income' ? 1 : 2); // Use type_id for filtering
    }
}

// Sorting logic (default: by date descending)
$order_by = "transaction_date DESC";
if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'asc') {
    $order_by = "transaction_date ASC";
}

// Fetch transactions
$transactions = mysqli_query($koneksi, "
    SELECT 
        id,
        transaction_date, 
        amount, 
        description, 
        type_id,
        (SELECT name FROM categories WHERE id = category_id) as category_name
    FROM 
        transactions
    WHERE 
        1 $filter_query
    ORDER BY 
        $order_by
");

if (!$transactions) {
    $_SESSION['error'] = "Error fetching transactions. Please try again.";
}

// Fetch categories for the filter dropdown
$categories = mysqli_query($koneksi, "SELECT id, name FROM categories");

if (!$categories) {
    $_SESSION['error'] = "Error fetching categories. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .income-row {
            background-color: #d4edda; /* Light green */
        }
        .expense-row {
            background-color: #f8d7da; /* Light red */
        }
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
                    <tr class="<?php echo $row['type_id'] == 1 ? 'income-row' : 'expense-row'; ?>">
                        <td><?php echo $row['transaction_date']; ?></td>
                        <td><?php echo $row['category_name'] ?? 'Uncategorized'; ?></td>
                        <td><?php echo $row['type_id'] == 1 ? 'Income' : 'Expense'; ?></td>
                        <td><?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td>
                            <a href="transaction_history.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

