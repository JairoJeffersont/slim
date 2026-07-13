<?php

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends BaseController {
    private const VIEW = 'dashboard.twig';

    public function index(Request $request, Response $response): Response {
        return $this->renderView($request, $response, self::VIEW, $this->getFlash());
    }


}
