/**
 * Analytics Tracker - Système d'analytics simple et conforme RGPD
 * Usage: <script src="https://www.extrag.one/assets/js/analytics.js" defer></script>
 */

(function() {
    'use strict';
    
    // Configuration
    const ENDPOINT = 'https://www.extrag.one/includes/analytics.php';
    
    // Attendre que la page soit chargée
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', track);
    } else {
        track();
    }
    
    function track() {
        // Préparer les données
        const data = {
            site_url: window.location.hostname,
            page_url: window.location.href,
            page_path: window.location.pathname + window.location.search,
            referrer_url: document.referrer || null,
            user_agent: navigator.userAgent,
            screen_width: window.screen.width,
            screen_height: window.screen.height,
            timestamp: new Date().getTime()
        };
        
        // Envoyer en POST asynchrone
        fetch(ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            // Ne pas bloquer la navigation
            keepalive: true
        }).catch(function(error) {
            // Échec silencieux pour ne pas perturber l'UX
            console.debug('Analytics tracking failed:', error);
        });
    }
})();