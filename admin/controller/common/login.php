<?php
class ControllerCommonLogin extends Controller
{
    public function index()
    {
        if (is_logged()) {
            redirect(admin_url('common/dashboard'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $superAdminExists = $this->db->fetch('SELECT id FROM users WHERE is_super_admin = 1 LIMIT 1');

            if (!$superAdminExists) {
                if ($username === '' || $password === '') {
                    $error = 'Введите логин и пароль для создания администратора.';
                } else {
                    $existingUser = $this->db->fetch('SELECT * FROM users WHERE username = :username', array(
                        'username' => $username,
                    ));

                    if ($existingUser) {
                        if (password_verify($password, $existingUser['password'])) {
                            $this->db->query('UPDATE users SET is_super_admin = 1 WHERE id = :id', array(
                                'id' => $existingUser['id'],
                            ));

                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $existingUser['id'];
                            $_SESSION['username'] = $existingUser['username'];
                            redirect(admin_url('common/dashboard'));
                        } else {
                            $error = 'Неверный логин или пароль';
                        }
                    } else {
                        $this->db->query('INSERT INTO users (username, password, is_super_admin, created_at) VALUES (:username, :password, 1, NOW())', array(
                            'username' => $username,
                            'password' => password_hash($password, PASSWORD_DEFAULT),
                        ));

                        $userId = $this->db->lastInsertId();

                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['username'] = $username;
                        redirect(admin_url('common/dashboard'));
                    }
                }
            } else {
                $user = $this->db->fetch('SELECT * FROM users WHERE username = :username', array(
                    'username' => $username,
                ));

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    redirect(admin_url('common/dashboard'));
                } else {
                    $error = 'Неверный логин или пароль';
                }
            }
        }

        $this->renderSimple('common/login', array(
            'error' => isset($error) ? $error : null,
        ));
    }

    public function logout()
    {
        session_destroy();
        redirect(admin_url('common/login'));
    }
}
