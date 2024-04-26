<?php
session_start();
require 'config.php';

$titre = "Admin";

$devoir_a_montrer = "all";

if(isset($_GET['type-devoir']) && isset($devoirs[strtolower($_GET['type-devoir'])]))
{
    $devoir_a_montrer = strtolower($_GET['type-devoir']);
}

if(!isset($_SESSION["USER"]))
{
    header("Location: login.php");
}

require __DIR__ . DIRECTORY_SEPARATOR . 'dbconfig.php';

if ($devoir_a_montrer == "all") {
    $stmt = $db->prepare("SELECT e.nom, e.matricule, d1.devoir_type, d1.fichier, d1.date_envoi FROM etudiant e INNER JOIN devoir d1 ON e.id = d1.etudiant_id LEFT JOIN devoir d2 ON d1.etudiant_id = d2.etudiant_id AND d1.devoir_type = d2.devoir_type AND d1.date_envoi < d2.date_envoi WHERE d2.etudiant_id IS NULL ORDER BY d1.date_envoi DESC;");
    $stmt->execute();
}
else {
    $stmt = $db->prepare("SELECT e.nom, e.matricule, d1.devoir_type, d1.fichier, d1.date_envoi FROM etudiant e INNER JOIN devoir d1 ON e.id = d1.etudiant_id LEFT JOIN devoir d2 ON d1.etudiant_id = d2.etudiant_id AND d1.devoir_type = d2.devoir_type AND d1.date_envoi < d2.date_envoi WHERE d2.etudiant_id IS NULL AND d1.devoir_type = ? ORDER BY d1.date_envoi DESC;");
    $stmt->execute(array($devoir_a_montrer));
}


?>

<?php require('partials/header.php') ?>

<div class="d-flex gap-2 align-items-center justify-content-end">
    <a href="index.php" class="btn text-primary">&larr; Retourner à l'Accueil</a>
    <form action="logout.php" method="post" class="my-2 d-flex align-items-center justify-content-end">
        <?=$_SESSION['USER']?>
        <button class="btn text-primary" type="submit">Se déconnecter</button>
    </form>
</div>

<form method="get" class="m-4">
    <div class="d-flex align-items-center gap-2 justify-content-end" >
        <label for="type-devoir">Filtrer</label>
        <div>
            <select class="form-select" name="type-devoir" id="type-devoir">
                <?php foreach($devoirs as $type => $devoir) : ?>
                    <option value="<?= $type ?>" <?= $devoir_a_montrer === $type ? "selected" : "" ?>><?= $devoir['nom'] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    
        <div>
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
    </div>
</form>


<h1 class="my-4 text-center">Liste des dévoirs disponnibles</h1>

<?php if($stmt->rowCount()): ?>
    <div class="table-responsive">
        <table class="table table-stripped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matricule</th>
                    <th>Nom et prénoms</th>
                    <th>Dévoir</th>
                    <th>Date</th>
                    <th>Liens</th>
                </tr>
            </thead>
    
            <tbody>
                <?php $i = 1; ?>
                <?php while($row = $stmt->fetch(0)): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= htmlentities($row['matricule']) ?></td>
                        <td><?= htmlentities($row['nom']) ?></td>
                        <td><?= htmlentities($row['devoir_type']) ?></td>
                        <td><?= htmlentities($row['date_envoi']) ?></td>
                        <td><a class="btn text-primary" target="_blank" href="<?= 'https://docs.google.com/viewer?url=' . $baseUrl . '/' . $upload_dir . '/' . $row['fichier'] ?>">Voir &rarr; </a></td>
                    </tr>
                    <?php $i++ ?>
                <?php endwhile ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="alert alert-primary" role="alert">
        Aucun devoir pour l'instant !
    </div>
<?php endif ?>

<?php require('partials/footer.php') ?>