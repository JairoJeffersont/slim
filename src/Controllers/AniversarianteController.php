<?php

namespace App\Controllers;

use App\Models\Pessoa;
use App\Models\Usuario; // Importa o modelo de Usuário
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

            // 1. QUERY DE PESSOAS
            $queryPessoas = Pessoa::where('gabinete_id', $this->usuario['gabinete_id'])
                ->whereNotNull('aniversario');

            // 2. QUERY DE USUÁRIOS
            $queryUsuarios = Usuario::where('gabinete_id', $this->usuario['gabinete_id'])
                ->whereNotNull('aniversario');

            // Aplica os filtros de data em ambas as queries
            if ($filtro === 'dia') {
                $queryPessoas->whereMonth('aniversario', $mesAtual)->whereDay('aniversario', $diaAtual);
                $queryUsuarios->whereMonth('aniversario', $mesAtual)->whereDay('aniversario', $diaAtual);
            } else {
                $queryPessoas->whereMonth('aniversario', $mesAtual);
                $queryUsuarios->whereMonth('aniversario', $mesAtual);
            }

            // Executa as consultas ordenadas pelo dia do aniversário
            $pessoas = $queryPessoas->orderByRaw('DAY(aniversario) ASC')->get();
            $usuarios = $queryUsuarios->orderByRaw('DAY(aniversario) ASC')->get();

            // Formata os dados de forma homogênea adicionando um campo identificador 'tipo_registro'
            $listaAniversariantes = [];

            foreach ($usuarios as $u) {
                $listaAniversariantes[] = [
                    'nome'          => $u->nome,
                    'telefone'      => $u->telefone,
                    'aniversario'   => $u->aniversario, // O cast do Eloquent já converte para objeto de data/carbon
                    'tipo_registro' => 'usuario', // Identifica que é da equipe interna
                    'link'          => '/perfil' // Link para o perfil se aplicável (ou /usuario/id)
                ];
            }

            foreach ($pessoas as $p) {
                $listaAniversariantes[] = [
                    'nome'          => $p->nome,
                    'telefone'      => $p->celular ?? $p->telefone_fixo,
                    'aniversario'   => $p->aniversario,
                    'tipo_registro' => 'pessoa', // Identifica que é um contato externo
                    'link'          => "/pessoas/{$p->id}" // Link para a ficha da pessoa
                ];
            }

            // Ordena a lista unificada pelo dia do aniversário
            usort($listaAniversariantes, function ($a, $b) {
                // Se o aniversário for um objeto Carbon/DateTime, pegamos o dia usando format() ou propriedade 'day'
                $diaA = $a['aniversario'] instanceof \DateTimeInterface ? (int)$a['aniversario']->format('d') : (int)date('d', strtotime($a['aniversario']));
                $diaB = $b['aniversario'] instanceof \DateTimeInterface ? (int)$b['aniversario']->format('d') : (int)date('d', strtotime($b['aniversario']));
                return $diaA <=> $diaB;
            });

            $payload = [
                'aniversariantes' => $listaAniversariantes,
                'filtro_atual'    => $filtro,
                'mes_nome'        => $this->getMesNome($mesAtual)
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
