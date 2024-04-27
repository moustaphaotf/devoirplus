<?php
session_start();
require('config.php');
$titre = "Accueil";
$form_errors = [];
$errorOccured = false;

if(isset($_POST['nom']) && isset($_POST['type-devoir']) && isset($_POST['matricule']) && isset($_FILES['devoir']))
{
    require __DIR__ . DIRECTORY_SEPARATOR . 'dbconfig.php';


    $nom = trim($_POST['nom']);
    $matricule = join(explode(' ', trim($_POST['matricule'])));
    $type_devoir = trim($_POST['type-devoir']);
    $devoir = $_FILES['devoir'];  

    $date_envoi = date('Y-m-d H:i:s');



    // Vérification des données
    if($nom == "")
    {
        $form_errors[] = "Le nom est obligatoire";
    }

    if($type_devoir == "" || !isset($devoirs[$type_devoir]) || $type_devoir == "all")
    {
        $form_errors[] = "Le type de devoir est obligatoire";
    } 
    
    if($date_envoi > $devoirs[$type_devoir]['deadline'])
    {
        $form_errors[] = "La délai de dépôt est dépassé pour ce devoir";
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

    if ($devoir['error'] !== UPLOAD_ERR_OK) 
    {
        switch($devoir['error'])
        {
            case UPLOAD_ERR_INI_SIZE:
                $form_errors[] = "La taille du fichier est trop grande";
                break;

            case UPLOAD_ERR_PARTIAL:
                $form_errors[] = "Le téléchargement du fichier est incomplet";
                break;

            case UPLOAD_ERR_NO_FILE:
                $form_errors[] = "Aucun fichier n'est fourni !";
                break;
            default:
                $form_errors[] = "Impossible d'uploader votre fichier !";
                break;
        }
    } else if($devoir['size'] == 0)
    {
        $form_errors[] = "Le dévoir est obligatoire";
    } else if($devoir['type'] !== "application/pdf")
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
            $devoirStmt->execute(array(
                "devoir_type" => $type_devoir,
                "fichier"=> $fichier,
                "etudiant_id" => $studId,
                "date_envoi" => $date_envoi,
            ));            
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
    <?php if(isset($_SESSION['USER'])): ?>
        <a class="btn text-primary" href="search.php">Vérifier un dévoir</a>
    <?php endif ?>
    <a class="btn text-primary" href="admin.php">Voir dashboard &rarr;</a>
</div>

<?php if(isset($_SESSION['USER'])): ?>
    <form action="search.php" method="get" class="d-flex gap-2 align-items-center justify-content-end">
        <label for="matricule">Matricule</label>
        <div>
            <input required placeholder="Ex: 2104XXXXXX" name="matricule" id="matricule" type="search" class="form-control">
        </div>
        <div>
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
    </form>
<?php endif ?>

<h1 class="my-4">Je rends mon devoir en Cybersécurité</h1>

<div class="alert alert-success">
    <p>Deadlines</p>

    <ul>
        <?php foreach($devoirs as $type => $devoir): ?>
            <?php if ($type === 'all') continue; ?>
            <li><?= $devoir['nom'] . ' : <b>' . date('d/m/Y à H:i:s', strtotime($devoir['deadline'])) . '</b>' ?></li>
        <?php endforeach ?>
    </ul>
</div>

<?php if($errorOccured) : ?>

    <div class="alert alert-warning" role="alert">
        <p class="fw-bold">Veuillez corriger ces erreurs</p>
        <?php foreach ($form_errors as $error): ?>
            <li><?= $error ?></li>
        <?php endforeach ?>
    </div>
<?php elseif(isset($matricule)) : ?>
    <div class="alert alert-success m-4">
        <h1>Félicitations !</h1>
        <p>Votre devoir a été enregistré avec succès</p>
        <p>Vous pouvez le consulter à partir de <a href='<?="https://docs.google.com/viewer?url=$baseUrl/$upload_dir/$fichier"?>'>ce lien</a></p>
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
        <?php $now = date('Y-m-d H:i:s');?>
        <label for="matricule" class="form-label">Quel dévoir déposez-vous ?</label>
        <select name="type-devoir" id="type-devoir" class="form-select">
            <?php foreach($devoirs as $type => $devoir) : ?>
                <option <?= $type !== 'all' && $now > $devoir['deadline'] ? "disabled" : "" ?> value="<?= $type ?>" <?= $errorOccured && $type_devoir === $type ? "selected" : "" ?>><?= $devoir['nom'] . ' - ' . $devoir['deadline'] ?></option>
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