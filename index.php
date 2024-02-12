<?php
session_start(); // Starta sessionen

// Om användaren inte är inloggad, omdirigera till inloggningssidan
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(); // Avsluta skriptet för att förhindra att resten av sidan visas
}

// Hantera utloggning
if (isset($_GET['logout'])) {
    // Förstör sessionen för att logga ut användaren
    session_destroy();
    // Omdirigera tillbaka till inloggningssidan efter utloggning
    header("Location: login.php");
    exit(); // Avsluta skriptet för att förhindra att resten av sidan visas
}
?>

<!-- HTML-kod för att visa "Du är inloggad" och användarnamnet -->
<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css"> <!-- Länk till extern CSS-fil -->
    <title>Du är inloggad</title>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['user'])) : ?>
            <h2>Du är inloggad som <?= htmlspecialchars($_SESSION['user']) ?></h2>
            <a href="?logout=1" class="logout-button">Logga ut</a>
        <?php endif; ?>
    </div>
</body>

</html>