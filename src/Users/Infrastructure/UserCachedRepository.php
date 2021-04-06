<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Users\Domain\UserRepositoryInterface;
use support\bootstrap\Redis;

class UserCachedRepository
{
    private Redis $cacheLayer;
    private UserRepositoryInterface $user_repository;

    public function __construct()
    {
        $this->cacheLayer = new Redis();
        $this->user_repository = new UserRepositoryIlluminate();
    }

    public function getModifiedUserList(): array
    {
        $modified_user_list = $this->cacheLayer::get('modified_user_list') ?? '';
        return (array) json_decode($modified_user_list);
    }

    public function addUserToModifiedUserList(string $uuid): void
    {
        $actual_modified_list = $this->getModifiedUserList();
        if (empty($actual_modified_list)) {
            $this->cacheLayer::set('modified_user_list', json_encode($uuid));
        }
        if (!in_array($uuid, $actual_modified_list)) {
            array_push($actual_modified_list, $uuid);
            $this->cacheLayer::set('modified_user_list', json_encode($actual_modified_list));
        }
    }

    public function removeUserFromModifiedUserList(string $uuid): void
    {
        $actual_modified_list = $this->getModifiedUserList();
        $new_modified_list = array_diff($actual_modified_list, [$uuid]);
        $this->cacheLayer::set('modified_user_list', json_encode($new_modified_list));
    }

    public function getUserlByUUID(string $uuid): ?object
    {
        $actual_modified_list = $this->getModifiedUserList();
        if (!empty($actual_modified_list) && in_array($uuid, $actual_modified_list)) {
            $this->removeUserFromModifiedUserList($uuid);
            $this->cacheLayer::del($uuid);
        }
        $user = $this->cacheLayer::get($uuid);
        if (!empty($user)) {
            return json_decode($user);
        }
        $user =  $this->user_repository->getUserlByUUID($uuid);
        if (empty($user)) {
            return null;
        }
        $this->cacheLayer::set($user->uuid, json_encode($user), 'EX', 1 * 60);
        return $user;
    }
}
