<?php
//Riccy
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db/db.php';
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_date = $_POST['transaction_date'];
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Insert the transaction into the database
    $stmt = $koneksi->prepare("INSERT INTO transactions (transaction_date, category_id, type_id, amount, description, user_id) VALUES (?, ?, 1, ?, ?, ?)");
    $stmt->bind_param("sidss", $transaction_date, $category_id, $amount, $description, $user_id);

    if ($stmt->execute()) {
        $message = "Income added successfully!";
        // Clear the form fields after submission
        $_POST = [];
    } else {
        $message = "Error: " . $koneksi->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Income</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
        }
        .form-container {
            margin-top: 50px;
            max-width: 600px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .btn-primary {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container mx-auto">
            <h2 class="text-center">Add Income</h2>
            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="add_income.php" method="POST" id="incomeForm">
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo $_POST['date'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" class="form-control" id="type" required>
                        <?php
                        $categories = mysqli_query($koneksi, "SELECT name FROM categories WHERE type_id = 1 AND user_id = {$_SESSION['user_id']}");
                        while ($row = mysqli_fetch_assoc($categories)) {
                            echo "<option value='{$row['name']}'>{$row['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" value="<?php echo $_POST['amount'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo $_POST['description'] ?? ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Add Income</button>
            </form>
            <a href="dashboard.php" class="btn btn-secondary mt-3 w-100">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

