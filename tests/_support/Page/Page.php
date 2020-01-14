<?php

declare(strict_types=1);

namespace Page;

/**
 * Class Page
 *
 * @package    Page
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
abstract class Page
{
    /**
     * @var \FunctionalTester
     */
    protected \FunctionalTester $I;

    /**
     * @var string
     */
    protected static string $url = '/';

    /**
     * @var string
     */
    protected string $page;

    /**
     * Constructor.
     *
     * @param \FunctionalTester $I
     */
    public function __construct(\FunctionalTester $I)
    {
        $this->I = $I;
    }

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     *
     * @param string $param
     *
     * @return string
     */
    public static function route($param): string
    {
        return static::$url . $param;
    }
}
