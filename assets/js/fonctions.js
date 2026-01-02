/* ============================================
   FONCTIONS GÉNÉRALES - eXtragone
   ============================================ */

/* Menu mobile (ancien code)
——————————————————————————————————————————————————*/
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('menu-toggle');
  const menu = document.getElementById('mobile-menu');
  const title = document.getElementById('menu-title');
  const menuIconOpen = document.getElementById('menu-icon-open');
  const menuIconClose = document.getElementById('menu-icon-close');

  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      menu.classList.toggle('hidden');
      if (title) title.classList.toggle('hidden');
      if (menuIconOpen) menuIconOpen.classList.toggle('hidden');
      if (menuIconClose) menuIconClose.classList.toggle('hidden');
    });
  }
});

/* Utilitaires pour les cookies
——————————————————————————————————————————————————*/
function setCookie(name, value, days = 365) {
  const expires = new Date();
  expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;domain=.extrag.one;SameSite=Lax`;
}

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

/* Changement de thème (ancien code - reste compatible)
——————————————————————————————————————————————————*/
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('themeToggle');
  const root = document.documentElement;

  if (!toggleBtn) return;

  // Récupérer le thème depuis le cookie
  let theme = getCookie('theme');
  
  // Migration depuis localStorage si nécessaire
  if (!theme && localStorage.theme) {
    theme = localStorage.theme;
    setCookie('theme', theme);
  }
  
  // Appliquer le thème
  if (theme === 'dark') {
    root.classList.add('dark');
  } else {
    root.classList.remove('dark');
  }

  toggleBtn.addEventListener('click', () => {
    root.classList.toggle('dark');
    const newTheme = root.classList.contains('dark') ? 'dark' : 'light';
    
    setCookie('theme', newTheme);
    localStorage.theme = newTheme;

    updateIcon();
  });

  const updateIcon = () => {
    toggleBtn.innerHTML = root.classList.contains('dark')
      ? sunIcon
      : moonIcon;
  };

  // Icônes SVG
  const sunIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 dark:text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path>
    </svg>`;
  const moonIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
        d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>`;

  updateIcon();
});

/* Slug (ancien code)
——————————————————————————————————————————————————*/
function slugify(text) {
  return text.toString().toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/* Recherche lors de la saisie (ancien code)
——————————————————————————————————————————————————*/
let outilsData = [];

window.addEventListener('DOMContentLoaded', () => {
  fetch('https://www.extrag.one/includes/get-outils.php')
    .then(response => response.json())
    .then(data => {
      outilsData = data;
    });
});

function handleSearch(query) {
  const container = document.getElementById("search-results");
  if (!container) return;

  if (!query.trim()) {
    container.innerHTML = '';
    container.classList.add('hidden');
    return;
  }

  const results = outilsData.filter(outil => {
    const content = `${outil.n} ${outil.d}`.toLowerCase();
    return content.includes(query.toLowerCase());
  });

  displayResults(results);
}

function displayResults(results) {
  const container = document.getElementById("search-results");
  if (!container) return;

  if (results.length === 0) {
    container.innerHTML = '';
    container.classList.add('hidden');
    return;
  }

  container.innerHTML = results.map(r => {
    return `
      <a href="outil/${r.s}" class="block px-6 py-4 hover:bg-gray-100 dark:hover:bg-slate-700 border-b border-gray-100 dark:border-slate-600">
        <strong class="text-blue-600 dark:text-blue-500">${r.n}</strong><br>
        <span class="text-sm text-gray-500 dark:text-slate-300">${r.d}</span>
      </a>
    `;
  }).join('');

  container.classList.remove('hidden');
}

/* ============================================
   HEADER MODERNE - Sidebar coulissante
   ============================================ */

window.HeaderModern = (function() {
  'use strict';
  
  // GESTION SIDEBAR
  const headerSidebar = document.getElementById('sidebar');
  const headerOverlay = document.getElementById('sidebarOverlay');
  const headerToggleBtn = document.getElementById('sidebarToggle'); // Desktop
  const headerToggleMobileBtn = document.getElementById('sidebarToggleMobile'); // Mobile
  const headerCloseBtn = document.getElementById('sidebarClose');

  function openSidebar() {
    if (!headerSidebar) return;
    headerSidebar.classList.remove('hidden');
    headerOverlay.classList.remove('hidden');
    headerSidebar.classList.add('sidebar-enter');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (!headerSidebar) return;
    headerSidebar.classList.remove('sidebar-enter');
    headerSidebar.classList.add('sidebar-exit');
    setTimeout(() => {
      headerSidebar.classList.add('hidden');
      headerSidebar.classList.remove('sidebar-exit');
      headerOverlay.classList.add('hidden');
      document.body.style.overflow = '';
    }, 300);
  }

  // Events listeners - Desktop ET Mobile
  if (headerToggleBtn) headerToggleBtn.addEventListener('click', openSidebar);
  if (headerToggleMobileBtn) headerToggleMobileBtn.addEventListener('click', openSidebar);
  if (headerCloseBtn) headerCloseBtn.addEventListener('click', closeSidebar);
  if (headerOverlay) headerOverlay.addEventListener('click', closeSidebar);

  // Fermeture avec ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && headerSidebar && !headerSidebar.classList.contains('hidden')) {
      closeSidebar();
    }
  });

  // TOGGLE RECHERCHE MOBILE
  const headerSearchToggle = document.getElementById('searchToggleMobile');
  const headerSearchBar = document.getElementById('mobileSearchBar');

  if (headerSearchToggle && headerSearchBar) {
    headerSearchToggle.addEventListener('click', () => {
      headerSearchBar.classList.toggle('hidden');
      if (!headerSearchBar.classList.contains('hidden')) {
        const input = headerSearchBar.querySelector('input');
        if (input) input.focus();
      }
    });
  }

  // TOGGLE THÈME SIDEBAR
  function toggleSidebarTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const themeLabel = document.getElementById('themeLabel');
    
    if (isDark) {
      html.classList.remove('dark');
      localStorage.setItem('theme', 'light');
      setCookie('theme', 'light');
      if (themeLabel) themeLabel.textContent = 'Clair';
    } else {
      html.classList.add('dark');
      localStorage.setItem('theme', 'dark');
      setCookie('theme', 'dark');
      if (themeLabel) themeLabel.textContent = 'Sombre';
    }
  }

  // Init theme label
  document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.classList.contains('dark');
    const themeLabel = document.getElementById('themeLabel');
    if (themeLabel) {
      themeLabel.textContent = isDark ? 'Sombre' : 'Clair';
    }
  });

  const headerThemeToggle = document.getElementById('themeToggleSidebar');
  if (headerThemeToggle) {
    headerThemeToggle.addEventListener('click', toggleSidebarTheme);
  }

  // API publique
  return {
    openSidebar: openSidebar,
    closeSidebar: closeSidebar,
    toggleTheme: toggleSidebarTheme
  };
})();