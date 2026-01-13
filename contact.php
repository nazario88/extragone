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
$title = "Prendre contact avec l'équipe eXtragone";
$description = "Une question, une demande d'informations ou une demande de sponsoring ? Nous sommes à votre écoute, n'hésitez pas à prendre contact avec l'équipe eXtragone.";

$url_canon = 'https://www.extrag.one/contact';

include 'includes/header.php';
?>

<div class="w-full p-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; contact</p>
    <h1 class="my-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        Demande d'informations et contact
    </h1>
    <?=$message?>

    <!-- Section Contact -->
    <div class="w-full px-5 py-5 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="text-2xl font-bold mb-4">Pourquoi nous contacter ?</h2>
        
        <div class="space-y-4">
            <!-- Proposer un outil -->
            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
                    <i class="fa-solid fa-plus text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-1">Proposer un outil français</h3>
                    <p class="text-sm">
                        Vous connaissez ou développez un outil français qui mérite d'être référencé ? 
                        Faites-le nous savoir ! Nous sommes toujours à la recherche de nouvelles pépites de la French Tech.
                    </p>
                </div>
            </div>

            <!-- Partenariats -->
            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
                    <i class="fa-solid fa-handshake text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-1">Partenariats & sponsoring</h3>
                    <p class="text-sm">
                        Intéressé par une collaboration ou un partenariat ? 
                        Contactez-nous pour discuter des opportunités de mise en avant de votre solution.
                    </p>
                </div>
            </div>

            <!-- Corrections -->
            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
                    <i class="fa-solid fa-flag text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-1">Signaler une erreur</h3>
                    <p class="text-sm">
                        Une information incorrecte ? Un lien cassé ? Aidez-nous à améliorer la qualité 
                        du catalogue en nous signalant toute erreur ou incohérence.
                    </p>
                </div>
            </div>

            <!-- Questions générales -->
            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
                    <i class="fa-solid fa-envelope text-blue-500 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-1">Questions & suggestions</h3>
                    <p class="text-sm">
                        Une question sur le projet ? Une idée d'amélioration ? 
                        N'hésitez pas à nous écrire, nous répondons à tous les messages !
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <p class="text-sm">
                <i class="fa-solid fa-info-circle mr-2"></i>
                <strong>Pour en savoir plus sur notre mission et nos valeurs,</strong> 
                consultez notre page 
                <a href="a-propos" class="border-b-2 border-blue-500 hover:border-dotted font-medium">À propos d'eXtragone</a>.
            </p>
        </div>
    </div>

    <!-- Separateur -->
    <hr class="my-8 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; envoyer un message</p>
    <h2 class="my-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        Prise de contact avec l'équipe
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
                <div class="flex items-center justify-center">
                    <input type="hidden" name="recaptcha_token" id="recaptcha-token">
                    <button type="button" onclick="validForm()" class="w-200 bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 transition"><i class="fa-solid fa-check"></i> Valider</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>