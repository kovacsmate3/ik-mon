<!-- Hitelesítési oldalak -->

<?php
    session_start();

    include_once 'utils/Storage.php';
    include_once 'utils/Auth.php';

    $errors = [];
    $input = $_POST;

    $is_valid = validate($input, $errors, $data);

    function is_empty($input, $key) {
        return !(isset($input[$key]) && trim($input[$key]) !== '');
    }

    function validate($input, &$errors, &$data) {
        /*Regisztráció során meg kell adni felhasználónevet, az e-mail címet és a jelszót kétszer.
        Mindegyik kötelező, a felhasználónév legyen egyedi, az e-mail cím formátuma legyen helyes,
        a jelszavak pedig egyezzenek. Sikeres regisztráció esetén a felhasználó kapjon
        x mennyiségű pénzt (ezt az összeget ajánlott beleégetned a kódba,
        mert úgyis azt szeretnénk, hogy minden user ugyanannyi pénzt kapjon).*/
        if (is_empty($input, 'email')) {
            $errors['email'] = "Email is required.";
        } else if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Please enter a valid email address.";
        } else {
            $data['email'] = $input['email'];
        }

        if(is_empty($input, 'email2')) {
            $errors['email2'] = "Email addresses do not match.";
        } else {
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email2'] = "Please enter a valid email address.";
            }
            else if (!is_empty($input, 'email') && $input['email'] != $input['email2']) {
                $errors['email2'] = "Email addresses do not match.";
            } else {
                $data['email2'] = $input['password2'];
            }
        } 

        if (is_empty($input, 'username')) {
            $errors['username'] = "Please enter a valid username.";
        } else if(!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{2,23}[0-9a-zA-Z]$/', $input['username'])) {
            $errors['username'] = "Hint: Username must start with a letter (either uppercase or lowercase), followed by 2 to 23 characters comprising letters, numbers, or underscores. It should end with a letter or number.";
        } else {
            $data['username'] = $input['username'];
        }

        if (is_empty($input, 'password')) {
            $errors['password'] = "Please enter your password.";
        } else {
            $data['password'] = $input['password'];
        }

        if(is_empty($input, 'password2')) {
            $errors['password2'] = "Passwords do not match";
        } else {
            if (!is_empty($input, 'password') && $input['password'] != $input['password2']) {
                $errors['password2'] = "Passwords do not match";
            } else {
                $data['password2'] = $input['password2'];
            }
        } 

        if (count($errors) === 0) {
            $data['balance'] = 1000;
            $data['admin_permission'] = FALSE;
        }

        return count($errors) == 0;
    }

    $users = new UserStorage();
    $auth = new Auth($users);

    if (count($_POST) > 0) {
        if ($is_valid) {
            if ($auth->user_exists($data['username'])) {
                $errors['user_exists'] = "A user with this name already exists.";
            } else {
                $user_id = $auth->register($data);
                $actual_user = $users->findById($user_id);
                $auth->login($actual_user);
                header("Location: ../index.php");
                exit();
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
    <link rel="stylesheet" href="../styles/register.css">
    <title>IKémon | Register</title>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand">IKémon > Register</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" href="../index.php">Home</a>
                        <a class="nav-link" href="login.php">Login</a>
                        <a class="nav-link active" aria-current="page" href="#">Register</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
    <div id="register-form-wrap">
        <h2>Create Account</h2>
        <!-- Regisztrációs hiba esetén jelenjenek meg hibaüzenetek! Az űrlap legyen állapottartó!
        Sikeres regisztráció után a felhasználó kerüljön bejelentkezve a főoldalra. -->
        <form id="register-form" action="register.php" method="post" novalidate>
            <p>
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" placeholder="Enter email address" value="<?= $input['email'] ?? "" ?>" class="<?= (isset($input['email']) && isset($errors['email'])) ? "invalid" : "" ?>">
                <?php if(isset($input['email']) && isset($errors['email'])): ?>
                    <br>
                    <span class="error"><?= $errors['email'] ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="email2">Confirm Email*</label>
                <input type="email" id="email2" name="email2" placeholder="Enter email address again" value="<?= $input['email2'] ?? "" ?>" class="<?= (isset($input['email2']) && isset($errors['email2'])) ? "invalid" : "" ?>">
                <?php if(isset($input['email']) && !isset($input['email2']) || isset($input['email2']) && isset($errors['email2'])): ?>
                    <br>
                    <span class="error"><?= $errors['email2'] ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="username">Username*</label>
                <input type="text" id="username" name="username" placeholder="Enter username" value="<?= $input['username'] ?? "" ?>" class="<?= (isset($input['username']) && isset($errors['username'])) ? "invalid" : "" ?>">
                <?php if(isset($input['username']) && isset($errors['username'])): ?>
                    <br>
                    <span class="error"><?= $errors['username'] ?></span>
                <?php elseif(isset($errors['user_exists'])): ?>
                    <br>
                    <span class="error"><?= $errors['user_exists']; ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="password">Password*</label>
                <input type="password" id="password" name="password" placeholder="Enter password" value="<?= $input['password'] ?? "" ?>" class="<?= (isset($input['password']) && isset($errors['password'])) ? "invalid" : "" ?>">
                <?php if(isset($input['password']) && isset($errors['password'])): ?>
                    <br>
                    <span class="error"><?= $errors['password'] ?></span>
                <?php endif; ?>
            </p>
            <p>
                <label for="password2">Confirm Password*</label>
                <input type="password" id="password2" name="password2" placeholder="Enter password again" value="<?= $input['password2'] ?? "" ?>" class="<?= (isset($input['password2']) && isset($errors['password2'])) ? "invalid" : "" ?>">
                <?php if(isset($input['password2']) && isset($errors['password2'])): ?>
                    <br>
                    <span class="error"><?= $errors['password2'] ?></span>
                <?php endif; ?>
            </p>
            <input type="submit" value="Sign Up" formnovalidate></input>
        </form>
        <div id="login-wrap">
                <p id="login">Already a member? <a href="login.php">Log In</a><p>
            </div>
    </div>
    </main>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
</body>
</html>