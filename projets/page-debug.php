<?php
/**
 * PAGE DE DEBUG COOKIES
 * Affiche TOUS les cookies pour diagnostic
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🍪 Debug Cookies</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; border: 1px solid #c3e6cb; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; border: 1px solid #f5c6cb; margin: 15px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; border: 1px solid #bee5eb; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 13px; }
        table th { background: #f8f9fa; font-weight: bold; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍪 Debug Cookies - Diagnostic complet</h1>
        
        <div class="info">
            <strong>🌐 Domaine actuel :</strong> <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?><br>
            <strong>📍 URL complète :</strong> <?= htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>
        </div>
        
        <hr>
        
        <h2>📋 Tous les cookies PHP ($_COOKIE)</h2>
        
        <?php if (empty($_COOKIE)): ?>
            <div class="error">
                <strong>❌ AUCUN COOKIE DÉTECTÉ</strong><br>
                PHP ne voit aucun cookie sur ce domaine.
            </div>
        <?php else: ?>
            <div class="success">
                <strong>✅ <?= count($_COOKIE) ?> cookie(s) détecté(s)</strong>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 200px;">Nom</th>
                        <th>Valeur (50 premiers caractères)</th>
                        <th style="width: 100px;">Longueur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_COOKIE as $name => $value): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($name) ?></code></td>
                        <td><?= htmlspecialchars(substr($value, 0, 50)) ?><?= strlen($value) > 50 ? '...' : '' ?></td>
                        <td><?= strlen($value) ?> car.</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <hr>
        
        <h2>🔍 Cookies importants pour l'authentification</h2>
        
        <table>
            <tr>
                <th style="width: 200px;">Cookie</th>
                <th>Présent ?</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td><code>session_token</code></td>
                <td>
                    <?php if (isset($_COOKIE['session_token'])): ?>
                        <span style="color: green; font-weight: bold;">✅ OUI</span>
                    <?php else: ?>
                        <span style="color: red; font-weight: bold;">❌ NON</span>
                    <?php endif; ?>
                </td>
                <td><?= isset($_COOKIE['session_token']) ? htmlspecialchars(substr($_COOKIE['session_token'], 0, 30)) . '...' : '-' ?></td>
            </tr>
            <tr>
                <td><code>PHPSESSID</code></td>
                <td>
                    <?php if (isset($_COOKIE['PHPSESSID'])): ?>
                        <span style="color: green; font-weight: bold;">✅ OUI</span>
                    <?php else: ?>
                        <span style="color: orange; font-weight: bold;">⚠️ NON</span>
                    <?php endif; ?>
                </td>
                <td><?= isset($_COOKIE['PHPSESSID']) ? htmlspecialchars(substr($_COOKIE['PHPSESSID'], 0, 30)) . '...' : '-' ?></td>
            </tr>
            <tr>
                <td><code>theme</code></td>
                <td>
                    <?php if (isset($_COOKIE['theme'])): ?>
                        <span style="color: green; font-weight: bold;">✅ OUI</span>
                    <?php else: ?>
                        <span style="color: orange;">⚠️ NON</span>
                    <?php endif; ?>
                </td>
                <td><?= isset($_COOKIE['theme']) ? htmlspecialchars($_COOKIE['theme']) : '-' ?></td>
            </tr>
        </table>
        
        <hr>
        
        <h2>🌐 Cookies JavaScript (via document.cookie)</h2>
        
        <pre id="jsCookies">Chargement...</pre>
        
        <script>
        document.getElementById('jsCookies').textContent = document.cookie || '(vide)';
        </script>
        
        <hr>
        
        <h2>🧪 Actions de test</h2>
        
        <div class="info">
            <strong>Pour tester l'authentification cross-domain :</strong><br><br>
            
            <strong>1️⃣ Sur www.extrag.one :</strong><br>
            • Déconnecte-toi : <a href="https://www.extrag.one/deconnexion" target="_blank">www.extrag.one/deconnexion</a><br>
            • Connecte-toi : <a href="https://www.extrag.one/connexion" target="_blank">www.extrag.one/connexion</a><br>
            • Vérifie que tu es bien connecté<br><br>
            
            <strong>2️⃣ Vérifie le cookie sur www.extrag.one :</strong><br>
            • Ouvre cette page : <a href="https://www.extrag.one/projets/debug-cookies.php" target="_blank">www.extrag.one/projets/debug-cookies.php</a><br>
            • Tu dois voir le cookie <code>session_token</code> ✅<br><br>
            
            <strong>3️⃣ Sur projets.extrag.one :</strong><br>
            • Recharge cette page<br>
            • Tu dois AUSSI voir le cookie <code>session_token</code> ✅
        </div>
        
        <hr>
        
        <h2>⚙️ Configuration serveur requise</h2>
        
        <div class="info">
            <strong>Pour que les cookies fonctionnent entre www.extrag.one et projets.extrag.one :</strong><br><br>
            
            ✅ Le cookie doit être créé avec <code>domain: .extrag.one</code> (avec le point)<br>
            ✅ Le cookie doit être <code>Secure</code> (HTTPS uniquement)<br>
            ✅ Le cookie doit être <code>HttpOnly</code> (pas accessible en JavaScript)<br>
            ✅ Le cookie doit être <code>SameSite: Lax</code> (autorise cross-domain entre sous-domaines)
        </div>
        
        <hr>
        
        <p style="text-align: center; color: #666; font-size: 12px;">
            ⚠️ Supprimer ce fichier après diagnostic
        </p>
    </div>
</body>
</html>