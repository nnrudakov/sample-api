<?php

declare(strict_types=1);

namespace Page;

/**
 * Class Users
 *
 * @package    Page
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Users extends Page
{
    protected string $page = 'users';

    public function passwordResetRequest(array $params = []): void
    {
        $this->I->sendPOST(static::route("{$this->page}/password-reset-request"), $params);
    }

    public function passwordReset(array $params = []): void
    {
        $this->I->sendPOST(static::route("{$this->page}/password-reset"), $params);
    }

    public function login(array $params = []): void
    {
        $this->I->sendPOST(static::route("{$this->page}/login"), $params);
    }

    public function logout(): void
    {
        $this->I->sendPOST(static::route("{$this->page}/logout"));
    }

    public function view(int $id, array $params = []): void
    {
        $this->I->sendGET(static::route("{$this->page}/$id"), $params);
    }

    public function update(int $id, array $params = []): void
    {
        $this->I->sendPATCH(static::route("{$this->page}/$id"), $params);
    }

    public function create(array $params): void
    {
        $this->I->sendPOST(static::route($this->page), $params);
    }

    public function list(array $params): void
    {
        $this->I->sendGET(static::route($this->page), $params);
    }
}
