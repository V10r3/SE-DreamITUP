<?php include 'includes/config.php'; if (!isset($_SESSION['user_id'])) header("Location: index.php"); 
$result = $conn->query("SELECT * FROM games");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - Explore</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header><!-- Same as dashboard --></header>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <h1>Explore More Games</h1>
    <p>This your gateway to new worlds and fresh challenges. Discover games crafted by talented developers, test your skills, and find your next favorite adventure. Start exploring and play today!</p>
    <h2>Games Created by Other Developers</h2>
    <div class="game-grid">
        <?php while ($game = $result->fetch_assoc()): ?>
            <div class="game-card">
                <img src="uploads/<?php echo $game['image']; ?>" alt="<?php echo $game['title']; ?>">
                <h3><a href="game-detail.php?id=<?php echo $game['id']; ?>"><?php echo $game['title']; ?></a></h3>
                <p><?php echo substr($game['projectdescription'], 0, 100); ?>...</p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>