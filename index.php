<?php

declare(strict_types=1);

require __DIR__ . "/db.php";

$pdo = get_pdo();
$message = "";
$messageType = "";

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
);

$checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$checkAdmin->execute(["admin"]);
if (!$checkAdmin->fetch()) {
    $insertAdmin = $pdo->prepare(
        "INSERT INTO users (username, password_hash) VALUES (?, ?)"
    );
    $insertAdmin->execute(["admin", password_hash("admin", PASSWORD_DEFAULT)]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $username = trim((string)($_POST["username"] ?? ""));
    $password = (string)($_POST["password"] ?? "");

    if ($action === "login") {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row["password_hash"])) {
            $message = "Vous etes connecte";
            $messageType = "success";
        } else {
            $message = "Erreur. Recommence";
            $messageType = "error";
        }
    }

    if ($action === "add") {
        if ($username === "" || $password === "") {
            $message = "Entrez identifiant et mot de passe";
            $messageType = "error";
        } else {
            $exists = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $exists->execute([$username]);

            if ($exists->fetch()) {
                $message = "Identifiant deja existant";
                $messageType = "error";
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO users (username, password_hash) VALUES (?, ?)"
                );
                $insert->execute([
                    $username,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);
                $message = "Ajout d'un compte";
                $messageType = "success";
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Formulaire d'identification</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <main class="page">
      <section class="card" aria-labelledby="title">
        <div class="logo" aria-hidden="true">
          <svg viewBox="0 0 64 64" role="img" aria-label="Logo authentification">
            <rect x="14" y="26" width="36" height="28" rx="6" />
            <path d="M22 26v-6a10 10 0 0 1 20 0v6" fill="none" stroke="currentColor" stroke-width="6" />
            <circle cx="32" cy="40" r="4" />
          </svg>
        </div>
        <h1 id="title">Identification</h1>

        <form method="post" autocomplete="off">
          <label class="field">
            <span>Identifiant</span>
            <input name="username" type="text" required />
          </label>

          <label class="field">
            <span>Mot de passe</span>
            <input name="password" type="password" required />
          </label>

          <div class="buttons">
            <button type="reset">Reset</button>
            <button type="submit" name="action" value="login">Valider</button>
            <button type="submit" name="action" value="add">AjoutCompte</button>
          </div>
        </form>

        <p class="message <?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>" role="status" aria-live="polite">
          <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
        </p>
      </section>
    </main>
  </body>
</html>
