<?php
$error = "";
$titre = "Recherche";
require ('config.php');

if (isset($_GET['matricule']))
{
    require "dbconfig.php";
    $matricule = $_GET['matricule'];

    $studentStmt = $db->prepare("SELECT id, nom FROM etudiant WHERE matricule=:matricule LIMIT 1");
    
    $studentStmt->execute(array(
        "matricule" => $matricule,
    ));

    if(!$studentStmt->rowCount())
    {
        $error = "Vous n'avez soumis aucun devoir encore !";
    } 
    else 
    {
        $student = $studentStmt->fetch();

        // Retrouver ses dévoirs

        $devoirStmt = $db->prepare("SELECT d1.devoir_type, d1.fichier, d1.date_envoi FROM devoir d1 LEFT JOIN devoir d2 ON d1.etudiant_id = d2.etudiant_id AND d1.devoir_type = d2.devoir_type AND d1.date_envoi < d2.date_envoi WHERE d2.etudiant_id IS NULL AND d1.etudiant_id=:etudiant_id ORDER BY d1.date_envoi DESC;");

        $devoirStmt->execute(array(
            "etudiant_id" => $student['id'],
        ));

        if (!$devoirStmt->rowCount()) $error = "Vous n'avez soumis aucun devoir encore !";
    }
}


?>

<?php require('partials/header.php') ?>

<div class="my-2 d-flex align-items-center justify-content-end">
    <a class="btn text-primary" href="index.php">&larr; Retourner à l'accueil</a>
    <a class="btn text-primary" href="admin.php">Voir dashboard &rarr;</a>
</div>

<h1>Je cherche mes dévoirs</h1>

<form method="get">
    <div class="mb-3">
        <label class="form-label" for="matricule">Matricule</label>
        <input placeholder="Ex: 2104XXXXXX" name="matricule" id="matricule" type="search" class="form-control">
    </div>
    <div class="mb-3">
        <button class="btn btn-primary" type="submit">Rechercher</button>
    </div>
</form>

<?php if (isset($matricule)): ?>
    <?php if($error !== ""): ?>
        <div class="alert alert-warning">
            <?=$error?>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <p class="fw-bold m-0"><?=$student['nom'] . "(" . htmlentities($matricule) . ")"?></p>
        </div>
        
        <table class="table table-stripped table-hover table-responsive">
            <thead>
                <th>#</th>
                <th>Dévoir</th>
                <th>Date d'Envoi</th>
                <th>Lien</th>
            </thead>
            <tbody>
                <?php $i = 1 ?>
                <?php while($devoir = $devoirStmt->fetch()): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= $devoir['devoir_type'] ?></td>
                        <td><?= $devoir['date_envoi'] ?></td>
                        <td><a class="btn text-primary" target="_blank" href="https://docs.google.com/viewer?url=<?= $baseUrl . "/" . $upload_dir . "/" . $devoir['fichier'] ?>">Voir &rarr;</td>
                    </tr>
    
                    <?php $i++ ?>
                <?php endwhile ?>
            </tbody>
        </table>
    <?php endif ?>
<?php endif ?>






<?php require('partials/footer.php') ?>