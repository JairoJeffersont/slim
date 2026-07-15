<?php

namespace App\Controllers;

use App\Models\Pessoa;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AniversarianteController extends BaseController {

    private const VIEW_ANIVERSARIANTES = 'pages/pessoas/aniversariantes.twig';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    public function index(Request $request, Response $response): Response {
        try {
            $queryParams = $request->getQueryParams();
            $filtro = $queryParams['filtro'] ?? 'dia';

            $hoje = new \DateTime();
            $diaAtual = $hoje->format('d');
            $mesAtual = $hoje->format('m');

            $query = Pessoa::where('gabinete_id', $this->usuario['gabinete_id'])
                ->whereNotNull('aniversario');

            if ($filtro === 'dia') {
                $query->whereMonth('aniversario', $mesAtual)
                    ->whereDay('aniversario', $diaAtual);
            } else {
                $query->whereMonth('aniversario', $mesAtual);
            }

            $aniversariantes = $query->orderByRaw('DAY(aniversario) ASC')->get();

            $payload = [
                'aniversariantes' => $aniversariantes,
                'filtro_atual' => $filtro,
                'mes_nome' => $this->getMesNome($mesAtual)
            ];

            return $this->renderView($request, $response, self::VIEW_ANIVERSARIANTES, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, '/dashboard');
        }
    }

    private function getMesNome(string $mes): string {
        $meses = [
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        ];
        return $meses[$mes] ?? '';
    }
}
