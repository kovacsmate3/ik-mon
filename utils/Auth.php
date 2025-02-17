<?php

/**
 * Class for authenticating a user.
 */
class Auth {
    /**
     * Storage where users are stored.
     *
     * @var IStorage<array{"id", string, "username": string, "password": string, "roles": array<string>}>
     */
    private IStorage $user_storage;

    /**
     * Currently active user.
     *
     * @var ?array{"id", string, "username": string, "password": string, "roles": array<string>}
     */
    private ?array $user = null;

    /**
     * Constructor. If a session exists the user is loaded from the session.
     *
     * @param IStorage $userStorage storage instance containing users.
     */
    public function __construct(IStorage $userStorage) {
        $this->user_storage = $userStorage;

        if (isset($_SESSION["user"])) {
            if (isset($_SESSION["user"]["id"])) {
                if($this->user_storage->findById($_SESSION["user"]["id"])) {
                    $this->user = $_SESSION["user"];
                }
            }
        }
    }

    /**
     * Registers a user based on the data supplied.
     *
     * @param array{"username": string, "password": string} $data data of the user.
     * @return string id of the user.
     */
    public function register(array $data): string {
        $user = [
            "username" => $data["username"],
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_DEFAULT),
            "balance" => $data["balance"],
            "admin_permission" => $data["admin_permission"]
        ];
        return $this->user_storage->addUser($user);
    }

    /**
     * Returns whether the user of the given username exists.
     *
     * @param string $username user to check.
     * @return bool true if user exists false otherwise.
     */
    public function user_exists(string $username): bool {
        $users = $this->user_storage->findOne(["username" => $username]);
        return !is_null($users);
    }

    /**
     * Attempts to authenticate a user by using the supplied credentials.
     *
     * @param string $username name of the user.
     * @param string $password hashed password of the user.
     * @return mixed null upon failure,
     */
    public function authenticate(string $email, string $username, string $password): mixed {
        $users = $this->user_storage->findMany(function ($user) use ($email, $username, $password) {
            return $user["email"] === $email && $user["username"] === $username && password_verify($password, $user["password"]);
        });

        return count($users) === 1 ? array_shift($users) : null;
    }

    /**
     * Returns whether the current user is authenticated.
     *
     * @return bool true if the user is authenticated.
     */
    public function is_authenticated(): bool {
        return !is_null($this->user);
    }

    /**
     * Logs in the given user, setting session data.
     *
     * @param array{"id": string, "username": string, "password": string, "roles": array<string>} $user user to log in.
     * @return void
     */
    public function login(array $user): void {
        $this->user = $user;
        $_SESSION["user"] = $user;
    }

    /**
     * Logs the current user out, unsetting session data.
     *
     * @return void
     */
    public function logout(): void {
        $this->user = null;
        unset($_SESSION["user"]);
    }

    /**
     * Returns the current user.
     *
     * @return array{"id": string, "username": string, "password": string, "roles": array<string>}
     */
    public function authenticated_user(): mixed {
        return $this->user;
    }

    public function is_admin() {
        if (!$this->is_authenticated()) {
            return FALSE;
        }
            return $this->user["admin_permission"] === TRUE;
        }
}

?>
