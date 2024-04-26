<?php
session_start();

$titre = "Authentification";

if(isset($_SESSION['USER']))
{
    header("Location: admin.php");
    exit();
}

$notfound = 0;


if(isset($_POST['username']) && isset($_POST['password']))
{
    require __DIR__ . DIRECTORY_SEPARATOR . 'dbconfig.php';
    
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    try{
        $stmt = $db->prepare("SELECT * FROM admin WHERE BINARY username=:username AND password=:password LIMIT 1;");
        $stmt->execute(array("username" => $username, "password" => $password));
        
        if (!$stmt->rowCount())
        {
            $notfound = 1;
        }
        else
        {
            $user = $stmt->fetch();
            $_SESSION['USER'] = $user['username'];
            header('Location: admin.php');
        }

    }
    catch(Exception $e)
    {
        header('Location: error.php');
        exit(1);
    }

}

?>

<?php require('partials/header.php') ?>

<h1 class="m4-2">Accédez au dashboard</h1>

<?php if($notfound): ?>
    <div class="my-4 alert alert-warning">
        <p>Nom d'utilisateur ou mot de passe incorrect</p>
    </div>
<?php endif ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label" for="username">Email ou nom d'utilisateur</label>
        <input class="form-control" id="username" type="text" name="username" value="<?= $notfound ? $username : ''?>">
    </div>
    
    <div class="mb-3">
        <label class="form-label" for="password">Mot de passe</label>
        <input class="form-control" id="password" type="text" name="password">
    </div>

    <div class="mb-3">
        <button class="btn btn-primary" type="submit">Se connecter</button>
    </div>
</form>

<?php require('partials/footer.php') ?>