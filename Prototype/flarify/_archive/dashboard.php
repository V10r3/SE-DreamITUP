<?php include 'includes/config.php'; if (!isset($_SESSION['user_id'])) header("Location: index.php"); 
$stmt = $conn->prepare("SELECT * FROM games WHERE developer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$games = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Dashboard</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header>
    <div class="logo">Flarify</div>
    <nav>About Us | Games | Logout</nav>
    <input type="text" class="search-bar" placeholder="Search...">
    <div class="notifications">🔔</div>
    <div class="profile"><?php echo $_SESSION['username']; ?></div>
</header>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <p>We're excited to showcase your projects, connect with studios, and level up your skills. Let's build, play, and succeed together! #GameItUp</p>
    <h2>Games Developed by greenKnight95</h2>  <!-- Adjust if needed -->
    <div class="game-grid">
        <?php while ($game = $games->fetch_assoc()): ?>
            <div class="game-card">
                <img src="uploads/<?php echo $game['image']; ?>" alt="<?php echo $game['title']; ?>">
                <h3><?php echo $game['title']; ?></h3>
                <p><?php echo substr($game['projectdescription'], 0, 100); ?>...</p>
                <p>$<?php echo $game['price']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>