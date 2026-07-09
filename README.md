# Sistema de Gestão de Gabinete Parlamentar

Sistema web para gestão de gabinetes parlamentares, desenvolvido com PHP 8.2+ e Slim Framework 4.

## Tecnologias

- **PHP** ^8.2
- **Slim Framework** 4.x
- **Twig** (template engine)
- **Eloquent ORM** (illuminate/database)
- **PHPMailer** — envio de e-mails
- **Slim PSR-7**
- **vlucas/phpdotenv** — variáveis de ambiente
- **Bootstrap** 5.3 + **Bootstrap Icons** 1.13
- **jQuery** 4.0 + **jQuery Mask**
- **TinyMCE** 8.5

## Requisitos

- PHP ^8.2
- Composer
- MySQL / MariaDB
- Servidor web com suporte a `.htaccess` (Apache) ou configuração equivalente (Nginx)

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/jairojeffersont/slim.git
   cd slim
   ```

2. Instale as dependências:
   ```bash
   composer install
   ```

3. Copie o arquivo de ambiente e configure suas variáveis:
   ```bash
   cp .env.example .env
   ```

4. Edite o `.env` com os dados do banco e e-mail:
   ```env
   DB_DRIVER=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=nome_do_banco
   DB_USERNAME=usuario
   DB_PASSWORD=senha
   DB_CHARSET=utf8mb4
   DB_COLLATION=utf8mb4_unicode_ci
   ```

5. Importe o banco de dados:
   ```bash
   mysql -u usuario -p nome_do_banco < db.sql
   ```

6. Configure o servidor web para apontar o `document root` para a pasta `public/`.

## Estrutura do Projeto

```
slim/
├── public/              # Document root — ponto de entrada (index.php)
│   ├── css/             # Estilos customizados
│   ├── js/              # Scripts JavaScript
│   ├── img/             # Imagens
│   └── vendor/          # Bibliotecas front-end (Bootstrap, jQuery, TinyMCE)
├── src/
│   ├── Config/          # Configurações (banco de dados, Twig)
│   ├── Controllers/     # Controllers da aplicação
│   ├── Helpers/         # Helpers (CURL, e-mail)
│   ├── Middleware/      # Middlewares (autenticação, sessão)
│   ├── Models/          # Models Eloquent
│   ├── Routes/          # Definição de rotas (web.php)
│   └── Views/           # Templates Twig
├── storage/
│   └── sessions/        # Sessões do servidor
├── logs/                # Logs da aplicação
├── vendor/              # Dependências PHP (Composer)
├── composer.json
├── db.sql               # Script de criação do banco de dados
└── .env                 # Variáveis de ambiente (não versionado)
```

## Rotas Principais

| Método | Rota | Descrição |
|--------|------|-----------|
| GET/POST | `/login` | Autenticação de usuários |
| GET/POST | `/cadastro` | Cadastro de novo gabinete |
| GET/POST | `/esqueci-senha` | Recuperação de senha |
| GET/POST | `/nova-senha/{token}` | Redefinição de senha |
| GET | `/dashboard` | Painel principal *(autenticado)* |
| GET/POST | `/meu-gabinete` | Gerenciamento do gabinete *(autenticado)* |
| GET | `/usuario/{id}` | Detalhes do usuário *(autenticado)* |
| POST | `/usuario/{id}/tipo` | Atualizar tipo de usuário *(autenticado)* |
| POST | `/usuario/{id}/status` | Alterar status do usuário *(autenticado)* |
| POST | `/usuario/{id}/excluir` | Excluir usuário *(autenticado)* |
| GET/POST | `/novo-usuario/{token}` | Aceitar convite de usuário *(autenticado)* |
| GET | `/logout` | Encerrar sessão *(autenticado)* |

## Ferramentas de Desenvolvimento

```bash
# Rodar testes
composer test

# Análise estática (PHPStan)
composer analyse

# Verificação de código (PHP_CodeSniffer)
composer cs
```

## Autor

**Jairo Jefferson Teixeira Dos Santos**
[jairojeffersont@gmail.com](mailto:jairojeffersont@gmail.com)
[github.com/jairojeffersont](https://github.com/jairojeffersont)

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
