<?php
class ControllerCommonLogin extends Controller
{
    public function index()
    {
        if (is_logged()) {
            redirect(admin_url('common/dashboard'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $user = $this->db->fetch('SELECT * FROM users WHERE username = :username', array(
                'username' => $username,
            ));

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect(admin_url('common/dashboard'));
            } else {
                $error = 'Неверный логин или пароль';
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
