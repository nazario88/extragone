<?php
if(!isset($base)) $base = "https://www.extrag.one";
if(!isset($url_canon)) $url_canon = "https://www.extrag.one";
if(!isset($recherche)) $recherche = "";
if(!isset($image_seo)) $image_seo = "$base/assets/img/image-og.png";

if(substr($image_seo, 0,5) !== 'https') {
  $new_url = 'https://extrag.one';
  if(substr($image_seo,-1) !== '/') $new_url .= '/';
  $image_seo = $new_url.$image_seo;
}

// Inclure le système d'authentification
require_once __DIR__ . '/auth.php';
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
  <head>
    <title><?=$title?></title>
    <!-- Meta -->
    <meta charset="UTF-8" />
    
    <meta name="application-name" content="eXtragone"/>
    <meta name="author"           content="InnoSpira"/>
    <meta name="description"      content="<?=$description?>"/>
    <meta name="viewport"         content="width=device-width, initial-scale=1">

    <meta property="og:site_name"   content="eXtragone">
    <meta property="og:title"       content="<?=$title?>">
    <meta property="og:description" content="<?=$description?>">
    <meta property="og:image"       content="<?=$image_seo?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?=$url_canon?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?=$title?>">
    <meta name="twitter:description" content="<?=$description?>">
    <meta name="twitter:image" content="<?=$image_seo?>">
    <meta name="twitter:url" content="<?=$url_canon?>">

    <!-- Link -->
    <link rel="canonical" href="<?=$url_canon?>" />
    <link rel="icon" href="assets/img/extragone.ico">

    <!-- Balise Base -->
    <base href="<?=$base?>">

    <!-- Tailwind CSS -->
    <script src="assets/js/talwind3.4.16.js"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
              colors: {
                'primary': '#335ca3',
                'secondary': '#d83b39'
              }
            }
        }
      };
      
      // Gestion du thème
      (function () {
        function getCookie(name) {
          const nameEQ = name + "=";
          const ca = document.cookie.split(';');
          for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
          }
          return null;
        }
        
        const theme = getCookie('theme') || localStorage.getItem('theme') || 'light';
        
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>

    <?php if(!is_admin_logged_in() && basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
    <!-- Statistiques -->
    <script src="https://www.extrag.one/assets/js/analytics.js" defer></script>
    <?php endif; ?>
    
    <!-- Gradiant CSS -->
    <link rel="stylesheet" href="assets/css/gradient-background.css">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://www.extrag.one/assets/fontawesome/css/all.min.css">
    
    <style>
      /* Animations sidebar */
      .sidebar-enter {
        animation: slideIn 0.3s ease-out forwards;
      }
      .sidebar-exit {
        animation: slideOut 0.3s ease-out forwards;
      }
      @keyframes slideIn {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
      }
      @keyframes slideOut {
        from { transform: translateX(0); }
        to { transform: translateX(100%); }
      }
      
      /* Backdrop blur */
      .backdrop-blur-custom {
        backdrop-filter: blur(4px);
      }
    </style>
  </head>
  <body class="bg-slate-50 text-slate-900 dark:bg-gray-900 dark:text-white min-h-screen flex flex-col">

    <!-- Gradiant Background -->
    <div class="gradient-bg"></div>
    
    <!-- Header Modern -->
    <header class="bg-gray-100/90 dark:bg-slate-950/90 backdrop-blur-sm shadow-md sticky top-0 z-40">
      <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-4">
          
          <!-- Logo -->
          <a href="" class="flex items-center gap-2 flex-shrink-0 group">
            <img src="assets/img/logo.webp" 
                 class="w-10 h-10 transition-transform duration-300 group-hover:scale-110" 
                 alt="Logo eXtragone">
            <span class="hidden sm:block text-xl font-bold bg-gradient-to-r from-primary to-slate-500 dark:from-slate-400 dark:to-slate-300 text-transparent bg-clip-text">
              eXtragone
            </span>
          </a>

          <!-- Navigation primaire (Desktop) -->
          <nav class="hidden lg:flex items-center gap-6 text-sm font-medium">
            <a href="outils" class="hover:text-primary transition-colors duration-200">
              <i class="fa-solid fa-grip mr-1.5"></i>Outils
            </a>
            <a href="categories" class="hover:text-primary transition-colors duration-200">
              <i class="fa-solid fa-folder-open mr-1.5"></i>Catégories
            </a>
          </nav>

          <!-- Barre de recherche (Desktop & Tablet) -->
          <form method="GET" action="outils.php" class="hidden md:flex flex-1 max-w-md">
            <div class="relative w-full">
              <input 
                type="text" 
                name="q" 
                value="<?= htmlspecialchars($recherche) ?>" 
                placeholder="Rechercher un outil..." 
                class="w-full pl-10 pr-4 py-2 rounded-lg text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
              />
              <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            </div>
          </form>

          <!-- Actions rapides (Desktop uniquement) -->
          <div class="hidden lg:flex items-center gap-3">
            <!-- Profil / Connexion -->
            <?php if ($current_user): ?>
              <a href="https://projets.extrag.one/membre/<?= $current_user['username'] ?>" 
                 class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors"
                 title="Mon profil">
                <img src="<?= $current_user['avatar'] ?: 'https://projets.extrag.one/uploads/avatars/' . urlencode($current_user['display_name']) ?>" 
                     class="w-7 h-7 rounded-full object-cover ring-2 ring-slate-300 dark:ring-slate-600" 
                     alt="Avatar">
                <span class="hidden xl:inline text-sm font-medium"><?= htmlspecialchars($current_user['display_name']) ?></span>
              </a>
            <?php else: ?>
              <a href="connexion" 
                 class="px-4 py-1.5 bg-primary hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Connexion
              </a>
            <?php endif; ?>

            <!-- Bouton sidebar DESKTOP -->
            <button 
              id="sidebarToggle"
              class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors"
              aria-label="Menu">
              <i class="fa-solid fa-bars text-xl"></i>
            </button>
          </div>

          <!-- Actions mobiles UNIQUEMENT -->
          <div class="flex lg:hidden items-center gap-2">
            <button 
              id="searchToggleMobile"
              class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors"
              aria-label="Rechercher">
              <i class="fa-solid fa-search text-lg"></i>
            </button>
            
            <button 
              id="sidebarToggleMobile"
              class="p-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors"
              aria-label="Menu">
              <i class="fa-solid fa-bars text-xl"></i>
            </button>
          </div>

        </div>

        <!-- Barre de recherche mobile (cachée par défaut) -->
        <div id="mobileSearchBar" class="hidden md:hidden mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
          <form method="GET" action="outils.php">
            <div class="relative">
              <input 
                type="text" 
                name="q" 
                value="<?= htmlspecialchars($recherche) ?>" 
                placeholder="Rechercher un outil..." 
                class="w-full pl-10 pr-4 py-2 rounded-lg text-sm bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary"
              />
              <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
          </form>
        </div>
      </div>
    </header>

    <!-- Sidebar Overlay -->
    <div 
      id="sidebarOverlay" 
      class="hidden fixed inset-0 bg-black/50 backdrop-blur-custom z-50 transition-opacity"
      aria-hidden="true">
    </div>

    <!-- Sidebar -->
    <aside 
      id="sidebar" 
      class="hidden fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white dark:bg-slate-900 shadow-2xl z-50 overflow-y-auto"
      role="dialog"
      aria-modal="true">
      
      <!-- Header Sidebar -->
      <div class="sticky top-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
        <h2 class="text-lg font-bold">Menu</h2>
        <button 
          id="sidebarClose"
          class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
          aria-label="Fermer">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <!-- Contenu Sidebar -->
      <div class="p-6 space-y-6">
        
        <!-- Navigation mobile (Outils/Catégories) -->
        <div class="lg:hidden space-y-2">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
            Navigation
          </h3>
          <a href="outils" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-grip text-primary w-5"></i>
            <span class="font-medium">Outils</span>
          </a>
          <a href="categories" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-folder-open text-primary w-5"></i>
            <span class="font-medium">Catégories</span>
          </a>
          <a href="ajouter" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-plus text-green-500 w-5"></i>
            <span class="font-medium">Ajouter un outil</span>
          </a>
        </div>

        <hr class="lg:hidden border-slate-200 dark:border-slate-700">

        <!-- Écosystème eXtragone -->
        <div class="space-y-2">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
            Écosystème
          </h3>
          
          <a href="https://projets.extrag.one" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-folder-open text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium group-hover:text-primary transition-colors">Projets</div>
              <div class="text-xs text-slate-500">Communauté & reviews</div>
            </div>
            <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-primary transition-colors"></i>
          </a>

          <a href="https://nomi.extrag.one" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-wand-magic-sparkles text-green-600 dark:text-green-400"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium group-hover:text-primary transition-colors">Nomi</div>
              <div class="text-xs text-slate-500">Générateur de noms IA</div>
            </div>
            <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-primary transition-colors"></i>
          </a>

          <a href="articles" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
            <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-newspaper text-orange-600 dark:text-orange-400"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium group-hover:text-primary transition-colors">Articles</div>
              <div class="text-xs text-slate-500">Guides & actualités</div>
            </div>
            <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-primary transition-colors"></i>
          </a>
        </div>

        <hr class="border-slate-200 dark:border-slate-700">

        <!-- Profil utilisateur -->
        <?php if ($current_user): ?>
        <div class="space-y-2">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
            Mon compte
          </h3>
          
          <!-- Info profil -->
          <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
            <div class="flex items-center gap-3 mb-3">
              <img src="<?= $current_user['avatar'] ?: 'https://projets.extrag.one/uploads/avatars/' . urlencode($current_user['display_name']) ?>" 
                   class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-300 dark:ring-slate-600" 
                   alt="Avatar">
              <div class="flex-1 min-w-0">
                <div class="font-medium truncate"><?= htmlspecialchars($current_user['display_name']) ?></div>
                <div class="text-xs text-slate-500">@<?= htmlspecialchars($current_user['username']) ?></div>
              </div>
            </div>
          </div>

          <a href="https://projets.extrag.one/membre/<?= $current_user['username'] ?>" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-user w-5 text-slate-600 dark:text-slate-400"></i>
            <span>Mon profil</span>
          </a>

          <a href="https://projets.extrag.one/reglages" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-gear w-5 text-slate-600 dark:text-slate-400"></i>
            <span>Réglages</span>
          </a>

          <a href="https://projets.extrag.one" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-folder-open w-5 text-slate-600 dark:text-slate-400"></i>
            <span>Mes projets</span>
          </a>

          <?php if (isReviewer()): ?>
          <a href="https://projets.extrag.one/reviewer/dashboard" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-star w-5 text-purple-500"></i>
            <span>Dashboard reviewer</span>
          </a>
          <?php endif; ?>

          <?php if (is_admin_logged_in()): ?>
          <a href="admin" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-shield w-5 text-red-500"></i>
            <span>Administration</span>
          </a>
          <?php endif; ?>
        </div>

        <hr class="border-slate-200 dark:border-slate-700">
        <?php endif; ?>

        <!-- Autres liens -->
        <div class="space-y-2">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
            Autres
          </h3>
          
          <a href="contact" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-envelope w-5 text-slate-600 dark:text-slate-400"></i>
            <span>Contact</span>
          </a>

          <a href="https://chromewebstore.google.com/detail/fjcfkhkhplfpngmffdkfekcpelkaggof" target="_blank" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-brands fa-chrome w-5 text-slate-600 dark:text-slate-400"></i>
            <span>Extension Chrome</span>
            <i class="fa-solid fa-external-link text-xs text-slate-400 ml-auto"></i>
          </a>

          <!-- Toggle thème -->
          <button 
            id="themeToggleSidebar"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors w-full text-left">
            <i class="fa-solid fa-moon w-5 text-slate-600 dark:text-slate-400 dark:hidden"></i>
            <i class="fa-solid fa-sun w-5 text-yellow-500 hidden dark:inline"></i>
            <span>Thème : <span id="themeLabel" class="font-medium">Clair</span></span>
          </button>
        </div>

        <?php if ($current_user): ?>
        <hr class="border-slate-200 dark:border-slate-700">
        
        <!-- Déconnexion -->
        <a href="deconnexion" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors font-medium">
          <i class="fa-solid fa-right-from-bracket w-5"></i>
          <span>Déconnexion</span>
        </a>
        <?php else: ?>
        <a href="connexion" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary text-white hover:bg-blue-600 transition-colors font-medium justify-center">
          <i class="fa-solid fa-right-to-bracket"></i>
          <span>Connexion / Inscription</span>
        </a>
        <?php endif; ?>

      </div>
    </aside>

    <main class="flex-grow">
    
    <?php
    // Affichage des messages flash
    if (isset($_SESSION['success'])): ?>
        <div class="max-w-7xl mx-auto px-5 mt-4">
            <div class="bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl">
                <i class="fa-solid fa-check-circle mr-2"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
        </div>
    <?php 
        unset($_SESSION['success']);
    endif;
    
    if (isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-5 mt-4">
            <div class="bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 rounded-xl">
                <i class="fa-solid fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
        </div>
    <?php 
        unset($_SESSION['error']);
    endif;
    ?>