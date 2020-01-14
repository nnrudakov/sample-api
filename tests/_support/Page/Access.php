<?php

declare(strict_types=1);

namespace Page;

/**
 * Class Access
 *
 * @package    Page
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Access extends Page
{
    protected string $page = 'access';

    public function view(int $companyId, int $userId): void
    {
        $this->I->sendGET(static::route("{$this->page}/$companyId/$userId"));
    }

    public function update(int $companyId, int $userId, array $params): void
    {
        $this->I->sendPATCH(static::route("{$this->page}/$companyId/$userId"), ['permissions' => $params]);
    }
}
