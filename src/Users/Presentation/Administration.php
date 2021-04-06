<?php

declare(strict_types=1);

namespace App\Users\Presentation;

use App\Users\Application\UserServices;
use App\Users\Domain\Admin;
use App\Users\Domain\Exceptions\InvalidAccessLevel;
use App\Users\Domain\User;
use App\Users\Domain\ValueObjects\Email;
use App\Users\Domain\ValueObjects\UserName;
use support\Request;
use support\Response;

class Administration
{
    public function changeUserUserName(Request $request): Response
    {
        $admin = $this->getAdminFromSession($request);
        if (is_array($admin)) {
            return json(400, $admin);
        }
        $uuid = $request->input('uuid');
        $user_name = $request->input('user_name');
        if (empty($uuid) || empty($user_name)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $new_user_name = $admin->changeUserUserName($uuid, $user_name);
        if ($new_user_name['status'] === 'success') {
            return json(201, $new_user_name);
        }
        return json(400, $new_user_name);
    }

    public function changeUserEmail(Request $request): Response
    {
        $admin = $this->getAdminFromSession($request);
        if (is_array($admin)) {
            return $admin;
        }
        $uuid = $request->input('uuid');
        $email = $request->input('email');
        if (empty($uuid) || empty($email)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $new_email = $admin->changeUserEmail($uuid, $email);
        if ($new_email['status'] === 'success') {
            return json(201, $new_email);
        }
        return json(400, $new_email);
    }

    public function activateUser(Request $request): Response
    {
        $admin = $this->getAdminFromSession($request);
        if (is_array($admin)) {
            return $admin;
        }
        $uuid = $request->input('uuid');
        if (empty($uuid)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['uuid' => trans('Invalid User.')]
            ]);
        };
        $activation_return = $admin->activateUser($uuid);
        if ($activation_return['status'] === 'success') {
            return json(201, $activation_return);
        }
        return json(400, $activation_return);
    }

    public function changeUserAccessLevel(Request $request): Response
    {
        $admin = $this->getAdminFromSession($request);
        if (is_array($admin)) {
            return $admin;
        }
        $uuid = $request->input('uuid');
        $access_level = $request->input('access_level');
        if (empty($access_level) || empty($uuid)) {
            return json(400, [
                'status' => 'fail',
                'data' => ['message' => trans('Please fill in all required fields.')]
            ]);
        };
        $access_level_return = $admin->changeUserAccessLevel($uuid, (int) $access_level);
        if ($access_level_return['status'] === 'success') {
            return json(201, $access_level_return);
        }
        return json(400, $access_level_return);
    }

    /** @return Admin|array */
    protected function getAdminFromSession(Request $request)
    {
        $session = $request->session();
        $session_user = $session->get('user');
        try {
            $admin = new Admin(
                $session_user['uuid'],
                new UserName($session_user['user_name']),
                new Email($session_user['email']),
                (bool) $session_user['active'],
                (int) $session_user['access_level']
            );
        } catch (InvalidAccessLevel $error) {
            $admin = [
                'status' => 'fail',
                'data' => ['message' => $error->getMessage()]
            ];
        }
        return $admin;
    }
}
