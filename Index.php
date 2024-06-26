<?php
session_start();

// Database connection
$servername = "sql203.infinityfree.com";
$username = "if0_36784770";
$password = "bawakontonkyi";
$dbname = "if0_36784770_kontonkyi_supply";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// User login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $username;
            header("Location: index.php");
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with this username!";
    }
}

// Product upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    $sql = "INSERT INTO products (name, description, price, category, image) VALUES ('$name', '$description', '$price', '$category', '$image')";
    if ($conn->query($sql) === TRUE) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            echo "Product uploaded successfully!";
        } else {
            echo "Failed to upload image!";
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Display products
$sql = "SELECT * FROM products";
$products = $conn->query($sql);

// Shopping cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    array_push($_SESSION['cart'], $product_id);
}

// Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $user_id = $_SESSION['user'];
    $total = $_POST['total'];
    $status = 'Pending';

    $sql = "INSERT INTO orders (user_id, total, status) VALUES ('$user_id', '$total', '$status')";
    if ($conn->query($sql) === TRUE) {
        $order_id = $conn->insert_id;
        foreach ($_SESSION['cart'] as $product_id) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) SELECT '$order_id', '$product_id', 1, price FROM products WHERE id='$product_id'";
            $conn->query($sql);
        }
        unset($_SESSION['cart']);
        echo "Order placed successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kontonkyi Supply</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header>
        <img src="images/logo.png" alt="Logo">
        <form method="GET">
            <input type="text" name="search" placeholder="Search...">
            <button type="submit">Search</button>
        </form>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section>
        <h1>Welcome to Kontonkyi Supply</h1>
        <p>Your one-stop shop for food items</p>
        <button>Shop Now</button>
    </section>

    <!-- Product Grid -->
    <section>
        <h2>Products</h2>
        <div class="product-grid">
            <?php while($product = $products->fetch_assoc()): ?>
                <div class="product">
                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><?php echo $product['description']; ?></p>
                    <p>$<?php echo $product['price']; ?></p>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Kontonkyi Supply. All rights reserved.</p>
        <a href="https://wa.me/233536361453"><i class="fa fa-whatsapp"></i> WhatsApp Us</a>
    </footer>
</body>
</html>