<!-- Felhasználó részletek -->

<?php
    session_start();
    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    $errors = [];
    $data = [];

    function validate(&$errors, &$data, $users, $pokemons, $auth) {
        if (isset($_GET['soldcard_id'])) {
            if ($auth->is_admin()) {
                $errors['global'] = "User with admin permission cannot sell a card!";
            } elseif (!($pokemons->findById($_GET['soldcard_id']))) {
                $errors['global'] = "The card with this identifier does not exist!";
            } else {
                $data['soldcard_id'] = $_GET['soldcard_id'];
            }
        }

        return count($errors) === 0;
    }

    $pokemons = new CardStorage();
    $users = new UserStorage();

    $auth = new Auth($users);

    $is_valid = validate($errors, $data, $users, $pokemons, $auth);

    if (!$auth->is_authenticated()) {
        $errors['access_denied'] = "User page is only accessible when logged in!";
    } else {
        $data['actual_user'] = $users->findById(($auth->authenticated_user())['id']);
        if (count($_GET) > 0) {
            if ($is_valid) {
                if (isset($data['soldcard_id'])) {
                    $newCard = $pokemons->findById($data['soldcard_id']);
                    $oldOwner = $users->findById($newCard['owner_id']);
                    $newOwner = $users->getAdmin();
                    $sellingPrice = (int)floor($newCard['price'] * 0.9);
            
                    $newCard['owner_id'] = $newOwner['id'];
                    $pokemons->update($newCard['id'], $newCard);
                    $oldOwner['balance'] = $oldOwner['balance'] + $sellingPrice;
                    $users->update($oldOwner['id'], $oldOwner);
                }
            }
        }
    }
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
    <link rel="stylesheet" href="../styles/cards.css">
    <link rel="stylesheet" href="../styles/user_details.css">
    <title>IKémon | <?= isset($errors['access_denied']) ? "User details" : $data['actual_user']['username']; ?></title>
</head>
<body class="<?php if(isset($errors['access_denied'])) { echo "erroredBody"; } ?>">
<?php if (isset($errors['access_denied'])): ?>
    <div class="erroredContent">
        <div class="container-fluid text-center">
            <h1 id="error_401"><b>401.</b></h1>
            <span><h1 id="errorMessage">That's an error.</h1></span>
        </div>
        <h2 id="pageInaccessible">Page is not accessible</h2>
        <p class="error_reason"><?= $errors['access_denied']; ?></p>
        <p class="backToMainPage"><a href="../index.php">Back to main page</a></p>
    </div>
<?php else: ?>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand">IKémon > <?= $data['actual_user']['username']; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                    <!-- Innen is lehessen elérni a főoldalt, esetlegesen a többi menüelemet. -->
                        <a class="nav-link" href="../index.php">Home</a>
                        <?php if($auth->is_authenticated()): ?>
                        <?php if ($auth->is_admin()): ?>
                            <a class="nav-link" href="../pages/addcard.php">Create Card</a>
                        <?php endif; ?>
                            <a id="profile_container" class="nav-link active" aria-current="page" href="#">
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
        <div id="user_details" class="row">
            <div class="col-xl-4">
                <div class="bordered card mb-4">
                    <div class="card-body text-center">
                        <img src="../img/profile_picture.png" alt="avatar" id="profile"
                        class="rounded-circle img-fluid">
                        <h5 class="my-3"><b><?= $data['actual_user']['username']; ?></b></h5>
                        <p class="text-muted mb-1">
                            <?= $data['actual_user']['admin_permission']  == FALSE ? "IKémon member" : "IKémon administrator" ?>
                        </p>
                    </div>
                </div>
            </div>
            <div id="profile_data" class="col-xl-8">
                <h2 id="information">Information</h2>
                <div class="bordered card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3">
                                <p class="data mb-0">Username</p>
                            </div>
                            <div class="col-lg-9">
                                <p class="text-muted mb-0"><?= $data['actual_user']['username'] ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-3">
                                <p class="data mb-0">Email</p>
                            </div>
                            <div class="col-lg-9">
                                <p class="text-muted mb-0"><?= $data['actual_user']['email'] ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-3">
                                <p class="data mb-0">Balance</p>
                            </div>
                            <div class="col-lg-9">
                                <p class="text-muted mb-0"><?= ($users->findById(($auth->authenticated_user())['id']))['balance'] ?><span class="icon">💰</span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-3">
                                <p class="data mb-0">Permissions</p>
                            </div>
                            <div class="col-lg-9">
                                <ul id="permissions">
                                <?php if ($data['actual_user']['admin_permission'] === TRUE): ?>
                                    <li>New card creation</li>
                                    <li>Card modification</li>
                                <?php else: ?>
                                    <li>Card purchase</li>
                                    <li>Sell previously purchased card (for 90% of its original price)</li>
                                    <li>Ask for card trading</li>
                                <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h2>Owned cards</h2>
            <hr id="owned_cards">
            <div id="card-list">
            <?php $ownCards = $pokemons->getCardsByOwner($data['actual_user']['id']); ?>
            <?php if ($data['actual_user']['admin_permission'] === TRUE): ?>
                <?php foreach ($ownCards as $card): ?>
                    <div class="pokemon-card">
                        <div class="image clr-<?= $card['type'] ?>">
                            <img src="<?= $card['image']; ?>" alt="<?= $card['name']; ?>'s image">
                        </div>
                        <div class="details">
                            <h2><a href="details.php?id=<?= $card['id'] ?>"><?= $card['name']; ?></a></h2>
                            <span class="card-type"><span class="icon">🏷</span> <?= $card['type']; ?></span>
                            <span class="attributes">
                                <span class="card-hp"><span class="icon">❤</span> <?= $card['hp']; ?></span>
                                <span class="card-attack"><span class="icon">⚔</span> <?= $card['attack']; ?></span>
                                <span class="card-defense"><span class="icon">🛡</span> <?= $card['defense']; ?></span>
                            </span>
                        </div>
                        <div class="balance_displayonly">
                            <span class="card-price"><span class="icon">💰</span> <?= $card['price']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($ownCards as $card): ?>
                    <div class="pokemon-card">
                        <div class="image clr-<?= $card['type']; ?>">
                            <img src="<?= $card['image']; ?>" alt="<?= $card['name']; ?>'s image">
                        </div>
                        <div class="details">
                            <h2><a href="details.php?id=<?= $card['id']; ?>"><?= $card['name']; ?></a></h2>
                            <span class="card-type"><span class="icon">🏷</span> <?= $card['type']; ?></span>
                            <span class="attributes">
                                <span class="card-hp"><span class="icon">❤</span> <?= $card['hp']; ?></span>
                                <span class="card-attack"><span class="icon">⚔</span> <?= $card['attack']; ?></span>
                                <span class="card-defense"><span class="icon">🛡</span> <?= $card['defense']; ?></span>
                            </span>
                        </div>
                        <a class="cardbuying" href="user_details.php?soldcard_id=<?= $card['id']; ?>">
                            <div class="buy">
                                <span class="card-price">Sell: <span class="icon">💰</span> <?= (int)floor($card['price'] * 0.9); ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        </div>
    </main>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
<?php endif; ?>
</body>
</html>