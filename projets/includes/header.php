<?php

if($base == "/eXtragone/") $base = "/eXtragone/projets/"; // dev
if($base == "https://www.extrag.one") {
    $base = "https://projets.extrag.one"; //defaut)
    if(!isset($url_canon)) $url_canon = "https://projets.extrag.one";
    if(!isset($image_seo)) $image_seo = "https://projets.extrag.one/assets/images/og-image.png";
}

if(!isset($noindex)) $noindex = FALSE;

// Récupérer l'utilisateur connecté
$current_user = getCurrentUser();

?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <title><?=$title?></title>
    
    <!-- Meta -->
    <meta charset="UTF-8" />
    <?php if($noindex): ?>
    <meta name="robots" content="noindex">
    <?php endif; ?>
    
    <meta name="application-name" content="Projets eXtragone"/>
    <meta name="author" content="InnoSpira"/>
    <meta name="description" content="<?=$description?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Open Graph -->
    <meta property="og:site_name" content="Projets eXtragone">
    <meta property="og:title" content="<?=$title?>">
    <meta property="og:description" content="<?=$description?>">
    <meta property="og:image" content="<?=$image_seo?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?=$url_canon?>">

    <!-- Link -->
    <link rel="canonical" href="<?=$url_canon?>" />
    <link rel="icon" href="assets/images/favicon.ico">
    
    <!-- Base -->
    <base href="<?=$base?>">

    <!-- Tailwind CSS -->
    <script src="https://www.extrag.one/assets/js/talwind3.4.16.js"></script>
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
        
        const theme = getCookie('theme') || localStorage.getItem('theme') || 'dark';
        
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>

    <!-- Statistiques -->
    <script src="https://www.extrag.one/assets/js/analytics.js" defer></script>

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://www.extrag.one/assets/fontawesome/css/all.min.css">
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-gray-900 dark:text-white min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-gray-100 dark:bg-slate-950 shadow px-4 md:px-6 py-4">
        <div class="container mx-auto flex items-center justify-between">
            
            <!-- Logo & Titre -->
            <div class="flex items-center gap-2 md:gap-3 text-xl md:text-2xl bg-gradient-to-r from-primary to-slate-500 dark:from-slate-600 dark:to-slate-300 text-transparent bg-clip-text">
                <a href="<?=$base?>" class="transition-transform duration-300 hover:scale-105 flex-shrink-0">
                    <img src="assets/images/logo_projets.png" class="w-[40px] md:w-[50px]" alt="Logo Projets">
                </a>
                <div class="space-y-0">
                    <h1 class="text-lg md:text-2xl"><a href="<?=$base?>">Projets</a></h1>
                    <a href="https://www.extrag.one" class="hidden md:flex items-center gap-1 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 hover:font-bold transition">
                        by eXtrag.one
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Menu desktop -->
            <nav class="hidden md:flex space-x-4 lg:space-x-6 items-center text-sm">
                <a href="<?=$base?>" class="hover:text-blue-500 transition-colors duration-300">Projets</a>
                <a href="top-reviewers" class="hover:text-blue-500 transition-colors duration-300">Top Reviewers</a>
                
                <?php if ($current_user): ?>
                    <?php if (isReviewer()): ?>
                        <a href="reviewer/dashboard" class="text-blue-500 hover:text-blue-600 font-bold transition-colors duration-300 flex items-center gap-1">
                            <span class="hidden lg:inline">Dashboard</span>
                            <?php 
                            $pending = getPendingReviewCount();
                            if ($pending > 0): 
                            ?>
                                <span class="px-1.5 py-0.5 bg-orange-500 text-white text-xs rounded-full"><?=$pending?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="soumettre" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
                        <i class="fa-solid fa-plus lg:mr-1"></i>
                        <span class="hidden lg:inline">Soumettre</span>
                    </a>
                    
                    <!-- Dropdown user avec click -->
                    <div class="relative" id="userDropdownMenu">
                        <button type="button" id="userDropdownButton" class="flex items-center gap-2 hover:text-blue-500 transition-colors">
                            <img src="<?= $current_user['avatar'] ?: $base.'/uploads/avatars/'.$current_user['display_name'] ?>" 
                                 class="w-8 h-8 object-cover rounded-full ring-1 ring-slate-300/70 dark:ring-white/10" 
                                 alt="Avatar">
                            <span class="hidden lg:inline"><?= htmlspecialchars($current_user['display_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="userDropdownContent" class="hidden absolute right-0 top-full mt-2 min-w-[200px] bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 py-2 z-50">
                            <a href="membre/<?= $current_user['username'] ?>" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <i class="fa-solid fa-user mr-2"></i>Mon profil
                            </a>
                            <a href="reglages" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <i class="fa-solid fa-gear mr-2"></i>Réglages
                            </a>
                            <?php if (!isReviewer()): ?>
                            <a href="devenir-reviewer" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                <i class="fa-solid fa-star mr-2"></i>Devenir reviewer
                            </a>
                            <?php endif; ?>
                            <hr class="my-2 border-slate-200 dark:border-slate-700">
                            <a href="deconnexion" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-red-500">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="connexion" class="hover:text-blue-500 transition-colors duration-300">Connexion</a>
                    <a href="soumettre" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
                        <i class="fa-solid fa-plus mr-1"></i>
                        Soumettre
                    </a>
                <?php endif; ?>
                
                <!-- Thème toggle -->
                <span id="themeToggle" class="cursor-pointer hover:text-blue-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2M12 20v2m-7.07-2.93 1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2m-13.66 5.66-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
                    </svg>
                </span>
            </nav>

            <!-- Menu mobile toggle -->
            <div class="flex md:hidden items-center gap-2">               
                <button id="menu-toggle" class="text-gray-600 dark:text-gray-300 hover:text-blue-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path id="menu-icon-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path id="menu-icon-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu mobile -->
        <nav id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-slate-200 dark:border-slate-700 pt-4">
            <div class="flex flex-col space-y-3">
                <a href="<?=$base?>" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    <i class="fa-solid fa-home mr-2"></i>Projets
                </a>
                <a href="top-reviewers" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    <i class="fa-solid fa-trophy mr-2"></i>Top Reviewers
                </a>
                
                <?php if ($current_user): ?>
                    <?php if (isReviewer()): ?>
                        <a href="reviewer/dashboard" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors flex items-center justify-between">
                            <span><i class="fa-solid fa-clipboard-check mr-2"></i>Dashboard Reviewer</span>
                            <?php 
                            $pending = getPendingReviewCount();
                            if ($pending > 0): 
                            ?>
                                <span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full"><?=$pending?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="soumettre" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
                        <i class="fa-solid fa-plus mr-2"></i>Soumettre un projet
                    </a>
                    
                    <hr class="border-slate-200 dark:border-slate-700">
                    
                    <div class="px-4 py-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <img src="<?= $current_user['avatar'] ?: $base.'/uploads/avatars/'.$current_user['display_name'] ?>" 
                             class="w-8 h-8 object-cover rounded-full ring-1 ring-slate-300/70 dark:ring-white/10" 
                             alt="Avatar">
                        <span><?= htmlspecialchars($current_user['display_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    
                    <a href="membre/<?= $current_user['username'] ?>" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fa-solid fa-user mr-2"></i>Mon profil
                    </a>
                    <a href="reglages" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fa-solid fa-gear mr-2"></i>Réglages
                    </a>
                    <?php if (!isReviewer()): ?>
                    <a href="devenir-reviewer" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fa-solid fa-star mr-2"></i>Devenir reviewer
                    </a>
                    <?php endif; ?>
                    <a href="deconnexion" class="px-4 py-2 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors text-red-500">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i>Déconnexion
                    </a>
                <?php else: ?>
                    <a href="connexion" class="px-4 py-2 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Connexion
                    </a>
                    <a href="soumettre" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
                        <i class="fa-solid fa-plus mr-2"></i>Soumettre un projet
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
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