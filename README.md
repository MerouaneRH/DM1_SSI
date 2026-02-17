# DM1 - Formulaire d'identification

## Informations techniques
- PHP 8+ avec WAMP 3.3.0
- MySQL (BDD: pageweb_auth)
- HTML/CSS sans framework

## Installation WAMP (guide complet)
1. Telecharger WAMP 3.3.0 sur le site officiel.
2. Installer WAMP (ex: `C:\wamp64`).
3. Lancer WAMP et attendre l'icone verte.
4. Ouvrir `http://localhost/` pour verifier que le serveur fonctionne.
5. Ouvrir phpMyAdmin via `http://localhost/phpmyadmin`.

## Deposer le dossier du projet
1. Placer le dossier `DM1_SSI` dans `C:\wamp64\www`.
2. Le chemin doit etre: `C:\wamp64\www\DM1_SSI`.
3. Dans VS Code, ouvrir ce dossier.

## Creer la base de donnees
1. Aller sur `http://localhost/phpmyadmin`.
2. Creer une base nommee `pageweb_auth`.
3. Aucun import SQL n'est necessaire (la table est creee automatiquement).

## Configurer la connexion MySQL
1. Ouvrir `db.php`.
2. Verifier que les identifiants sont corrects (par defaut: `root` / mot de passe vide).
3. Si besoin, modifier `$user` et `$pass`.

## Configuration des variables d'environnement (OBLIGATOIRE)
Definir les identifiants via variables d'environnement :
- `DB_HOST` (ex: `localhost`)
- `DB_NAME` (ex: `pageweb_auth`)
- `DB_USER` (ex: `root`)
- `DB_PASS` (ex: mot de passe)
- `DB_CHARSET` (ex: `utf8mb4`)
- `ADMIN_USER` (ex: `admin`)
- `ADMIN_PASS` (ex: mot de passe admin fort)

### Configuration WAMP
1. Ouvrir `C:\wamp64\bin\apache\apache2.4.xx\conf\httpd.conf`.
2. Ajouter les lignes suivantes :
```
SetEnv DB_HOST localhost
SetEnv DB_NAME pageweb_auth
SetEnv DB_USER root
SetEnv DB_PASS
SetEnv DB_CHARSET utf8mb4
SetEnv ADMIN_USER adminuser
SetEnv ADMIN_PASS VotreMotDePasseSecurise123!
```
3. Redemarrer WAMP pour appliquer.

## Securite implementee (OWASP Top 10)

### A1 - Broken Access Control
- Controle d'acces pour creation de comptes (admin uniquement)
- Verification cote serveur sur chaque requete
- Affichage conditionnel des fonctionnalites selon le role

### A2 - Cryptographic Failures
- Mots de passe haches avec `password_hash()` (bcrypt)
- Verification avec `password_verify()`
- Variables d'environnement pour credentials DB et admin
- Erreurs PHP non exposees (display_errors = Off)

### A3 - Injection
- Requetes preparees PDO pour toutes les requetes SQL
- Protection CSRF avec tokens aleatoires (`random_bytes()`)
- Verification des tokens avec `hash_equals()`
- Validation des entrees (longueurs limitees)

### A5 - Security Misconfiguration
- Fichiers sensibles proteges via `.htaccess`
- Directory listing desactive
- Headers de securite (X-Frame-Options, CSP, etc.)
- `allow_url_fopen` et `allow_url_include` desactives

### A7 - Identification and Authentication Failures
- Cookies securises (HttpOnly, SameSite=Strict)
- Timeout de session (30 minutes d'inactivite)
- Rate limiting (5 tentatives max, blocage 15 min)
- Session regeneration apres login

### A8 - Software and Data Integrity Failures
- Content Security Policy (CSP) stricte
- Permissions Policy (APIs sensibles desactivees)
- HSTS configure (si HTTPS disponible)

## Lancer le projet
1. Ouvrir `http://localhost/DM1_SSI/index.php`.
2. Boutons disponibles : Reset, Valider, Deconnexion (si connecte).
3. AjoutCompte est reserve a l'admin.

## Identifiant / mot de passe
- Compte admin cree automatiquement au premier lancement si `ADMIN_PASS` est defini.
- Identifiant admin par defaut: `admin` (modifiable via `ADMIN_USER`).
- Le mot de passe admin doit etre defini via `ADMIN_PASS` dans les variables d'environnement.

## Fonctionnalites
- Connexion securisee avec hachage bcrypt
- Creation de comptes (admin uniquement)
- Deconnexion securisee avec destruction de session
- Protection CSRF sur tous les formulaires
- Rate limiting (max 5 tentatives de connexion)
- Timeout de session apres 30 minutes d'inactivite

## Remarques
- Le bouton AjoutCompte ajoute un nouvel identifiant dans la base.
- L'ajout de compte est autorise uniquement si l'utilisateur connecte est l'admin.
- Le bouton Deconnexion apparait uniquement si un utilisateur est connecte.
- Les mots de passe sont stockes avec `password_hash()`.
