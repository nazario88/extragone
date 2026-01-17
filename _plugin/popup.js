// ============================================
// SYSTÈME DE TRACKING GAMIFICATION
// ============================================

/**
 * Récupère le nombre d'outils français découverts
 */
function getFrenchToolsCount() {
  const tools = JSON.parse(localStorage.getItem('frenchToolsVisited') || '[]');
  return tools.length;
}

/**
 * Ajoute un outil français au compteur
 */
function addFrenchTool(url) {
  const tools = JSON.parse(localStorage.getItem('frenchToolsVisited') || '[]');
  
  // Extraire le domaine
  const domain = new URL(url).hostname.replace('www.', '');
  
  // Ajouter si pas déjà présent
  if (!tools.includes(domain)) {
    tools.push(domain);
    localStorage.setItem('frenchToolsVisited', JSON.stringify(tools));
    
    // Animation confettis (optionnel)
    showCongrats();
  }
  
  return tools.length;
}

/**
 * Animation de félicitations
 */
function showCongrats() {
  // Simple feedback visuel
  const badge = document.querySelector('.stat-badge');
  if (badge) {
    badge.style.animation = 'bounce 0.6s ease-out';
    setTimeout(() => {
      badge.style.animation = '';
    }, 600);
  }
}

// Animation bounce
const style = document.createElement('style');
style.textContent = `
  @keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
  }
`;
document.head.appendChild(style);

chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
  const tab = tabs[0];
  const container = document.getElementById("alternatives");
  const noAlt = document.getElementById("no-alternative");
  const suggestBtn = document.getElementById("suggest-btn");
  const isFrench = document.getElementById("is-french");
  const logo = document.getElementById("logo");

  // Redirection vers extrag.one au clic logo
  logo.addEventListener("click", () => {
    chrome.tabs.create({ url: "https://extrag.one" });
  });

  // Gestion recherche
  document.getElementById("searchForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const query = document.getElementById("searchInput").value.trim();
    if (query) {
      chrome.tabs.create({ 
        url: `https://extrag.one/outils?q=${encodeURIComponent(query)}` 
      });
    }
  });

  // Appel API
  fetch(`https://extrag.one/includes/get-alternatives.php?site=${encodeURIComponent(tab.url)}`)
    .then(response => response.json())
    .then(data => {
      if (data.alternatives && data.alternatives.length > 0) {
        // Afficher les alternatives
        data.alternatives.forEach((alt) => {
          const card = createAlternativeCard(alt);
          container.appendChild(card);
        });
      } else if (data.is_french) {
        // ✅ OUTIL FRANÇAIS DÉTECTÉ
        isFrench.style.display = "block";
        
        // Ajouter au compteur
        const count = addFrenchTool(tab.url);
        
        // Mettre à jour l'affichage
        document.getElementById('french-count').textContent = count;
        
      } else {
        // Pas d'alternative
        noAlt.style.display = "block";
        suggestBtn.addEventListener("click", () => {
          chrome.tabs.create({ 
            url: `https://extrag.one/ajouter?site=${encodeURIComponent(tab.url)}` 
          });
        });
      }
    })
    .catch(() => {
      noAlt.style.display = "block";
      noAlt.querySelector("p").textContent = "Erreur de chargement";
    });
});

/**
 * Crée une card alternative
 */
function createAlternativeCard(alt) {
  const card = document.createElement("a");
  card.className = "alt-card";
  card.href = `https://extrag.one/outil/${alt.slug}`;
  card.target = "_blank";

  // Logo
  const logo = document.createElement("img");
  logo.src = `https://extrag.one/${alt.logo}`;
  logo.alt = alt.nom;
  logo.onerror = () => {
    logo.src = "icons/icon48.png"; // Fallback
  };

  // Infos
  const info = document.createElement("div");
  info.className = "alt-info";

  const name = document.createElement("h4");
  name.textContent = alt.nom;

  const desc = document.createElement("p");
  desc.textContent = alt.description.substring(0, 80) + 
    (alt.description.length > 80 ? "..." : "");

  // Badges
  const badges = document.createElement("div");
  badges.className = "alt-badges";

  if (alt.is_free) {
    const freeBadge = document.createElement("span");
    freeBadge.className = "badge free";
    freeBadge.innerHTML = '<i class="fa fa-gift"></i> Gratuit';
    badges.appendChild(freeBadge);
  }

  if (alt.is_paid) {
    const paidBadge = document.createElement("span");
    paidBadge.className = "badge paid";
    paidBadge.innerHTML = '<i class="fa fa-crown"></i> Payant';
    badges.appendChild(paidBadge);
  }

  info.appendChild(name);
  info.appendChild(desc);
  info.appendChild(badges);

  card.appendChild(logo);
  card.appendChild(info);

  return card;
}