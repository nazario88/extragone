function checkIfSiteHasAlternative(url, tabId) {
  fetch(`https://extrag.one/includes/get-alternatives.php?site=${encodeURIComponent(url)}`)
    .then(response => response.json())
    .then(data => {
      if (data.alternatives && data.alternatives.length > 0) { // Si alternative trouvée
        // On démarre le clignotement
        startIconBlink(tabId);
      } else if(data.is_french && data.is_french.length > 0) { // Si outil français
        stopIconBlink();
        chrome.action.setIcon({
          tabId: tabId,
          path: {
            "16": "icons/icon16-active.png",
            "32": "icons/icon32-active.png",
            "48": "icons/icon48-active.png"
          }
        });
      } else { // Pas d'alternative trouvée
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
      //console.error("Erreur lors de l'appel à l'API eXtragone :", error);
    });
}

// Suivre les changements de page
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  if (changeInfo.status === 'complete') {
    checkIfSiteHasAlternative(tab.url, tabId);
  }
});

// Suivre les changements d'onglet actif
chrome.tabs.onActivated.addListener(activeInfo => {
  chrome.tabs.get(activeInfo.tabId, tab => {
    if (tab.status === "complete") {
      checkIfSiteHasAlternative(tab.url, tab.id);
    }
  });
});

/* Blink
—————————————————————————*/

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

  // Nettoie tout clignotement précédent
  stopIconBlink();

  blinkInterval = setInterval(() => {
    chrome.action.setIcon({
      tabId: tabId,
      path: toggle ? icon1 : icon2
    });
    toggle = !toggle;
  }, 500); // change toutes les 500ms
}

function stopIconBlink() {
  if (blinkInterval !== null) {
    clearInterval(blinkInterval);
    blinkInterval = null;
  }
}
