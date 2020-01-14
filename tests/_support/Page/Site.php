<?php

declare(strict_types=1);

namespace Page;

/**
 * Class Site
 *
 * @package    Page
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Site extends Page
{
    public function csrf(string $method = 'HEAD'): void
    {
        $this->I->{"send$method"}(static::route('csrf'));
    }
}
