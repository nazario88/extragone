/* Menu mobile
——————————————————————————————————————————————————*/
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('menu-toggle');
  const menu = document.getElementById('mobile-menu');
  const title = document.getElementById('menu-title');

  toggle.addEventListener('click', () => {
    menu.classList.toggle('hidden');
    title.classList.toggle('hidden');
  });
});


/* Changement de thème
——————————————————————————————————————————————————*/
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('themeToggle')
  const root = document.documentElement

  if (localStorage.theme === 'dark') {
    root.classList.add('dark')
  }

  toggleBtn.addEventListener('click', () => {
    root.classList.toggle('dark')
    localStorage.theme = root.classList.contains('dark') ? 'dark' : 'light'

    // Changer l’icône dynamiquement
    updateIcon()
  })

  const updateIcon = () => {
    toggleBtn.innerHTML = root.classList.contains('dark')
      ? sunIcon
      : moonIcon
  }

  // Icônes SVG
  const sunIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 dark:text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path>
    </svg>`
  const moonIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
        d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>`

  updateIcon(); // initialiser l'icône au chargement
});

/* Slug
——————————————————————————————————————————————————*/
function slugify(text) {
  return text.toString().toLowerCase()
    .normalize('NFD')                     // accents → non accentué
    .replace(/[\u0300-\u036f]/g, '')      // supprime accents restants
    .replace(/\s+/g, '-')                 // espaces → tirets
    .replace(/[^\w\-]+/g, '')             // supprime tout le reste
    .replace(/\-\-+/g, '-')               // multiple tirets → un seul
    .replace(/^-+|-+$/g, '');             // supprime tirets début/fin
}


/* Recherche lors de la saisie
——————————————————————————————————————————————————*/
let outils = [];

window.addEventListener('DOMContentLoaded', () => {
  fetch('includes/get-outils.php')
    .then(response => response.json())
    .then(data => {
      outils = data;
      //displayResults(outils); //A décommenter pour afficher tous les outils d'un coup (et tester)
    });
});
function handleSearch(query) {
  const container = document.getElementById("search-results");

   // Masquer les résultats si la recherche est vide
  if (!query.trim()) {
    container.innerHTML = '';
    container.classList.add('hidden');
    return;
  }

  const results = outils.filter(outil => {
    const content = `${outil.nom} ${outil.description}`.toLowerCase();
    return content.includes(query.toLowerCase());
  });

  // Afficher les résultats
  displayResults(results);
}
function displayResults(results) {
  const container = document.getElementById("search-results");

  if (results.length === 0) {
    container.innerHTML = '';
    container.classList.add('hidden');
    return;
  }

  container.innerHTML = results.map(r => {
    const slug = slugify(r.nom);
    return `
      <a href="outil/${slug}" class="block px-6 py-4 hover:bg-gray-100 dark:hover:bg-slate-700 border-b border-gray-100 dark:border-slate-600">
        <strong class="text-blue-600 dark:text-blue-500">${r.nom}</strong><br>
        <span class="text-sm text-gray-500 dark:text-slate-300">${r.description}</span>
      </a>
    `;
  }).join('');

  container.classList.remove('hidden');
}



