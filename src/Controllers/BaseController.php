<?php

declare(strict_types=1);

namespace Bastien\TerrariaWikiCommunity\Controllers;

use Slim\Views\PhpRenderer;

/**
 * Class BaseController
 * 
 * Serves as the base controller for all other controllers, providing common functionality.
 */
abstract class BaseController
{
    /** @var PhpRenderer View renderer instance. */
    protected PhpRenderer $view;

    /**  * BaseController constructor.
     * 
     * Initializes the view renderer with default settings.
     */
    public function __construct()
    {
        $this->view = new PhpRenderer(__DIR__ . '/../../views', [
            'title' => 'Terraria Wiki Community',
            'withMenu' => true,
        ]);
        $this->view->setLayout("layout.php");
    }
}
