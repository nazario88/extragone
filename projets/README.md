# Projets eXtragone

Plateforme communautaire pour partager et découvrir des projets créatifs avec reviews détaillées.

## 🚀 Installation

### 1. Base de données

Exécuter le script SQL dans `db_structure.sql` pour créer toutes les tables nécessaires.

### 2. Configuration

Le projet utilise le fichier `../includes/config.php` existant. Assure-toi que les variables d'environnement suivantes sont configurées :

```php
$_ENV['MISTRAL_API_KEY'] = 'ta_cle_api'; // Optionnel pour Nomi
// Pour les emails (à configurer plus tard) :
$_ENV['SMTP_HOST'] = 'smtp.example.com';
$_ENV['SMTP_USER'] = 'noreply@extrag.one';
$_ENV['SMTP_PASS'] = 'password';
```

### 3. Permissions

```bash
chmod 755 projets/uploads/
chmod 755 projets/uploads/projects/
chmod 755 projets/uploads/avatars/
```

### 4. Dépendances

Le projet utilise :
- **Parsedown** pour le rendu Markdown (à placer dans `../includes/Parsedown.php`)
- **SimpleMDE** (chargé via CDN)
- **Tailwind CSS** (chargé via CDN)

## 📁 Structure

```
projets/
├── index.php                 # Homepage
├── connexion.php             # Login/Register
├── soumettre.php             # Formulaire soumission
├── projet.php                # Fiche projet
├── membre.php                # Profil utilisateur
├── reglages.php              # Paramètres
├── top-reviewers.php         # Classement
├── devenir-reviewer.php      # Candidature reviewer
├── deconnexion.php           # Logout
│
├── includes/
│   ├── auth.php              # Système d'authentification
│   ├── functions.php         # Fonctions utilitaires
│   ├── header.php            # Header commun
│   └── footer.php            # Footer commun
│
├── functions/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── projects/
│   │   └── submit-project.php
│   ├── comments/
│   │   ├── add-comment.php
│   │   ├── edit-comment.php
│   │   └── delete-comment.php
│   ├── reviews/
│   │   ├── claim-review.php
│   │   ├── publish-project.php
│   │   └── apply-reviewer.php
│   └── settings/
│       ├── update-profile.php
│       ├── update-notifications.php
│       └── change-password.php
│
├── reviewer/
│   ├── dashboard.php         # Liste projets à reviewer
│   └── review.php            # Page de review
│
├── uploads/
│   ├── projects/             # Screenshots projets
│   └── avatars/              # Avatars utilisateurs
│
└── images/
    ├── default-avatar.png
    └── og-default.png
```

## 🎯 Fonctionnalités

### Pour les membres
- ✅ Inscription / Connexion
- ✅ Soumettre un projet (titre, description, screenshots, lien)
- ✅ Voir ses projets (brouillons + publiés)
- ✅ Commenter les projets
- ✅ Éditer/supprimer ses commentaires
- ✅ Profil public personnalisable
- ✅ Paramètres de notifications

### Pour les reviewers
- ✅ Dashboard avec projets en attente
- ✅ Système de "claim" (premier arrivé, premier servi)
- ✅ Éditeur markdown pour rédiger les reviews
- ✅ Choix de l'image de couverture
- ✅ Ajout optionnel d'une vidéo YouTube
- ✅ Publication du projet avec la review

### Workflow
1. **Soumission** : L'utilisateur soumet son projet (statut : `draft`)
2. **Claim** : Un reviewer s'attribue le projet (statut : `in_review`)
3. **Review** : Le reviewer rédige sa review avec meta description
4. **Publication** : Le projet est publié (statut : `published`)

## 🔧 À implémenter plus tard

- [ ] Système d'envoi d'emails (notifications)
- [ ] Système de badges (contributeur actif, top reviewer, etc.)
- [ ] Notation par étoiles
- [ ] Recherche et filtres avancés
- [ ] Intégration Discord (webhook)
- [ ] Suppression de compte
- [ ] OAuth Google (pour connexion simplifiée)

## 🎨 Design

Le projet utilise :
- **Tailwind CSS** pour le styling
- **Mode dark/light** avec gestion via cookie
- **FontAwesome** pour les icônes
- **SimpleMDE** pour l'édition markdown

## 📊 Base de données

Tables principales :
- `extra_proj_users` : Utilisateurs
- `extra_proj_projects` : Projets
- `extra_proj_images` : Images des projets
- `extra_proj_comments` : Commentaires
- `extra_proj_reviewer_requests` : Demandes pour devenir reviewer
- `extra_proj_sessions` : Sessions utilisateurs
- `extra_proj_logs` : Logs d'activité

## 🔒 Sécurité

- ✅ Protection CSRF sur tous les formulaires
- ✅ Password hashing avec bcrypt
- ✅ Sanitization des inputs
- ✅ Validation côté serveur
- ✅ Protection des routes reviewer
- ✅ Sessions sécurisées

## 📝 Notes

- Les images uploadées sont limitées à 5 Mo
- Maximum 5 images par projet
- Les commentaires sont limités à 2000 caractères
- Le markdown est supporté pour les reviews et descriptions longues

---

**Créé avec ❤️ pour la communauté eXtragone**