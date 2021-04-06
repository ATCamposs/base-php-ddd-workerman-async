<?php

namespace support\middleware;

use App\Users\Infrastructure\JwtSecurityHandler;
use App\Users\Infrastructure\UserCachedRepository;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class CheckLogin implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        $session = $request->session();
        $user = $session->get('user');
        $modified_users = (new UserCachedRepository())->getModifiedUserList();
        if (isset($user['uuid']) && (in_array($user['uuid'], $modified_users))) {
            $session->delete('user');
            $user = null;
        }
        if ($user) {
            return $next($request);
        }
        $token = explode(" ", trim($request->header('authorization')))[1];
        if (!isset($token) && empty(trim($token))) {
            return json(401, [
                'status' => 'fail',
                'message' => trans('You need to login to view this content.')
            ]);
        }
        $auth_user = (new JwtSecurityHandler())->checkAuthorization($token);
        if ($auth_user['status'] === 'fail') {
            return json(401, $auth_user);
        }
        $session->set('user', (array) $auth_user['data']['user']);
        return $next($request);
    }
}
