<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use Strux\Component\Attributes\Route;
use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Response;

class WelcomeController extends Controller
{
    #[Route(path: '/', methods: ['GET'], name: 'welcome')]
    public function index(): Response
    {
        $version = $this->container->get('app.version');
        return $this->view('welcome', [
            'version' => $version,
            'controller_path' => 'src/Http/Controllers/Web/WelcomeController.php',
            'view_path' => 'templates/welcome.php'
        ]);
    }
}