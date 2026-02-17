<?php

declare(strict_types=1);

// A7 - Configuration sécurisée des cookies de session
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_secure', '0'); // mettre à 1 si HTTPS
ini_set('session.gc_maxlifetime', '1800'); // 30 minutes

session_start();
ini_set("display_errors", "0");
error_reporting(0);

// A7 - Timeout de session (30 minutes d'inactivité)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = time();

// A3 - Protection CSRF: génération du token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// A7 - Protection contre brute force (rate limiting simple)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Réinitialiser les tentatives après 15 minutes
if (isset($_SESSION['last_attempt_time']) && (time() - $_SESSION['last_attempt_time'] > 900)) {
    $_SESSION['login_attempts'] = 0;
}

require __DIR__ . "/db.php";

$pdo = null;
$message = "";
$messageType = "";

try {
  $pdo = get_pdo();

  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(50) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
  );

  $adminUser = getenv("ADMIN_USER") ?: "admin";
  $adminPass = getenv("ADMIN_PASS");
  $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $checkAdmin->execute([$adminUser]);
  if (!$checkAdmin->fetch() && $adminPass !== false && $adminPass !== "") {
    $insertAdmin = $pdo->prepare(
      "INSERT INTO users (username, password_hash) VALUES (?, ?)"
    );
    $insertAdmin->execute([
      $adminUser,
      password_hash($adminPass, PASSWORD_DEFAULT),
    ]);
  }
} catch (Throwable $e) {
  $message = "Erreur. Recommence";
  $messageType = "error";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $pdo instanceof PDO) {
    // A3 - Vérification du token CSRF
    $csrf_token_post = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token_post)) {
        $message = "Erreur. Recommence";
        $messageType = "error";
    } else {
    $action = $_POST["action"] ?? "";
    $username = trim((string)($_POST["username"] ?? ""));
    $password = (string)($_POST["password"] ?? "");
    
    // A3 - Validation supplémentaire des inputs
    if (strlen($username) > 50 || strlen($password) > 255) {
        $message = "Erreur. Recommence";
        $messageType = "error";
        $action = ""; // Empêcher le traitement
    }

    if ($action === "logout") {
      $_SESSION = [];
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
          session_name(),
          "",
          time() - 42000,
          $params["path"],
          $params["domain"],
          $params["secure"],
          $params["httponly"]
        );
      }
      session_destroy();
      $message = "Vous etes deconnecte";
      $messageType = "success";
    }

    if ($action === "login") {
    // A7 - Vérifier le nombre de tentatives
    if ($_SESSION['login_attempts'] >= 5) {
        $message = "Trop de tentatives. Attendez 15 minutes";
        $messageType = "error";
    } else {
    try {
      $stmt = $pdo->prepare(
        "SELECT password_hash FROM users WHERE username = ?"
      );
      $stmt->execute([$username]);
      $row = $stmt->fetch();

      if ($row && password_verify($password, $row["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["user"] = $username;
        $_SESSION['login_attempts'] = 0; // Réinitialiser les tentatives
        $message = "Vous etes connecte";
        $messageType = "success";
      } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $message = "Erreur. Recommence";
        $messageType = "error";
      }
    } catch (Throwable $e) {
      $_SESSION['login_attempts']++;
      $_SESSION['last_attempt_time'] = time();
      $message = "Erreur. Recommence";
      $messageType = "error";
    }
    }
    }

    if ($action === "add") {
      if (!isset($_SESSION["user"]) || $_SESSION["user"] !== $adminUser) {
        $message = "Erreur. Recommence";
        $messageType = "error";
      } elseif ($username === "" || $password === "") {
        $message = "Entrez identifiant et mot de passe";
        $messageType = "error";
      } else {
        try {
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
        } catch (Throwable $e) {
          $message = "Erreur. Recommence Admin seul peut ajouter";
          $messageType = "error";
        }
      }
    }
    } // Fin de la vérification CSRF
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
        <?php if (isset($_SESSION["user"])) : ?>
          <p class="welcome", style="color: brown;">
            Bienvenue <?php echo htmlspecialchars($_SESSION["user"], ENT_QUOTES); ?>
          </p>
        <?php endif; ?>

        <form method="post" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>" />
          <label class="field">
            <span>Identifiant</span>
            <input name="username" type="text" required maxlength="50" />
          </label>

          <label class="field">
            <span>Mot de passe</span>
            <input name="password" type="password" required maxlength="255" />
          </label>

          <div class="buttons">
            <button type="reset">Reset</button>
            <button type="submit" name="action" value="login">Valider</button>
            <?php if (isset($_SESSION["user"])) : ?>
              <button type="submit" name="action" value="logout" formnovalidate>
                Deconnexion
              </button>
            <?php endif; ?>
            <?php if (isset($_SESSION["user"]) && $_SESSION["user"] === $adminUser) : ?>
              <button type="submit" name="action" value="add">AjoutCompte</button>
            <?php endif; ?>
          </div>
        </form>

        <p class="message <?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>" role="status" aria-live="polite">
          <?php echo htmlspecialchars($message, ENT_QUOTES); ?>
        </p>
      </section>
    </main>
  </body>
</html>
