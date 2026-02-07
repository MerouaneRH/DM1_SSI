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

## Lancer le projet
1. Ouvrir `http://localhost/DM1_SSI/index.php`.
2. Boutons disponibles : Reset, Valider, AjoutCompte.

## Identifiant / mot de passe
- admin / admin (cree automatiquement au premier lancement)

## Remarques
- Le bouton AjoutCompte ajoute un nouvel identifiant dans la base.
- Les mots de passe sont stockes avec `password_hash()`.
