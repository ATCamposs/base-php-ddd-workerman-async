<?php

declare(strict_types=1);

namespace App\Users\Presentation;

use App\Users\Application\UserServices;
use App\Users\Domain\User;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\UserName;
use support\Request;
use support\Response;

class Authentication
{
    private UserServices $user_services;

    public function __construct()
    {
        $this->user_services = new UserServices();
    }

    public function login(Request $request): Response
    {
        $email = $request->input('email');
        $password = $request->input('password');
        if (
            !isset($email)
            || !isset($password)
            || empty(trim($email))
            || empty(trim($password))
        ) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $login_return = $this->user_services->login($email, $password);
        if ($login_return['status'] === 'success') {
            return json(200, $login_return);
        }
        return json(400, $login_return);
    }

    public function updateUserName(Request $request)
    {
        $user = $this->getUserFromSession($request);
        $user_name = $request->input('user_name');
        if (empty($user_name)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $new_user_name = $user->updateUserName($user_name);
        if ($new_user_name['status'] === 'success') {
            $session = $request->session();
            $session->delete('user');
            return json(201, $new_user_name);
        }
        return json(400, $new_user_name);
    }

    public function updateEmail(Request $request)
    {
        $user = $this->getUserFromSession($request);
        $email = $request->input('email');
        if (empty($email)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $new_email = $user->updateEmail($email);
        if ($new_email['status'] === 'success') {
            return json(201, $new_email);
        }
        return json(400, $new_email);
    }

    public function updatePassword(Request $request)
    {
        $user = $this->getUserFromSession($request);
        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');
        if (empty($password) || empty($confirm_password)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $new_password = $user->updatePassword($password, $confirm_password);
        if ($new_password['status'] === 'success') {
            return json(201, $new_password);
        }
        return json(400, $new_password);
    }

    public function register(Request $request): Response
    {
        $user_name = $request->input('user_name');
        $email = $request->input('email');
        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');
        if (
            !isset($user_name)
            || !isset($email)
            || !isset($password)
            || !isset($confirm_password)
            || empty(trim($user_name))
            || empty(trim($email))
            || empty(trim($password))
            || empty(trim($confirm_password))
        ) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $register_return = $this->user_services->register(
            $user_name,
            $email,
            $password,
            $confirm_password
        );
        if ($register_return['status'] === 'success') {
            return json(201, $register_return);
        }
        return json(400, $register_return);
    }

    public function activate(Request $request): Response
    {
        $activation_code = $request->get('activation_code', null);
        $activate_return = $this->user_services->activate($activation_code);
        if ($activate_return['status'] === 'success') {
            return json(200, $activate_return);
        }
        return json(400, $activate_return);
    }

    public function delete(Request $request): Response
    {
        $uuid = $request->input('uuid');
        $session = $request->session();
        $session_uuid = $session->get('user')['uuid'];
        if (!isset($uuid) || empty(trim($uuid))) {
            return json(400, [
                'status' => 'fail',
                'data' => ['uuid' => trans('Please fill in all required fields.')]
            ]);
        };
        if ($session_uuid != $uuid) {
            return json(400, [
                'status' => 'fail',
                'data' => ['uuid' => trans('Incorrect user id.')]
            ]);
        }
        $session->delete('user');
        $delete_return = $this->user_services->delete($uuid);
        if ($delete_return['status'] === 'success') {
            return json(201, $delete_return);
        }
        return json(400, $delete_return);
    }

    public function logOut(Request $request): Response
    {
        $session = $request->session();
        $session->flush();
        return json(200, [
            'status' => 'success',
            'data' => ['message' => trans('User successfully logged out.')]
        ]);
    }

    protected function getUserFromSession(Request $request): User
    {
        $session = $request->session();
        $session_user = $session->get('user');
        return new User(
            $session_user['uuid'],
            new UserName($session_user['user_name']),
            new Email($session_user['email']),
            (bool) $session_user['active'],
            (int) $session_user['access_level']
        );
    }
}
