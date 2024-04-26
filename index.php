<?php
require('config.php');
$titre = "Accueil";
$form_errors = [];
$errorOccured = false;

$devoirs = array(
    "all" => "Choisissez une option",
    "intrusion-windows" => "Test d'intrusion Windows",
    "intrusion-metasploitable" => "Test d'intrusion Metasploitable",
    "audit-web" => "Audit Site Web",
);

if(isset($_POST['nom']) && isset($_POST['type-devoir']) && isset($_POST['matricule']) && isset($_FILES['devoir']))
{
    require __DIR__ . DIRECTORY_SEPARATOR . 'dbconfig.php';


    $nom = trim($_POST['nom']);
    $matricule = join(explode(' ', trim($_POST['matricule'])));
    $type_devoir = trim($_POST['type-devoir']);
    $devoir = $_FILES['devoir'];    


    // Vérification des données
    if($nom == "")
    {
        $form_errors[] = "Le nom est obligatoire";
    }

    if($type_devoir == "" || $type_devoir == "all")
    {
        $form_errors[] = "Le type de devoir est obligatoire";
    }

    if($matricule == ""){
        $form_errors[] = "Le matricule est obligatoire";
    } 
    else if (!preg_match('/^\d+$/', $matricule)){
        $form_errors[] = "Le matricule ne doit contenir que des chiffres";
    } 
    else if(strlen($matricule) !== 10){
        $form_errors[] = "Le matricule doit compter 10 chiffres";
    } 

    if($devoir['size'] == 0)
    {
        $form_errors[] = "Le dévoir est obligatoire";
    } else if ($devoir['size'] >= 30 * 1024 * 1024)
    {
        $form_errors[] = "La taille du fichier est trop grande";
    }
    if($devoir['type'] !== "application/pdf")
    {
        $form_errors[] = "Le format du dévoir doit être en PDF";
    }
    

    $errorOccured = count($form_errors) !== 0;
    if(!$errorOccured)
    {
        try
        {
            // S'il a déjà rendu un devoir récupérer son id
            
            $checkStmt = $db->prepare("SELECT id FROM etudiant WHERE matricule=:matricule LIMIT 1");
            $result = $checkStmt->execute(array('matricule' => $matricule));
            
            if($checkStmt->rowCount())
            {
                $row = $checkStmt->fetch();
                $studId = $row["id"];
            }
            else
            {
                // Sinon, on enregistre ses infos et son dévoir
        
                $studStmt = $db->prepare("INSERT INTO etudiant(matricule, nom) VALUES(:matricule, :nom);");
                $studStmt->execute(array(
                    "matricule" => $matricule,
                    "nom" => $nom,
                ));
        
                $studId = $db->lastInsertId();
            }

            // Sauvegarder le fichier

            $fichier = $matricule . "_" . $type_devoir . "_" . time() . '.pdf';
            move_uploaded_file($devoir['tmp_name'], "./$upload_dir" . '/' . $fichier) ;
            // Enregistrer le devoir
            
            $devoirStmt = $db->prepare("INSERT INTO devoir(devoir_type, fichier, etudiant_id, date_envoi) VALUES(:devoir_type, :fichier, :etudiant_id, :date_envoi);");
            
            $date_envoi = date('Y-m-d H:i:s', $date_envoi);

            $devoirStmt->execute(array(
                "devoir_type" => $type_devoir,
                "fichier"=> $fichier,
                "etudiant_id" => $studId,
                "date_envoi" => $date_envoi,
            ));
            
            header("Location: success.php");
        }
        catch(Exception $e)
        {
            header('Location: error.php');
            exit(1);
        }
    }
}

?>

<?php require('partials/header.php') ?>

<div class="my-2 d-flex align-items-center justify-content-end">
    <a class="btn text-primary" href="admin.php">Voir dashboard &rarr;</a>
</div>

<h1 class="my-4">Je rends mon devoir en Cybersécurité</h1>

<?php if($errorOccured) : ?>

    <div class="alert alert-warning" role="alert">
        <p class="fw-bold">Veuillez corriger ces erreurs</p>
        <?php foreach ($form_errors as $error): ?>
            <li><?= $error ?></li>
        <?php endforeach ?>
    </div>
<?php endif ?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="nom" class="form-label">Nom complet</label>
        <input class="form-control" value="<?= $errorOccured ? $nom : "" ?>" id="matricule" type="text" name="nom">
    </div>
    
    <div class="mb-3">
        <label for="matricule" class="form-label">Matricule</label>
        <input class="form-control" value="<?= $errorOccured ? $matricule : "" ?>" id="matricule" type="text" name="matricule">
    </div>

    <div class="mb-3">
        <label for="matricule" class="form-label">Quel dévoir déposez-vous ?</label>
        <select name="type-devoir" id="type-devoir" class="form-select">
            <?php foreach($devoirs as $type => $devoir) : ?>
                <option value="<?= $type ?>" <?= $errorOccured && $type_devoir === $type ? "selected" : "" ?>><?= $devoir ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="mb-3">
        <input class="form-control" type="file" name="devoir" id="devoir">
    </div>

    <div class="mb-3">
        <button class="btn btn-primary" type="submit">Envoyer</button>
    </div>

</form>


<?php require('partials/footer.php') ?>