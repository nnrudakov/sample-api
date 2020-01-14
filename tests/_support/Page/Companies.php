<?php

declare(strict_types=1);

namespace Page;

/**
 * Class Companies
 *
 * @package    Page
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Companies extends Page
{
    protected string $page = 'companies';

    public function list(array $params = []): void
    {
        $this->I->sendGET(static::route($this->page), $params);
    }

    public function create(array $params): void
    {
        $this->I->sendPOST(static::route($this->page), $params);
    }

    public function view(int $id, array $params = []): void
    {
        $this->I->sendGET(static::route("{$this->page}/$id"), $params);
    }

    public function update(int $id, array $params = []): void
    {
        $this->I->sendPATCH(static::route("{$this->page}/$id"), $params);
    }
}
