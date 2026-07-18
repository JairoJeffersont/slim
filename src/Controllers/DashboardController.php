<?php

namespace App\Controllers;

use App\Models\Agenda;
use App\Models\Pessoa;
use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends BaseController {
    private const VIEW = 'dashboard.twig';

    private array $usuario;

    public function __construct() {
        $this->usuario = $_SESSION['usuario'];
    }

    public function index(Request $request, Response $response): Response {
        try {
            $payload['aniversariantes_data'] = $this->listarAniversariantes();
            $payload['compromissos_data'] = $this->listarCompromissos(); // Adicionado aqui

            $dadosView = array_merge($payload, $this->getFlash());
            return $this->renderView($request, $response, self::VIEW, $dadosView);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    private function listarCompromissos() {
        $hoje = new \DateTime();
        $dataAtual = $hoje->format('Y-m-d');
        $gabineteId = $this->usuario['gabinete_id'];

        // 1. Conta o total de compromissos do dia direto no banco (sem limites)
        // Filtra pela data_hora iniciando no dia de hoje
        $totalCompromissos = Agenda::where('gabinete_id', $gabineteId)
            ->whereDate('data_hora', $dataAtual)
            ->count();

        // 2. Busca os dados limitados e ordenados pelo horário para a lista
        $compromissos = Agenda::where('gabinete_id', $gabineteId)
            ->whereDate('data_hora', $dataAtual)
            ->orderBy('data_hora', 'ASC')
            ->limit(5)
            ->get();

        $listaCompromissos = [];

        foreach ($compromissos as $c) {
            $listaCompromissos[] = [
                'titulo'    => $c->titulo,
                'local'     => $c->local ?? 'Não informado',
                // Pega apenas o horário (HH:MM) da data_hora que já é um objeto Carbon/DateTime
                'horario'   => $c->data_hora->format('H:i'),
                'link'      => "/agenda/{$c->id}" // Adapte para a sua rota de detalhes da agenda se necessário
            ];
        }

        // Retorna a estrutura unificada (lista limitada e total absoluto)
        return [
            'lista' => $listaCompromissos,
            'total' => $totalCompromissos
        ];
    }

    private function listarAniversariantes() {
        $hoje = new \DateTime();
        $mes = $hoje->format('m');
        $dia = $hoje->format('d');
        $gabineteId = $this->usuario['gabinete_id'];

        // 1. Faz a contagem real direto no banco de dados primeiro
        $totalUsuarios = Usuario::where('gabinete_id', $gabineteId)
            ->whereNotNull('aniversario')
            ->whereMonth('aniversario', $mes)
            ->whereDay('aniversario', $dia)
            ->count();

        $totalPessoas = Pessoa::where('gabinete_id', $gabineteId)
            ->whereNotNull('aniversario')
            ->whereMonth('aniversario', $mes)
            ->whereDay('aniversario', $dia)
            ->count();

        // 2. Busca os dados limitados para a lista
        $listaAniversariantes = [];

        $usuarios = Usuario::where('gabinete_id', $gabineteId)
            ->whereNotNull('aniversario')
            ->whereMonth('aniversario', $mes)
            ->whereDay('aniversario', $dia)
            ->limit(5)
            ->get();

        foreach ($usuarios as $u) {
            $listaAniversariantes[] = [
                'nome'          => $u->nome,
                'telefone'      => $u->telefone,
                'tipo_registro' => 'usuario',
                'link'          => '/perfil'
            ];
        }

        $limiteRestante = 5 - count($listaAniversariantes);

        if ($limiteRestante > 0) {
            $pessoas = Pessoa::where('gabinete_id', $gabineteId)
                ->with('orgao')
                ->whereNotNull('aniversario')
                ->whereMonth('aniversario', $mes)
                ->whereDay('aniversario', $dia)
                ->limit($limiteRestante)
                ->get();

            foreach ($pessoas as $p) {
                $listaAniversariantes[] = [
                    'nome'          => $p->nome,
                    'telefone'      => $p->telefone,
                    'foto'          => $p->foto,
                    'tipo_registro' => 'pessoa',
                    'link'          => "/pessoas/{$p->id}",
                    'orgao'         => $p->orgao->nome ?? 'Órgão não informado'
                ];
            }
        }

        // Retorna as duas informações unificadas neste método
        return [
            'lista' => $listaAniversariantes,
            'total' => $totalUsuarios + $totalPessoas
        ];
    }
}
