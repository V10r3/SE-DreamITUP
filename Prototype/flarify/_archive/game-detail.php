<?php include 'includes/config.php'; if (!isset($_SESSION['user_id'])) header("Location: index.php"); 
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Flarify - <?php echo $game['title']; ?></title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header><!-- Same --></header>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <h1><?php echo $game['title']; ?></h1>
    <p>By <?php // Fetch developer name ?> | A downloadable game for <?php echo $game['platforms']; ?></p>
    <img src="uploads/<?php echo $game['image']; ?>" alt="Banner">
    <p>Rating: <?php echo $game['rating']; ?> | Downloads: <?php echo $game['downloads']; ?></p>
    <p><?php echo $game['projectdescription']; ?></p>
    <h3>Controls:</h3>
    <ul>
        <li>CRANK - Operate the airplane in the air</li>
        <li>D-Pad/Left - Shoot a bomb</li>
    </ul>
    <?php if ($_SESSION['userrole'] === 'Investor'): ?>
        <form action="invest-process.php" method="POST">
            <input type="hidden" name="game_id" value="<?php echo $id; ?>">
            <input type="number" name="amount" placeholder="Investment Amount" required>
            <button type="submit">Invest</button>
        </form>
    <?php endif; ?>
    <button>Play Demo</button>
</div>
<script src="js/script.js"></script>
</body>
</html>