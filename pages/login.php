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
        if (is_empty($input, 'email')) {
            $errors['email'] = "Please enter your email address.";
        } else if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Please enter a valid email address.";
        } else {
            $data['email'] = $input['email'];
        }

        if (is_empty($input, 'username')) {
            $errors['username'] = "Please enter your username.";
        } else if(!preg_match('/^[a-zA-Z][0-9a-zA-Z_]{2,23}[0-9a-zA-Z]$/', $input['username'])) {
            $errors['username'] = "Hint: Username must start with a letter (either uppercase or lowercase), followed by 2 to 23 characters comprising letters, numbers, or underscores. It should end with a letter or number.";
        } else {
            $data['username'] = $input['username'];
        }

        if (is_empty($_POST, 'password')) {
            $errors['password'] = "Please enter your password.";
        } else {
            $data['password'] = $input['password'];
        }

        return count($errors) == 0;
    }

    $users = new UserStorage();
    $auth = new Auth($users);

    if (count($_POST) > 0) {
        if ($is_valid) {
            /*A bejelentkezés során az email címmel, a felhasználónévvel és
            jelszóval tudjuk azonosítani magunkat.*/
            $auth_user = $auth->authenticate($data['email'], $data['username'], $data['password']);
            if (!$auth_user) {
                $errors['failed_login'] = "Invalid login credentials. Please check and try again.";
            } else {
            /*A bejelentkezés során előforduló hibákat az űrlap fölött jelezd!
            Sikeres belépés után kerüljünk a főoldalra!*/
                $auth->login($auth_user);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/login.css">
    <title>IKémon | Login</title>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand">IKémon > Login</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarNavAltMarkup1" aria-controls="navbarNavAltMarkup1" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"> 
                </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup1">
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" href="../index.php">Home</a>
                        <a class="nav-link active" aria-current="page" href="#">Login</a>
                        <a class="nav-link" href="register.php">Register</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <div id="login_bg_container">
        <main>
            <div id="login-form-wrap">
                <?php if (isset($errors['failed_login'])): ?>
                    <p id="failed_login"><?= $errors['failed_login'] ?></p>
                <?php endif; ?>
                <h2>Login</h2>
                <form id="login-form" action="login.php" method="post" novalidate> <!-- action="../index.php" -->
                    <p>
                    <input type="email" id="email" name="email" placeholder="Email Address" value="<?= $input['email'] ?? "" ?>" class="<?= (isset($input['email']) && isset($errors['email'])) ? "invalid" : "" ?>">
                    <br>
                    <?php if(isset($input['email']) && isset($errors['email'])): ?>
                        <span class="error"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                    </p>
                    <p>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?= $input['username'] ?? "" ?>" class="<?= (isset($input['username']) && isset($errors['username'])) ? "invalid" : "" ?>">
                    <br>
                    <?php if(isset($input['username']) && isset($errors['username'])): ?>
                        <span class="error"><?= $errors['username'] ?></span>
                    <?php endif; ?>
                    </p>
                    <p>
                    <input type="password" id="password" name="password" placeholder="Password" value="<?= $input['password'] ?? "" ?>" class="<?= (isset($input['password']) && isset($errors['password'])) ? "invalid" : "" ?>">
                    <br>
                    <?php if(isset($input['password']) && isset($errors['password'])): ?>
                        <span class="error"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                    </p>
                    <input type="submit" value="Sign In" formnovalidate></input>
                </form>
                <div id="create-account-wrap">
                    <p id="register">Not a member? <a href="register.php">Create Account</a><p>
                </div>
            </div>
        </main>
    </div>
    <footer>
        <p>IKémon | ELTE IK Webprogramozás</p>
    </footer>
</body>
</html>