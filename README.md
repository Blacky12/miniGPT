# MiniGPT - Application de Chat IA

Une application web moderne développée avec Laravel et Vue.js qui permet aux utilisateurs de discuter avec des modèles d'IA comme ChatGPT.

## ✨ Fonctionnalités

- 💬 Chat interactif avec l'IA (OpenAI GPT)
- 👤 Système d'authentification complet avec Laravel Jetstream
- 🔐 Authentification à deux facteurs
- 📝 Gestion des conversations et historique
- ⚙️ Instructions utilisateur personnalisables
- 🎨 Interface utilisateur moderne avec Vue.js et Tailwind CSS
- 📱 Design responsive

## 🛠️ Technologies utilisées

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 avec Inertia.js
- **Base de données**: MySQL/PostgreSQL
- **Authentification**: Laravel Jetstream + Fortify
- **Styling**: Tailwind CSS
- **API IA**: OpenAI GPT API
- **Build Tool**: Vite

## 📋 Prérequis

- PHP >= 8.2
- Composer
- Node.js >= 16
- MySQL ou PostgreSQL
- Clé API OpenAI

## 🚀 Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-username/miniGPT.git
   cd miniGPT
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Installer les dépendances Node.js**
   ```bash
   npm install
   ```

4. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurer la base de données**
   - Créer une base de données
   - Mettre à jour les paramètres dans `.env`

6. **Configurer OpenAI**
   ```env
   OPENAI_API_KEY=votre_clé_api_openai
   ```

7. **Exécuter les migrations**
   ```bash
   php artisan migrate --seed
   ```

8. **Compiler les assets**
   ```bash
   npm run build
   ```

## 🎯 Utilisation

1. **Démarrer le serveur de développement**
   ```bash
   php artisan serve
   npm run dev
   ```

2. **Accéder à l'application**
   - Ouvrir `http://localhost:8000`
   - Créer un compte ou se connecter
   - Commencer à discuter avec l'IA !

## 📁 Structure du projet

```
app/
├── Http/Controllers/    # Contrôleurs
├── Models/             # Modèles Eloquent
├── Services/           # Services métier
└── Policies/          # Politiques d'autorisation

resources/
├── js/
│   ├── Components/    # Composants Vue.js
│   ├── Layouts/      # Layouts de l'application
│   └── Pages/        # Pages Vue.js
└── css/              # Styles CSS

database/
├── migrations/       # Migrations de base de données
└── seeders/         # Seeders
```

## 🔧 Commandes utiles

```bash
# Tests
php artisan test

# Nettoyage du cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Génération de clé d'application
php artisan key:generate
```

## 📝 Fonctionnalités principales

### Gestion des conversations
- Création de nouvelles conversations
- Historique des messages
- Suppression des conversations

### Authentification
- Inscription/Connexion
- Authentification à deux facteurs
- Gestion du profil utilisateur

### Chat IA
- Intégration avec l'API OpenAI
- Support de différents modèles GPT
- Instructions utilisateur personnalisées

## 🤝 Contribution

Ce projet a été développé dans le cadre d'un cours universitaire.

## 📄 Licence

Ce projet est sous licence MIT.

## 👨‍💻 Auteur

Développé par [Votre Nom] pour [Nom du Cours/Université]

---

> **Note**: Ce projet nécessite une clé API OpenAI valide pour fonctionner correctement.
