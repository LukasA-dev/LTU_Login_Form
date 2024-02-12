<?php
session_start(); // Starta sessionen

// Kontrollera om variabeln för antalet inloggningsförsök inte är satt i den aktuella sessionen
if (!isset($_SESSION['login_attempts'])) {
    // Om variabeln inte är satt, sätt den till 0
    $_SESSION['login_attempts'] = 0;

    // Sätt också tiden för det senaste inloggningsförsöket till den aktuella tiden
    $_SESSION['last_attempt_time'] = time();
}

$filePath = 'users.txt'; // Lagra användarnamn och hashade lösenord i denna fil

// Funktion för att kontrollera om användarnamnet finns
function userExists($username, $filePath)
{
    if (!is_readable($filePath)) {
        die("Fel: Kan inte läsa från filen ($filePath). Kontakta IT-Service för hjälp.");
    }
    $users = file_get_contents($filePath); // Läs in användarfilen
    $users = explode("\n", $users); // Dela upp filen i rader
    foreach ($users as $user) {
        if (strtolower(explode(";", $user)[0]) == strtolower($username)) { // Använd små bokstäver för jämförelse
            return true; // Användarnamnet finns
        }
    }
    return false; // Användarnamnet finns inte
}

// Hantera registrering
if (isset($_POST['register'])) {
    $username = strtolower(filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)); // Konvertera till små bokstäver
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($username) || empty($password)) {
        echo "Felaktigt användarnamn eller lösenord.";
    } elseif (!userExists($username, $filePath)) {
        $hash = password_hash($password, PASSWORD_DEFAULT); // Hasha lösenordet
        if (!is_writable($filePath)) {
            die("Fel: Kan inte skriva till filen ($filePath).");
        }
        file_put_contents($filePath, "$username;$hash\n", FILE_APPEND); // Spara användarnamn och hashat lösenord
        echo "Användare registrerad.";
    } else {
        echo "Användarnamnet finns redan.";
    }
}

// Hantera inloggning
if (isset($_POST['login'])) {
    $max_attempts = 5; // Max antal tillåtna försök
    $lockout_time = 60; // Tidsperiod för låsning i sekunder - 1 minut för testning  

    if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lockout_time) {
        die("För många misslyckade inloggningsförsök. Försök igen senare.");
    } elseif ((time() - $_SESSION['last_attempt_time']) >= $lockout_time) {
        // Återställ räknaren om tidsperioden har passerat
        $_SESSION['login_attempts'] = 0;
    }

    // Konvertera till små bokstäver på användarnamnet för felhantering
    $username = strtolower(filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($username) || empty($password)) {
        echo "Felaktigt användarnamn eller lösenord.";
        exit();
    }

    $isValid = false;

    if (!is_readable($filePath)) {
        die("Fel: Kan inte läsa från filen ($filePath). Kontakta IT-Service för hjälp.");
    }
    $users = file_get_contents($filePath);
    $users = explode("\n", $users);
    foreach ($users as $user) {
        $userData = explode(";", trim($user));
        if (count($userData) == 2) { // Kontrollera att vi har exakt två element
            list($storedUser, $storedHash) = $userData;
            if (strtolower($username) == strtolower($storedUser) && password_verify($password, $storedHash)) {
                $isValid = true;
                break;
            }
        }
    }

    if ($isValid) {
        $_SESSION['user'] = $username; // Sätt användarnamnet i sessionen
        session_regenerate_id(true); // Regenerera session ID för att förhindra session hijacking
        header("Location: index.php"); // Omdirigera till index.php
        exit();
    } else {
        $_SESSION['login_attempts'] += 1;
        $_SESSION['last_attempt_time'] = time();
        echo "Felaktigt användarnamn eller lösenord.";
    }
}

?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css"> <!-- Länk till extern CSS-fil -->
    <title>Inloggningssida</title>
</head>

<body>
    <div class="container">
        <h2>Logga in</h2>
        <!-- Formulär för inloggning -->
        <form action="login.php" method="post">
            <label for="username">Användarnamn:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Lösenord:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="login">Logga in</button>
        </form>

        <hr> <!-- Horisontell linje som separator -->

        <!-- Formulär för att registrera ny användare -->
        <h2>Registrera ny användare</h2>
        <form action="login.php" method="post">
            <label for="new_username">Användarnamn:</label>
            <input type="text" id="new_username" name="username" required>
            <label for="new_password">Lösenord:</label>
            <input type="password" id="new_password" name="password" required>
            <button type="submit" name="register">Spara ny användare</button>
        </form>
    </div>
</body>

</html>