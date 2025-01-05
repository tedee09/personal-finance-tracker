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

$categories = mysqli_query($koneksi, "SELECT id, name, type_id FROM categories WHERE user_id = $user_id");

// Handle add, edit, and delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'] ?? '';
        $type_id = $_POST['category_type'] ?? 1; // Default to 1 (Income)

        if ($category_name) {
            mysqli_query($koneksi, "INSERT INTO categories (name, user_id, type_id) VALUES ('$category_name', $user_id, $type_id)");
            header("Location: manage_categories.php");
            exit();
        }
    } elseif (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];

        mysqli_query($koneksi, "DELETE FROM categories WHERE id=$category_id AND user_id=$user_id");
        header("Location: manage_categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-2">
    <a href="dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>
        <h2>Manage Categories</h2>
        <form method="POST" class="mb-5">
            <div class="row">
            </form>
                <div class="col-md-6">
                    <input type="text" name="category_name" class="form-control" placeholder="New Category Name" required>
                </div>
                <div class="col-md-4">
                    <select name="category_type" class="form-control" required>
                        <option value="1">Income</option>
                        <option value="2">Expense</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <form method="POST">
                            <td>
                                <input type="text" name="category_name" value="<?php echo $row['name']; ?>" class="form-control category-name" readonly>
                                <input type="hidden" name="category_id" value="<?php echo $row['id']; ?>">
                            </td>
                            <td>
                                <select name="category_type" class="form-control category-type" disabled>
                                    <option value="1" <?php echo $row['type_id'] == 1 ? 'selected' : ''; ?>>Income</option>
                                    <option value="2" <?php echo $row['type_id'] == 2 ? 'selected' : ''; ?>>Expense</option>
                                </select>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
