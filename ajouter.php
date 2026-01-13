<?php
include 'includes/config.php';

// Récupérer toutes les catégories pour les afficher dans le formulaire
$stmt = $pdo->query('SELECT * FROM extra_tools_categories');
$categories = $stmt->fetchAll();

$add_comment = $add_categorie_id = $add_url = $add_description = $add_nom = $message = '';

if(isset($_GET['site'])) {
    $site = strtolower(trim($_GET['site']));
    $domain = str_replace("www.","",parse_url($site, PHP_URL_HOST));

    $add_description = 'Alternative du site '.$domain;
}

if(isset($_GET['nom'])) {
    $add_nom = trim($_GET['nom']);
}

/* Proposition
—————————————————————————————————————————————*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $add_nom = isset($_POST['nom']) ? sanitizeInput($_POST['nom']) : '';
    $add_description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    $add_url = isset($_POST['url']) ? sanitizeInput($_POST['url']) : '';
    $add_categorie_id = isset($_POST['categorie']) ? (int) $_POST['categorie'] : 0;
    $add_comment = isset($_POST['comment']) ? sanitizeInput($_POST['comment']) : '';


    $stmt = $pdo->prepare('INSERT INTO extra_tools (nom, categorie_id, description, url, comment) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$add_nom, $add_categorie_id, $add_description, $add_url, $add_comment]);

    $message = '<p class="mx-auto text-center p-2 bg-green-300 text-green-800 rounded w-1/2">La proposition a bien été effectuée.</p>';

    $add_comment = $add_categorie_id = $add_url = $add_description = $add_nom = '';
}

/* SEO
—————————————————————————————————————————————*/
$title = "Ajouter un nouvel outil";
$description = "Proposer un nouvel outil à ajouter dans la liste des outils eXtragone. Qu'il s'agisse d'un outil français, d'une alternative à un outil existant. ou autre.";

$url_canon = 'https://www.extrag.one/ajouter';

include 'includes/header.php';
?>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Ajout</p>
    <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        Proposer un nouvel outil
    </h1>
    <?=$message?>
</div>
<div class="flex items-center justify-center">
    <div class="p-6 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700 w-full md:w-1/2">
        <form method="post" name="add_tool">
            <!-- Nom -->
            <div class="mb-4">
                <label for="nom" class="block font-medium">Nom de l'outil</label>
                <input type="text" id="nom" name="nom" value="<?=$add_nom?>" placeholder="Nom de l'outil" class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" required minlength="3">
            </div>

            <!-- Description courte -->
            <div class="mb-4">
                <label for="description" class="block font-medium">Description</label>
                <textarea id="description" name="description" placeholder="Que fait l'outil ?" rows=2 class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" minlength="20"><?=$add_description;?></textarea>
            </div>


            <!-- URL -->
            <div class="mb-4">
                <label for="url" class="block font-medium">URL du site</label>
                <input type="url" id="url" name="url" value="<?=$add_url?>" placeholder="URL" class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" required minlength="5">
            </div>

            <!-- Catégorie -->
            <div class="mb-4">
                <label for="categorie" class="block font-medium">Catégorie</label>
                <!-- Select -->
                <select class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" name="categorie" id="categorie">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?php echo $categorie['id']; ?>" <?php echo ($add_categorie_id == $categorie['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categorie['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Commentaire interne -->
            <div class="mb-4">
                <label for="comment" class="block font-medium">Autre précision, pour la modération ?</label>
                <textarea id="comment" rows=3 name="comment" placeholder="Est-ce un outil français ? Outil alternatif de quel(s) outil(s) français ?" class="w-full px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring"><?=$add_comment;?></textarea>
                <p class="text-sm text-gray-500">Indiquez vos coordonnées si vous souhaitez être informé de l'ajout.</p>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 transition"><i class="fa-solid fa-check"></i> Valider</button>
            </div>

        </form>
    </div>
</div>

<?php
include 'includes/footer.php';
?>