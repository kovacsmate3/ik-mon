<!-- Főoldal / Listaoldal -->

<?php
    session_start();
    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    $pokemons = new CardStorage();
    $users = new UserStorage();
    $auth = new Auth($users);
    $errors = [];
    $data = [];


    function validate(&$errors, &$data, $users, $pokemons, $auth) {
        if (isset($_GET['boughtcard_id'])) {
            if (!($auth->is_authenticated())) {
                $errors['global'] = "Sign in to purchase a card!";
            } else {
                if (!($pokemons->findById($_GET['boughtcard_id']))) {
                    $errors['global'] = "The card with this identifier does not exist!";
                } else {
                    if ($auth->is_admin()) {
                        $errors['global'] = "User with admin permission cannot purchase a card!";
                    } else {
                        $user = $users->findById(($auth->authenticated_user())['id']);
                        $pokemon = $pokemons->findById($_GET['boughtcard_id']);
                        $numberOfCardsOwnedByUser = $pokemons->getNumberOfCardsOwnedByUser($user['id']);
                        $admin_id = ($users->getAdmin())['id'];
                        if ($pokemon['owner_id'] !== $admin_id) {
                            $errors['global'] = "The card has already been purchased!";
                        } else {
                            if ($user['balance'] < $pokemon['price']) {
                                $errors['global'] = "You don't have enough balance to make the purchase!";
                            } else {
                                if ($numberOfCardsOwnedByUser >= 5) {
                                    $errors['global'] = "You have reached the maximum limit of owned cards!";
                                } else {
                                    $data['boughtcard_id'] = $_GET['boughtcard_id'];
                                }
                            }
                        }
                    }  
                }
            } 
        }

        if (isset($_GET['random_card_buy'])) {
            if ($_GET['random_card_buy'] === "true") {
                $randomCard_id = $pokemons->getRandomPurchasableCard($users);
                
                if (isset($randomCard_id)) {
                    if ($auth->is_admin()) {
                        $errors['global'] = "User with admin permission cannot purchase a card!";
                    } else {
                        $user = $users->findById(($auth->authenticated_user())['id']);
                        $numberOfCardsOwnedByUser = $pokemons->getNumberOfCardsOwnedByUser($user['id']);
                        
                        if ($user['balance'] < 50) {
                            $errors['global'] = "You don't have enough balance to purchase a random card!";
                        } else {
                            if ($numberOfCardsOwnedByUser >= 5) {
                                $errors['global'] = "You have reached the maximum limit of owned cards!";
                            } else {
                                $data['random_card_buy'] = $randomCard_id;
                            }
                        }
                    }
                } else {
                    $errors['global'] = "There are no available cards for purchase!";
                }
            } else {
                $errors['global'] = "Invalid parameters for purchasing a random card!";
            }
        }
    
        return count($errors) === 0;
    }

    $is_valid = validate($errors, $data, $users, $pokemons, $auth);

    $filter_card = "";

    if (!(isset($_POST['filter']))) {
        $filter_card = "all";
    } else {
        $filter_card = $_POST['filter'];
    }

    if (count($_GET) > 0) {
        if ($is_valid) {
            if (isset($data['logout'])) {
                $auth->logout();
            }

            if (isset($data['boughtcard_id'])) {
                $newCard = $pokemons->findById($data['boughtcard_id']);
                $newOwner = $users->findById(($auth->authenticated_user())['id']);
                $numberOfCardsOwnedByNewOwner = $pokemons->getNumberOfCardsOwnedByUser($newOwner['id']);
                if ($numberOfCardsOwnedByNewOwner < 5 && $newOwner['balance'] >= $newCard['price']) {
                    $newCard['owner_id'] = $newOwner['id'];
                    $pokemons->update($newCard['id'], $newCard);
                    $newOwner['balance'] = $newOwner['balance'] - $newCard['price'];
                    $users->update($newOwner['id'], $newOwner);
                }
            }

            if (isset($data['random_card_buy'])) {
                $newCard = $pokemons->findById($data['random_card_buy']);
                $newOwner = $users->findById(($auth->authenticated_user())['id']);
                $numberOfCardsOwnedByNewOwner = $pokemons->getNumberOfCardsOwnedByUser($newOwner['id']);
                if ($numberOfCardsOwnedByNewOwner < 5 && $newOwner['balance'] >= 50) {
                    $newCard['owner_id'] = $newOwner['id'];
                    $pokemons->update($newCard['id'], $newCard);
                    $newOwner['balance'] = $newOwner['balance'] - 50;
                    $users->update($newOwner['id'], $newOwner);
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
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/cards.css">
    <title>IKémon | Home</title>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand">IKémon > Home</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                        <?php if ($auth->is_authenticated()): ?>
                            <?php if ($auth->is_admin()): ?>
                                <a class="nav-link" href="./pages/addcard.php">Create Card</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($auth->is_authenticated()): ?>
                            <a id="profile_container" class="nav-link" href="./pages/user_details.php">
                                <span class="profile_content">
                                    <?= ($users->findById($auth->authenticated_user()['id']))['username'] ." " ?>
                                    <img class="profile_details" src="./img/user.png" alt="Profile details">
                                </span>
                            </a>
                            <a class='nav-link' id="balance">
                                <?= ($users->findById($auth->authenticated_user()['id']))['balance'] ?><span class='icon'>💰</span>
                            </a>
                            <a id="logout_container" class="nav-link" href="./pages/logout.php">
                                <span>
                                    <img class="logout" src="./img/logout.png" alt="Logout">
                                </span>
                            </a>
                        <?php else: ?>
                            <!-- A főoldalról legyen lehetőség elérni a bejelentkező és regisztrációs oldalt. -->
                            <a class="nav-link" href="./pages/login.php">Login</a>
                            <a class="nav-link" href="./pages/register.php">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
    <!-- A lista oldalon, avagy a főoldalon statikus szöveggel jelenjen meg egy cím és egy rövid ismertetés. -->
    <div id="introduction"><p><b>Welcome to IKémon</b> - the ultimate online destination where Pokémon cards meet the world of Non-Fungible Tokens (NFTs)<b>!</b><br> Whether you're a seasoned collector or a casual Pokémon fan, <b>IKémon offers an accessible platform for NFT vendors to sell and for users to buy and trade Pokémon cards.</b> Dive into a nostalgic yet innovative experience, where every card is a unique digital asset, secured by blockchain technology.<br> <b>Join our community, explore the catalog, and embrace the fusion of Pokémon nostalgia and cutting-edge technology at IKémon!</b><p>
    </div>
    <h2 id="pokemons">Pokémon cards</h2>
    <div id="content">
        <div id="filter_and_random_div" class="container-fluid">
            <div class="row">
                <div class="col-8 d-flex justify-content-center">
                <form action="index.php" method="post" id="card_filter_form">
                    <label id="filter_label" for="filter">Filter by type: </label>
                    <select id="filter" class="form-select" name="filter">
                        <option value="all" <?= $filter_card === "all" ? " selected" : "" ?>>All</option>
                        <option value="normal" <?= $filter_card === "normal" ? " selected" : "" ?>>Normal</option>
                        <option value="fire" <?= $filter_card === "fire" ? " selected" : "" ?>>Fire</option>
                        <option value="water" <?= $filter_card === "water" ? " selected" : "" ?>>Water</option>
                        <option value="electric" <?= $filter_card === "electric" ? " selected" : "" ?>>Electric</option>
                        <option value="grass" <?= $filter_card === "grass" ? " selected" : "" ?>>Grass</option>
                        <option value="ice" <?= $filter_card === "ice" ? " selected" : "" ?>>Ice</option>
                        <option value="fighting" <?= $filter_card === "fighting" ? " selected" : "" ?>>Fighting</option>
                        <option value="poison" <?= $filter_card === "poison" ? " selected" : "" ?>>Poison</option>
                        <option value="ground" <?= $filter_card === "ground" ? " selected" : "" ?>>Ground</option>
                        <option value="psychic" <?= $filter_card === "psychic" ? " selected" : "" ?>>Psychic</option>
                        <option value="bug" <?= $filter_card === "bug" ? " selected" : "" ?>>Bug</option>
                        <option value="rock" <?= $filter_card === "rock" ? " selected" : "" ?>>Rock</option>
                        <option value="ghost" <?= $filter_card === "ghost" ? " selected" : "" ?>>Ghost</option>
                        <option value="dark" <?= $filter_card === "dark" ? " selected" : "" ?>>Dark</option>
                        <option value="steel" <?= $filter_card === "steel" ? " selected" : "" ?>>Steel</option>
                    </select>
                    <input id="filter_cards" type="submit" value="Filter">
                </form>
                </div>
                <div class="col-4 d-flex justify-content-center">
                <?php if ($auth->is_authenticated()) : ?>
                    <?php if (!($auth->is_admin())) : ?>
                        <div id="random_card">
                            <a class="btn btn-primary" id="randomcardbutton" href="index.php?random_card_buy=true">
                            Random Card (50💰)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (isset($errors['global'])): ?>
            <p class="error"><?= $errors['global']; ?></p>
        <?php endif; ?>
        <div id="card-list">
            <?php 
                if ($filter_card === "all") {
                    $cards = $pokemons->getAllCards();
                } else {
                    $cards = $pokemons->getCardsByType($filter_card);
                }
            ?>
            <!-- A lista oldalon legyenek kilistázva a rendszerben létező Pokémon kártyák. -->
            <?php foreach($cards as $card):?>
                <div class="pokemon-card">
                    <div class="image clr-<?= $card['type'] ?>">
                        <img src="<?= $card['image'] ?>" alt="<?= $card['name'] ?>'s image">
                    </div>
                    <div class="details">
                        <!-- A Pokémon kártyákhoz tartozzon egy link (ez akár egy képen is rajta lehet), ami az adott Pokémon kártya részletező oldalára vezet. -->
                        <h2><a href="pages/card_details.php?id=<?= $card['id'] ?>"><?= $card['name'] ?></a></h2>
                        <span class="card-type"><span class="icon">🏷</span> <?= $card['type'] ?></span>
                        <span class="attributes">
                            <span class="card-hp"><span class="icon">❤</span> <?= $card['hp'] ?></span>
                            <span class="card-attack"><span class="icon">⚔</span> <?= $card['attack'] ?></span>
                            <span class="card-defense"><span class="icon">🛡</span> <?= $card['defense'] ?></span>
                        </span>
                    </div>
                    <?php
                        $owner = $users->findById($card['owner_id']);
                        $cardOwnerIsAdmin = $owner['admin_permission'];
                    ?>
                    <?php if ($auth->is_authenticated()): ?>
                        <?php if (!($auth->is_admin()) && $cardOwnerIsAdmin): ?>
                            <?php
                                $user = $users->findById(($auth->authenticated_user())['id']);
                                $numberOfCardsOwnedByUser = $pokemons->getNumberOfCardsOwnedByUser($user['id']);
                            ?>
                            <?php if (($user['balance'] < $card['price']) || ($numberOfCardsOwnedByUser >= 5)): ?>
                                <div class="balance_displayonly">
                                    <span class='card-price'>Buy: <?= $card['price'] ?><span class="icon">💰</span></span>
                                </div>
                            <?php else: ?>
                                <a class="cardbuying" href="index.php?boughtcard_id=<?= $card['id'] ?>">
                                    <div class="buy">
                                        <span class="card-price">Buy: <?= $card['price'] ?><span class="icon">💰</span></span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php elseif (!($auth->is_admin()) && !($cardOwnerIsAdmin)): ?>
                            <?php if (($auth->authenticated_user())['id'] === $owner['id']): ?>
                                <div class="unavailable">
                                    <span class="card-price">Purchased</span>
                                </div>
                            <?php else: ?>
                                <div class="unavailable">
                                    <span class="card-price">Out of stock</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($cardOwnerIsAdmin): ?>
                                <div class="balance_displayonly">
                                    <span class="card-price"><?= $card['price'] ?><span class="icon">💰</span></span>
                                </div>
                            <?php else: ?>
                                <div class="unavailable">
                                    <span class="card-price">Out of stock</span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($cardOwnerIsAdmin): ?>
                            <div class="balance_displayonly">
                                <span class="card-price"><?= $card['price'] ?><span class="icon">💰</span></span>
                            </div>
                        <?php else: ?>
                            <div class="unavailable">
                                <span class="card-price">Out of stock</span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
</body>

</html>