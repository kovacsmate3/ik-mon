<!-- Kártya részletek -->

<?php
    session_start();
    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    $pokemons = new CardStorage();
    $users = new UserStorage();
    $auth = new Auth($users);
    $actual_id = $_GET['id'];
    $actual_pokemon = $pokemons->findById($actual_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ==== Bootstrap ==== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <!-- ==== Bootstrap+Javascript ==== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <!-- ==== CSS ==== -->
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/card_details.css">
    <title>IKémon | <?= $actual_pokemon['name']; ?></title>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand">IKémon > <?= $actual_pokemon['name']; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                    <!-- Innen is lehessen elérni a főoldalt, esetlegesen a többi menüelemet. -->
                    <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                    <?php if($auth->is_authenticated()): ?>
                        <?php if ($auth->is_admin()): ?>
                            <a class="nav-link" href="../pages/addcard.php">Create Card</a>
                        <?php endif; ?>
                            <a id="profile_container" class="nav-link" href="../pages/user_details.php">
                                <span class="profile_content">
                                    <?= ($users->findById($auth->authenticated_user()['id']))['username'] ." " ?>
                                    <img class="profile_details" src="../img/user.png" alt="Profile details">
                                </span>
                            </a>
                            <a class='nav-link' id="balance">
                                <?= ($users->findById($auth->authenticated_user()['id']))['balance'] ?><span class='icon'>💰</span>
                            </a>
                            <a id="logout_container" class="nav-link" href="logout.php">
                                <span>
                                    <img class="logout" src="../img/logout.png" alt="Logout">
                                </span>
                            </a>
                    <?php endif; ?>
                    <?php if(!$auth->is_authenticated()): ?>
                            <a class="nav-link" href="login.php">Login</a>
                            <a class="nav-link" href="register.php">Register</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <div id="content">
    <!-- A Pokémon kártya részletek oldalon jelenjen meg az adott kártyán szereplő szörny neve,
    a hozzá tartozó kép, a szörny tulajdonságai, a kártya leírása. -->
            <h1 id="detailedCard" class="<?= $actual_pokemon['type'] ?>"><?= $actual_pokemon['name'] ?></h1>
            <div id="details" class="row justify-content-center align-self-center text-center">
            <!-- Az oldalon egy jól látható mező (pl. a kép háttere, oldal háttere) színe változzon
            a kártyán szereplő szörny eleme szerint. Pl.: Fire esetén piros, Lightning esetén sárga, stb. -->
                <div class="image clr-<?= $actual_pokemon['type'] ?>">
                    <img src="<?= $actual_pokemon['image'] ?>" alt="<?= $actual_pokemon['name'] ?>'s image">
                </div>
                <div class="info">
                    <div class="description">
                    <?= $actual_pokemon['description'] ?> 
                    </div>
                    <span class="card-type"><span class="icon">🏷</span> Type: <span class="<?= $actual_pokemon['type'] ?>"><?= $actual_pokemon['type'] ?></span></span>
                    <div class="attributes">
                        <div class="card-hp"><span class="icon">❤</span> Health: <?= $actual_pokemon['hp'] ?></div>
                        <div class="card-attack"><span class="icon">⚔</span> Attack: <?= $actual_pokemon['attack'] ?></div>
                        <div class="card-defense"><span class="icon">🛡</span> Defense: <?= $actual_pokemon['defense'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
</body>
</html>