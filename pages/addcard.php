<?php
    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    session_start();

    $errors = [];
    $input = $_POST;

    $is_valid = validate($input, $errors, $data);

    function is_empty($input, $key) {
        return !(isset($input[$key]) && trim($input[$key]) !== '');
    }

    function validate($input, &$errors, &$data) {
        if (is_empty($input, 'name')) {
            $errors['name'] = "Name is required!";
        } else if (!is_string($input['name']) || is_numeric($input['name'])) {
            $errors['name'] = "Name of the Pokémon must be a string.";
        } else if (strlen($input['name']) < 3 || strlen($input['name']) > 12) {
            $errors['name'] = "Name of the Pokémon must be between 3 and 12 characters long!";
        } else {
            $data['name'] = $input['name'];
        }

        if (is_empty($input, 'type')) {
            $errors['type'] = "Type is required!";
        } else if (!in_array($input['type'], ["normal", "fire", "water", "electric", "grass", "ice", "fighting", "poison", "ground", "psychic", "bug", "rock", "ghost", "dark", "steel"])) {
            $errors['type'] = "Please select a valid type!";
        } else {
            $data['type'] = $input['type'];
        }

        if (is_empty($input, 'hp')) {
            $errors['hp'] = "HP is required!";
        } else if (!is_numeric($input['hp']) || !(filter_var($input['hp'], FILTER_VALIDATE_INT)) || $input['hp'] < 0) {
            $errors['hp'] = "HP must be a positive integer value!";
        } else {
            $data['hp'] = (int)$input['hp'];
        }

        if (is_empty($input, 'attack')) {
            $errors['attack'] = "Attacking statistics is required!";
        } else if (!is_numeric($input['attack']) || !(filter_var($input['attack'], FILTER_VALIDATE_INT)) || $input['attack'] < 0) {
            $errors['attack'] = "Attacking damage must be a positive integer value!";
        } else {
            $data['attack'] = (int)$input['attack'];
        }

        if (is_empty($input, 'defense')) {
            $errors['defense'] = "Defensive strength is required!";
        } else if (!is_numeric($input['defense']) || !(filter_var($input['defense'], FILTER_VALIDATE_INT)) || $input['defense'] < 0) {
            $errors['defense'] = "Defense must be a positive integer value!";
        } else {
            $data['defense'] = (int)$input['defense'];
        }

        if (is_empty($input, 'price')) {
            $errors['price'] = "Price is required!";
        } else if (!is_numeric($input['price']) || !(filter_var($input['price'], FILTER_VALIDATE_INT)) || $input['price'] < 0) {
            $errors['price'] = "Price must be a positive integer value!";
        } else {
            $data['price'] = (int)$input['price'];
        }

        if (!is_empty($input, 'description')) {
            if (!is_string(trim($input['description'])) || is_numeric($input['description'])) {
                $errors['description'] = "Description must be a short text (not only whitespace characters) written in string format!";
            } else {
                $data['description'] = $input['description'];
            }
        } else {
            $data['description'] = "";
        }

        if (!is_empty($input, 'image')) {
            if (!filter_var($input['image'], FILTER_VALIDATE_URL)) {
                $errors['image'] = "Invalid Image URL format!";
            } else {
                $data['image'] = $input['image'];
            }
        } else {
            $data['image'] = "";
        }

        return count($errors) == 0;
    }

    $users = new UserStorage();
    $pokemons = new CardStorage();
    $auth = new Auth($users);
    $success = FALSE;

    if ($auth->is_authenticated()) {
        if (!($auth->is_admin())) {
            $errors['access_denied'] = "Access denied. You need administrator privileges to complete this task.";
        } else {
            if (isset($_POST) && count($_POST) > 0) {
                if ($is_valid) {
                    if ($pokemons->findOne(['name' => $data['name']])) {
                        $errors['same_card'] = "Pokémon card already exists under the given name.";
                    } else {
                        $pokemons->add([
                            'name' => $data['name'],
                            'type' => $data['type'],
                            'hp' => $data['hp'],
                            'attack' => $data['attack'],
                            'defense' => $data['defense'],
                            'price' => $data['price'],
                            'description' => $data['description'],
                            'image' => $data['image'],
                            'owner_id' => ($users->getAdmin())['id'],
                        ]);
                        $success = TRUE;
                    }
                }
            }
        }
    } else {
        $errors['access_denied'] = "Admin login is required to access the addcard.php page, which is responsible for creating a new card.";
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
    <link rel="stylesheet" href="../styles/addcard.css">
    <title>IKémon | Add Pokémon Card</title>
</head>
<body class="<?php if(isset($errors['access_denied'])) { echo "erroredBody"; } ?>">
<?php if (isset($errors['access_denied'])): ?>
    <div class="erroredContent">
        <div class="container-fluid text-center">
            <h1 id="error_401"><b>403.</b></h1>
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
                <a class="navbar-brand">IKémon > Home</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                        <a class="nav-link active" aria-current="page" href="#">Create Card</a>
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
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <div id="new-card-create-wrap">
            <?php if ($success): ?>
                <p id="success">
                    New card successfully created!
                </p>
            <?php endif; ?>
            <h2>Create New Pokémon Card</h2>
            <form id="new-card-create-form" action="addcard.php" method="post" novalidate>
                <label for="name">Name*</label>
                <input type="text" id="name" name="name" value="<?= $input['name'] ?? "" ?>" class="<?= (isset($input['name']) && isset($errors['name'])) ? "invalid" : "" ?>">
                <?php if(isset($input['name']) && isset($errors['name'])): ?>
                    <span class="error"><?= $errors['name'] ?></span>
                <?php elseif (isset($errors['same_card'])): ?>
                    <span class="error"><?= $errors['same_card']; ?></span>
                <?php endif; ?>
                <br>
                <label for="type" class="type">Type*</label>
                <select name="type" id="type" class="type">
                    <option value="">Select a type</option> 
                    <option value="normal" <?php echo (isset($input['type']) && ($input['type'] == "normal")) ? "selected" : "" ?>>Normal</option>
                    <option value="fire" <?php echo (isset($input['type']) && ($input['type'] == "fire")) ? "selected" : "" ?>>Fire</option>
                    <option value="water" <?php echo (isset($input['type']) && ($input['type'] == "water")) ? "selected" : "" ?>>Water</option>
                    <option value="electric" <?php echo (isset($input['type']) && ($input['type'] == "electric")) ? "selected" : "" ?>>Electric</option>
                    <option value="grass" <?php echo (isset($input['type']) && ($input['type'] == "grass")) ? "selected" : "" ?>>Grass</option>
                    <option value="ice" <?php echo (isset($input['type']) && ($input['type'] == "ice")) ? "selected" : "" ?>>Ice</option>
                    <option value="fighting" <?php echo (isset($input['type']) && ($input['type'] == "fighting")) ? "selected" : "" ?>>Fighting</option>
                    <option value="poison" <?php echo (isset($input['type']) && ($input['type'] == "posion")) ? "selected" : "" ?>>Poison</option>
                    <option value="ground" <?php echo (isset($input['type']) && ($input['type'] == "ground")) ? "selected" : "" ?>>Ground</option>
                    <option value="psychic" <?php echo (isset($input['type']) && ($input['type'] == "psychic")) ? "selected" : "" ?>>Psychic</option>
                    <option value="bug" <?php echo (isset($input['type']) && ($input['type'] == "bug")) ? "selected" : "" ?>>Bug</option>
                    <option value="rock" <?php echo (isset($input['type']) && ($input['type'] == "rock")) ? "selected" : "" ?>>Rock</option>
                    <option value="ghost" <?php echo (isset($input['type']) && ($input['type'] == "ghost")) ? "selected" : "" ?>>Ghost</option>
                    <option value="dark" <?php echo (isset($input['type']) && ($input['type'] == "dark")) ? "selected" : "" ?>>Dark</option>
                    <option value="steel" <?php echo (isset($input['type']) && ($input['type'] == "steel")) ? "selected" : "" ?>>Steel</option>
                </select>
                <?php if(isset($input['type']) && isset($errors['type'])): ?>
                    <br>
                    <span class="error"><?= $errors['type'] ?></span>
                <?php endif; ?>
                <br>
                <label for="hp">Health Points*</label>
                <input type="number" id="hp" name="hp" value="<?= $input['hp'] ?? "" ?>" class="<?= (isset($input['hp']) && isset($errors['hp'])) ? "invalid" : "" ?>">
                <?php if(isset($input['hp']) && isset($errors['hp'])): ?>
                    <span class="error"><?= $errors['hp'] ?></span>
                <?php endif; ?>
                <br>
                <label for="attack">Attacking damage*</label>
                <input type="number" id="attack" name="attack" value="<?= $input['attack'] ?? "" ?>" class="<?= (isset($input['attack']) && isset($errors['attack'])) ? "invalid" : "" ?>">
                <?php if(isset($input['attack']) && isset($errors['attack'])): ?>
                    <span class="error"><?= $errors['attack'] ?></span>
                <?php endif; ?>
                <br>
                <label for="defense">Defensive strength*</label>
                <input type="number" id="defense" name="defense" value="<?= $input['defense'] ?? "" ?>" class="<?= (isset($input['defense']) && isset($errors['defense'])) ? "invalid" : "" ?>">
                <?php if(isset($input['defense']) && isset($errors['defense'])): ?>
                    <span class="error"><?= $errors['defense'] ?></span>
                <?php endif; ?>
                <br>
                <label for="price">Price*</label>
                <input type="number" id="price" name="price" value="<?= $input['price'] ?? "" ?>" class="<?= (isset($input['price']) && isset($errors['price'])) ? "invalid" : "" ?>">
                <?php if(isset($input['price']) && isset($errors['price'])): ?>
                    <span class="error"><?= $errors['price'] ?></span>
                <?php endif; ?>
                <br>
                <div class="form-outline">
                    <label class="form-label" for="description">Description (optional)</label>
                    <textarea class="form-control <?= (isset($input['description']) && isset($errors['description'])) ? "invalid" : "" ?>" id="description" name="description" aria-label="Card description" rows="4"><?php if(isset($input['description'])) { echo htmlentities ($input['description']); }?></textarea>
                </div>
                <?php if(isset($input['description']) && isset($errors['description'])): ?>
                    <span class="error"><?= $errors['description'] ?></span>
                <?php endif; ?>
                <br>
                <label for="image">Image URL (optional)</label>
                <input type="text" id="image" name="image" value="<?= $input['image'] ?? "" ?>" class="<?= (isset($input['image']) && isset($errors['image'])) ? "invalid" : "" ?>">
                <?php if(isset($input['image']) && isset($errors['image'])): ?>
                    <span class="error"><?= $errors['image'] ?></span>
                <?php endif; ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" formnovalidate>Create</button>
                </div>
            </form>
        </div>
    </main>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
<?php endif; ?>
</body>
</html>