<?php
include 'includes/config.php';

$to = $_ENV['CONTACT_EMAIL'];

$name = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
$from = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$subject = '[eXtrag.one] Message provenant du site';
$subject .= filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
$message = nl2br($message);

$body = "
  <html><body>
  <font face='arial' size=2>Message du site eXtrag.one de la part de <b>$name ($from)</b>:
  <hr>
  $message
  <hr>
  Email envoyé depuis le site.
  </font>
  </body></html>";

$headers = 'From: <noreply@extrag.one>' . "\r\n";

// The content type is required when sending HTML Based emails.
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "MIME-Version: 1.0" . "\r\n";

// CAPTCHA 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'];
    $recaptcha_token = $_POST['recaptcha_token'];

    // Vérifier le token avec Google
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_token}");
    $response_keys = json_decode($response, true);

    if ($response_keys["success"] && $response_keys["score"] > 0.5) {
        // reCAPTCHA valide, traiter le formulaire
        // C'est OK , on continue ! (donc on fait rien)
    } else {
        echo "Échec du reCAPTCHA, action bloquée.";
        exit;
    }
    if(mail($to,$subject,$body,$headers)) {
      $message = '<p class="mx-auto text-center p-2 bg-green-300 text-green-800 rounded w-1/2">Le message a bien été envoyé.</p>';
    }
    else {
      $message = '<p class="mx-auto text-center p-2 bg-red-300 text-red-800 rounded w-1/2">Une erreur a été rencontrée, veuillez réessayer.</p>';
    }
}

/* SEO
—————————————————————————————————————————————*/
$title = "À propos d'eXtragone";
$description = "Une question, suggestion ? N'hésite pas à prendre contact avec l'équipe eXtragone.";

$url_canon = 'https://www.extrag.one/contact';

include 'includes/header.php';
?>

<div class="w-full p-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; a propos</p>
    <h1 class="my-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        En savoir + sur eXtragone
    </h1>
    <?=$message?>

    <!-- A propos -->
    <div class="w-full px-5 py-5 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <p class="p-2 m-2">
            Le projet vise à mettre en avant les outils web français, qui peuvent constituer des alternatives sérieuses. L'idée n'est pas de remplacer l'ensemble de vos outils par des solutions 100 % françaises, mais plutôt de vous faire découvrir nos propres outils. Se tourner vers des sites français dans un premier temps nous semble être une bonne démarche. Ces outils seront conformes aux exigences réglementaires (RGPD, etc.) et contribueront à renforcer le marché local.
        </p>
        <p class="p-2 m-2">
            &rarr; Dans le même registre, le site <a href="https://european-alternatives.eu" target="_blank" class="border-b-2 border-blue-500 hover:border-dotted">european-alternatives.eu</a> recense les outils européens.
        </p>
        <p class="p-2 m-2">
            Ah, et l'outil a été conçu à Nantes, en France 😅. Il est hébergé sur les serveurs français d'<a href="outil/ovh">OVH</a>.
        </p>
    </div>

    <!-- Separateur -->
    <hr class="my-8 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; envoyer un message</p>
    <h2 class="my-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        Prendre contact avec nous
    </h2>
    <script>
    function validForm() {
        const form = document.getElementById('contactForm');
        grecaptcha.ready(function() {
            grecaptcha.execute('<?=$_ENV['RECAPTCHA_SITE_KEY'];?>', {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                document.getElementById("recaptcha-token").value = token;
                form.requestSubmit();
            });
        });
    }
    </script>

    <div class="flex items-center justify-center">
        <div class="w-full px-5 py-5 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <form method="post" id="contactForm">
                <!-- Nom -->
                <div class="mb-4">
                    <label for="nom" class="block font-medium">Votre prénom/nom</label>
                    <input type="text" id="nom" name="nom" value="" placeholder="Prénom et nom" class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" required minlength="5">
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block font-medium">Votre email</label>
                    <input type="email" id="email" name="email" value="" placeholder="john.doe@extrag.one" class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" required minlength="5">
                </div>

                <!-- Description courte -->
                <div class="mb-4">
                    <label for="message" class="block font-medium">Votre message</label>
                    <textarea id="message" name="message" placeholder="Votre message et vos coordonnées" rows=5 class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" required minlength="20"></textarea>
                </div>

                <!-- Submit Button -->
                <div>
                    <input type="hidden" name="recaptcha_token" id="recaptcha-token">
                    <button type="button" onclick="validForm()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 transition"><i class="fa-solid fa-check"></i> Valider</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>