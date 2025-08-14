chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
  const tab = tabs[0];

  const container = document.getElementById("alternatives");
  const noAlt = document.getElementById("no-alternative");
  const suggestBtn = document.getElementById("suggest-btn");
  const isFrench = document.getElementById("is-french");

  const logo = document.getElementById("logo");
  logo.addEventListener("click", () => {
    const extrag = `https://extrag.one`;
    chrome.tabs.create({ url: extrag });
  });

  // Appel à l'API
  fetch(`https://extrag.one/includes/get-alternatives.php?site=${encodeURIComponent(tab.url)}`)
    .then(response => response.json())
    .then(data => {
      if (data.alternatives && data.alternatives.length > 0) {
        data.alternatives.forEach((alt) => {
          const a = document.createElement("a");
          a.href = "https://extrag.one/outil/"+alt.slug;
          a.textContent = `➡️ ${alt.nom}`;
          a.className = "alt-link";
          a.target = "_blank";
          container.appendChild(a);
        });
      } else if(data.is_french && data.is_french.length > 0) {
        isFrench.style.display = "inline-block";
      } else {
        noAlt.style.display = "block";
        suggestBtn.style.display = "inline-block";
        suggestBtn.addEventListener("click", () => {
          const url = `https://extrag.one/ajouter?site=${encodeURIComponent(tab.url)}`;
          chrome.tabs.create({ url });
        });
      }
    })
    .catch(error => {
      console.error("Erreur lors de l'appel à l'API eXtragone :", error);
      noAlt.style.display = "block";
      noAlt.textContent = "Erreur de chargement des alternatives.";
    });
});
