<?php
namespace Services;

use Models\User;
use Repositories\UserRepository;

class UserService
{

    private $repository;

    function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function checkLogin($password, $emailAddress)
    {
        return $this->repository->checkLogin($password, $emailAddress);
    }

    public function hashPassword($password)
    {
        return $this->repository->hashPassword($password);
    }

    public function register(User $user)
    {
        return $this->repository->register($user);
    }

    public function create(User $user)
    {
        return $this->repository->create($user);
    }

    public function update(User $user)
    {
        return $this->repository->update($user);
    }

    public function getAll($limit, $offset)
    {
        return $this->repository->getAll($limit, $offset);
    }

    public function getOne($userId)
    {
        return $this->repository->getOne($userId);
    }

    public function delete($userId)
    {
        return $this->repository->delete($userId);
    }

    public function deleteTutorEntry($userId)
    {
        return $this->repository->deleteTutorEntry($userId);
    }

    public function getTotalUsersCount()
    {
        return $this->repository->getTotalUsersCount();
    }
}