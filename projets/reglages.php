<?php
/**
 * Redirection permanente vers les réglages centralisés sur extrag.one
 * Ancien : projets.extrag.one/reglages
 * Nouveau : www.extrag.one/reglages
 */

// Redirection 301 permanente
header('Location: https://www.extrag.one/reglages', true, 301);
exit;