<?php
//arifin
require_once 'db/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$filter_query = "WHERE t.user_id = $user_id"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $type_id = $_POST['type_id'] ?? '';
    $category_id = $_POST['category_id'] ?? '';

    if ($from_date && $to_date) {
        $filter_query .= " AND t.transaction_date BETWEEN '$from_date' AND '$to_date'";
    }
    if ($type_id) {
        $filter_query .= " AND t.type_id = $type_id";
    }
    if ($category_id) {
        $filter_query .= " AND t.category_id = $category_id";
    }
}

// Ini kode setelah diperbaiki, disusun yang rapih mas biar ga pegel pas baca, berantakan kali njir
$transactions = mysqli_query($koneksi, "
    SELECT 
        t.transaction_date, 
        t.amount, 
        t.description, 
        c.name as category_name, 
        t.type_id 
    FROM 
        transactions t 
    JOIN 
        categories c 
    ON 
        t.category_id = c.id 
    $filter_query 
    ORDER BY 
        t.transaction_date DESC
");
$categories = mysqli_query($koneksi, "SELECT id, name FROM categories WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-2">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
    <div class="container mt-5">
        <h2>Transaction History</h2>
        <form method="POST" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <label for="type_id" class="form-label">Type</label>
                    <select id="type_id" name="type_id" class="form-control">
                        <option value="">All Types</option>
                        <option value="1">Income</option>
                        <option value="2">Expense</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category_id" class="form-label">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="visibility: hidden;">Filter</label>
                    <button type="submit" class="btn btn-primary form-control">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                    <tr>
                        <td><?php echo $row['transaction_date']; ?></td>
                        <td><?php echo $row['type_id'] == 1 ? 'Income' : 'Expense'; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
