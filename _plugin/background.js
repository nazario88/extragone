function checkIfSiteHasAlternative(url, tabId) {
  fetch(`https://extrag.one/includes/get-alternatives.php?site=${encodeURIComponent(url)}`)
    .then(response => response.json())
    .then(data => {
      if (data.alternatives && data.alternatives.length > 0) {
        // Alternative trouvée → clignotement
        startIconBlink(tabId);
      } else if (data.is_french && data.is_french.length > 0) {
        // Outil français → icône verte fixe
        stopIconBlink();
        chrome.action.setIcon({
          tabId: tabId,
          path: {
            "16": "icons/icon16-active.png",
            "32": "icons/icon32-active.png",
            "48": "icons/icon48-active.png"
          }
        });
      } else {
        // Pas d'alternative → icône grise
        stopIconBlink();
        chrome.action.setIcon({
          tabId: tabId,
          path: {
            "16": "icons/icon16.png",
            "32": "icons/icon32.png",
            "48": "icons/icon48.png"
          }
        });
      }
    })
    .catch(error => {
      console.error("Erreur API eXtragone:", error);
    });
}

// Écoute des changements de page
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  if (changeInfo.status === 'complete') {
    checkIfSiteHasAlternative(tab.url, tabId);
  }
});

// Écoute des changements d'onglet actif
chrome.tabs.onActivated.addListener(activeInfo => {
  chrome.tabs.get(activeInfo.tabId, tab => {
    if (tab.status === "complete") {
      checkIfSiteHasAlternative(tab.url, tab.id);
    }
  });
});

/* Gestion du clignotement */
let blinkInterval = null;

function startIconBlink(tabId) {
  const icon1 = {
    "16": "icons/icon16-active.png",
    "32": "icons/icon32-active.png",
    "48": "icons/icon48-active.png"
  };

  const icon2 = {
    "16": "icons/icon16.png",
    "32": "icons/icon32.png",
    "48": "icons/icon48.png"
  };

  let toggle = false;
  stopIconBlink();

  blinkInterval = setInterval(() => {
    chrome.action.setIcon({
      tabId: tabId,
      path: toggle ? icon1 : icon2
    });
    toggle = !toggle;
  }, 500);
}

function stopIconBlink() {
  if (blinkInterval !== null) {
    clearInterval(blinkInterval);
    blinkInterval = null;
  }
}