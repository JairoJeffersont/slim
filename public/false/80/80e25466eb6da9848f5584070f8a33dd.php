<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedTestError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* pages/cadastro/form_cadastro.twig */
class __TwigTemplate_68f99278b953922c3183aa4f5c0b4ad4 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "layouts/guest.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layouts/guest.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 4
        yield "\t<div class=\"container-fluid vh-100 overflow-hidden d-flex justify-content-center align-items-center p-4\">
\t\t<div class=\"card border-0 bg-transparent\" style=\"width: 100%; max-width: 470px;\">
\t\t\t<div class=\"card-body\">
\t\t\t\t<div class=\"text-center mb-3\">
\t\t\t\t\t<img src=\"img/logo_white.png\" alt=\"Logo\" class=\"img-fluid\" style=\"max-width: 120px;\">
\t\t\t\t</div>

\t\t\t\t<h2 class=\"text-center mb-2 text-white fw-light\">
\t\t\t\t\t";
        // line 12
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["app_name"] ?? null), "html", null, true);
        yield "
\t\t\t\t</h2>

\t\t\t\t<p class=\"card-text text-center text-white mb-4\">";
        // line 15
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["app_sloga"] ?? null), "html", null, true);
        yield "</p>

\t\t\t\t<form class=\"row g-2 custom-form\" method=\"POST\">
\t\t\t\t\t<div class=\"col-12\">
\t\t\t\t\t\t<input type=\"text\" class=\"form-control rounded-pill py-2 px-4 border-0\" name=\"nome\" placeholder=\"Nome do responsável pelo gabinete\" required>
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"col-12\">
\t\t\t\t\t\t<input type=\"email\" class=\"form-control rounded-pill py-2 px-4 border-0\" name=\"email\" placeholder=\"E-mail\" required>
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"col-6\">
\t\t\t\t\t\t<input type=\"password\" class=\"form-control rounded-pill py-2 px-4 border-0\" name=\"senha\" placeholder=\"Senha\" required>
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"col-6\">
\t\t\t\t\t\t<input type=\"password\" class=\"form-control rounded-pill py-2 px-4 border-0\" name=\"senha2\" placeholder=\"Confirme a senha\" required>
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"col-md-6\">
\t\t\t\t\t\t<select class=\"form-select rounded-pill py-2 px-4 border-0\" name=\"tipo_gabinete_id\" id=\"tipo_gabinete_id\" required>
\t\t\t\t\t\t\t<option disabled selected hidden value=\"\">Tipo de gabinete</option>
\t\t\t\t\t\t</select>
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"col-12 col-md-6\">
\t\t\t\t\t\t<select class=\"form-select rounded-pill py-2 px-4 border-0\" name=\"uf\" id=\"uf\" required>
\t\t\t\t\t\t\t<option disabled selected hidden value=\"\">Escolha o estado</option>
\t\t\t\t\t\t</select>
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"col-12\">
\t\t\t\t\t\t<select class=\"form-select rounded-pill py-2 px-4 border-0\" name=\"id_parlamentar\" id=\"parlamentar\" required>
\t\t\t\t\t\t\t<option disabled selected hidden value=\"\">Selecione o parlamentar</option>
\t\t\t\t\t\t</select>
\t\t\t\t\t</div>

\t\t\t\t\t<input type=\"hidden\" name=\"nome_parlamentar\">

\t\t\t\t\t<div class=\"col-12 d-flex gap-2 mt-3\">
\t\t\t\t\t\t<button type=\"submit\" name=\"btn_salvar\" class=\"btn btn-success rounded-pill py-2 w-100\">Salvar</button>
\t\t\t\t\t\t<a href=\"/login\" class=\"btn btn-primary rounded-pill py-2 w-100\">Voltar</a>
\t\t\t\t\t</div>
\t\t\t\t</form>
\t\t\t</div>
\t\t</div>
\t</div>

\t<script src=\"js/form_cadastro.js\"></script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/cadastro/form_cadastro.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  75 => 15,  69 => 12,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/cadastro/form_cadastro.twig", "/Users/jairo/Sites/slim/src/Views/pages/cadastro/form_cadastro.twig");
    }
}
