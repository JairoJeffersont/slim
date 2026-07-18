<?php

namespace App\Controllers;

use App\Models\Agenda;
use App\Models\Gabinete;
use App\Models\Pessoa;
use App\Models\SituacaoAgenda;
use App\Models\TipoAgenda;
use App\Models\Usuario;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AgendaExternaController extends BaseController {
    private const VIEW = 'pages/agenda/agendamento-externo.twig';
    private const ROUTE = '/agendar/';
    private const TIPO_FIXO = 'Pedido de agenda';

    private function buscarGabinetePorToken(string $token): ?Gabinete {
        return Gabinete::where('token', $token)->first();
    }

    private function normalizarAniversario(?string $valor): ?string {
        if (!$valor) {
            return null;
        }

        $valor = trim($valor);

        if (!preg_match('/^\d{2}\/\d{2}$/', $valor)) {
            return null;
        }

        $partes = explode('/', $valor);

        if (!checkdate((int) $partes[1], (int) $partes[0], 2000)) {
            return null;
        }

        return '2000-' . $partes[1] . '-' . $partes[0];
    }

    private function normalizarData(?string $data): ?string {
        if (!$data) {
            return null;
        }

        $data = trim($data);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return null;
        }

        return $data . ' 09:00:00';
    }

    private function buscarTipoFixo(int $gabineteId, ?int $usuarioId): TipoAgenda {
        return TipoAgenda::firstOrCreate(
            [
                'gabinete_id' => $gabineteId,
                'nome' => self::TIPO_FIXO
            ],
            [
                'usuario_id' => $usuarioId
            ]
        );
    }

    private function buscarSituacaoPadrao(int $gabineteId, int $usuario): ?SituacaoAgenda {


        SituacaoAgenda::firstOrCreate([
            'nome' => 'Aguardando confirmação',
            'gabinete_id' => $gabineteId,
            'usuario_id' => $usuario

        ]);


        return SituacaoAgenda::where('gabinete_id', $gabineteId)
            ->orderBy('id', 'asc')
            ->first();
    }

    private function buscarUsuarioResponsavelId(int $gabineteId): ?int {
        $admin = Usuario::where([
            'gabinete_id' => $gabineteId,
            'tipo_usuario_id' => 1
        ])->orderBy('id', 'asc')->first();

        if ($admin) {
            return $admin->id;
        }

        $usuario = Usuario::where('gabinete_id', $gabineteId)
            ->orderBy('id', 'asc')
            ->first();

        return $usuario?->id;
    }

    public function formulario(Request $request, Response $response, array $args): Response {
        try {
            $token = $args['token'];
            $gabinete = $this->buscarGabinetePorToken($token);

            if (!$gabinete) {
                $this->flash('info', 'Link inválido ou expirado');
                return $this->renderView($request, $response, self::VIEW, $this->getFlash());
            }

            $payload['gabinete'] = $gabinete;
            $payload['token'] = $token;

            return $this->renderView($request, $response, self::VIEW, array_merge($payload, $this->getFlash()));
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->renderView($request, $response, self::VIEW, $this->getFlash());
        }
    }

    public function salvar(Request $request, Response $response, array $args): Response {
        try {
            $token = $args['token'];
            $gabinete = $this->buscarGabinetePorToken($token);

            if (!$gabinete) {
                $this->flash('info', 'Link inválido ou expirado');
                return $this->redirect($response, '/login');
            }

            $dados = $request->getParsedBody();

            $camposObrigatorios = [
                'nome',
                'email',
                'telefone',
                'estado',
                'cidade',
                'aniversario',
                'titulo',
                'pauta',
                'data'
            ];

            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                    $this->flash('info', 'Todos os campos são obrigatórios');
                    return $this->redirect($response, self::ROUTE . $token);
                }
            }

            $email = strtolower(trim($dados['email']));
            $aniversarioNormalizado = $this->normalizarAniversario($dados['aniversario'] ?? null);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('info', 'E-mail inválido');
                return $this->redirect($response, self::ROUTE . $token);
            }

            if (!$aniversarioNormalizado) {
                $this->flash('info', 'Aniversário inválido. Use o formato dd/mm');
                return $this->redirect($response, self::ROUTE . $token);
            }

            $usuarioResponsavelId = $this->buscarUsuarioResponsavelId($gabinete->id);
            $tipoFixo = $this->buscarTipoFixo($gabinete->id, $usuarioResponsavelId);
            $situacaoPadrao = $this->buscarSituacaoPadrao($gabinete->id, $usuarioResponsavelId);

            if (!$situacaoPadrao) {
                $this->flash('info', 'Este gabinete ainda não configurou situações de agenda');
                return $this->redirect($response, self::ROUTE . $token);
            }

            $pessoa = Pessoa::where([
                'email' => $email,
                'gabinete_id' => $gabinete->id
            ])->first();

            if (!$pessoa) {
                $pessoa = Pessoa::create([
                    'nome' => trim($dados['nome']),
                    'email' => $email,
                    'telefone' => trim($dados['telefone']),
                    'estado' => trim($dados['estado']),
                    'cidade' => trim($dados['cidade']),
                    'aniversario' => $aniversarioNormalizado,
                    'gabinete_id' => $gabinete->id,
                    'usuario_id' => $usuarioResponsavelId
                ]);
            }

            $dataHora = $this->normalizarData($dados['data'] ?? null);

            if (!$dataHora) {
                $this->flash('info', 'Data inválida');
                return $this->redirect($response, self::ROUTE . $token);
            }

            Agenda::create([
                'gabinete_id' => $gabinete->id,
                'tipo_agenda_id' => $tipoFixo->id,
                'situacao_agenda_id' => $situacaoPadrao->id,
                'usuario_id' => $usuarioResponsavelId,
                'pessoa_id' => $pessoa->id,
                'titulo' => trim($dados['titulo']),
                'descricao' => trim($dados['pauta']),
                'local' => 'Não informado',
                'data_hora' => $dataHora,
                'data_hora_fim' => null
            ]);

            $this->flash('success', 'Solicitação enviada com sucesso. O gabinete entrará em contato.');
            return $this->redirect($response, self::ROUTE . $token);
        } catch (Exception $e) {
            $this->flashError($e);
            return $this->redirect($response, self::ROUTE . $args['token']);
        }
    }
}
