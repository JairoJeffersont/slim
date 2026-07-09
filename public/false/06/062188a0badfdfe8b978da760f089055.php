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

/* errors/500.twig */
class __TwigTemplate_3cf56aa6b3ccf2ba9d715a8a0d3a5b45 extends Template
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
            'title' => [$this, 'block_title'],
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
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Erro Interno do Servidor (500)
";
        yield from [];
    }

    // line 6
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 7
        yield "\t<div class=\"container-fluid min-vh-100 d-flex justify-content-center align-items-center\">
\t\t<div class=\"card border-0 bg-white shadow text-center p-4 p-md-5\" style=\"width: 100%; max-width: 480px; border-radius: 16px;\">
\t\t\t<div
\t\t\t\tclass=\"card-body\">

\t\t\t\t";
        // line 13
        yield "\t\t\t\t<div class=\"mb-4 text-danger opacity-90\">
\t\t\t\t\t<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"64\" fill=\"currentColor\" class=\"bi bi-exclamation-circle-fill\" viewbox=\"0 0 16 16\">
\t\t\t\t\t\t<path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2\"/>
\t\t\t\t\t</svg>
\t\t\t\t</div>

\t\t\t\t";
        // line 20
        yield "\t\t\t\t<h1 class=\"display-4 fw-bold mb-2 text-dark\" style=\"letter-spacing: -1px;\">Erro 500</h1>

\t\t\t\t<h4 class=\"text-secondary fw-normal mb-3\">Algo deu errado no servidor</h4>

\t\t\t\t<p class=\"text-muted small mb-4\">
\t\t\t\t\tOcorreu um erro inesperado ao processar sua solicitação.
\t\t\t\t\t<br>
\t\t\t\t\tNossa equipe técnica já foi alertada. Por favor, tente novamente em alguns instantes.
\t\t\t\t</p>

\t\t\t\t";
        // line 31
        yield "\t\t\t\t";
        if ((($tmp = ($context["error_id"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 32
            yield "\t\t\t\t\t<div class=\"bg-light rounded-3 p-3 mb-4 text-start font-monospace small border d-flex align-items-center justify-content-between\">
\t\t\t\t\t\t<div>
\t\t\t\t\t\t\t<span class=\"text-muted d-block text-uppercase tracking-wider\" style=\"font-size: 0.65rem;\">ID do Suporte</span>
\t\t\t\t\t\t\t<span class=\"text-dark fw-bold\">";
            // line 35
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error_id"] ?? null), "html", null, true);
            yield "</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<button class=\"btn btn-sm btn-link text-muted p-0 text-decoration-none hover-primary\" onclick=\"navigator.clipboard.writeText(\x27";
            // line 37
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error_id"] ?? null), "html", null, true);
            yield "\x27); alert(\x27ID copiado!\x27);\" title=\"Copiar ID\">
\t\t\t\t\t\t\t<small>Copiar</small>
\t\t\t\t\t\t</button>
\t\t\t\t\t</div>
\t\t\t\t";
        }
        // line 42
        yield "
\t\t\t\t";
        // line 44
        yield "\t\t\t\t<div class=\"pt-2\">
\t\t\t\t\t<a href=\"/\" class=\"btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm text-decoration-none\">
\t\t\t\t\t\tVoltar para a Página Inicial
\t\t\t\t\t</a>
\t\t\t\t</div>

\t\t\t</div>
\t\t</div>
\t</div>

\t<style>
\t\t.tracking-wider {
\t\t\tletter-spacing: 0.05em;
\t\t}
\t\t.hover-primary:hover {
\t\t\tcolor: #0d6efd !important;
\t\t}
\t</style>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "errors/500.twig";
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
        return array (  123 => 44,  120 => 42,  112 => 37,  107 => 35,  102 => 32,  99 => 31,  87 => 20,  79 => 13,  72 => 7,  65 => 6,  53 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/guest.twig\" %}

{% block title %}Erro Interno do Servidor (500)
{% endblock %}

{% block content %}
\t<div class=\"container-fluid min-vh-100 d-flex justify-content-center align-items-center\">
\t\t<div class=\"card border-0 bg-white shadow text-center p-4 p-md-5\" style=\"width: 100%; max-width: 480px; border-radius: 16px;\">
\t\t\t<div
\t\t\t\tclass=\"card-body\">

\t\t\t\t{# Ícone de Alerta em Vermelho Suave / Laranja #}
\t\t\t\t<div class=\"mb-4 text-danger opacity-90\">
\t\t\t\t\t<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"64\" fill=\"currentColor\" class=\"bi bi-exclamation-circle-fill\" viewbox=\"0 0 16 16\">
\t\t\t\t\t\t<path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2\"/>
\t\t\t\t\t</svg>
\t\t\t\t</div>

\t\t\t\t{# Número do Erro em Destaque Moderno #}
\t\t\t\t<h1 class=\"display-4 fw-bold mb-2 text-dark\" style=\"letter-spacing: -1px;\">Erro 500</h1>

\t\t\t\t<h4 class=\"text-secondary fw-normal mb-3\">Algo deu errado no servidor</h4>

\t\t\t\t<p class=\"text-muted small mb-4\">
\t\t\t\t\tOcorreu um erro inesperado ao processar sua solicitação.
\t\t\t\t\t<br>
\t\t\t\t\tNossa equipe técnica já foi alertada. Por favor, tente novamente em alguns instantes.
\t\t\t\t</p>

\t\t\t\t{# Bloco do Error ID Sólido (Estilo Painel Cinza) #}
\t\t\t\t{% if error_id %}
\t\t\t\t\t<div class=\"bg-light rounded-3 p-3 mb-4 text-start font-monospace small border d-flex align-items-center justify-content-between\">
\t\t\t\t\t\t<div>
\t\t\t\t\t\t\t<span class=\"text-muted d-block text-uppercase tracking-wider\" style=\"font-size: 0.65rem;\">ID do Suporte</span>
\t\t\t\t\t\t\t<span class=\"text-dark fw-bold\">{{ error_id }}</span>
\t\t\t\t\t\t</div>
\t\t\t\t\t\t<button class=\"btn btn-sm btn-link text-muted p-0 text-decoration-none hover-primary\" onclick=\"navigator.clipboard.writeText(\x27{{ error_id }}\x27); alert(\x27ID copiado!\x27);\" title=\"Copiar ID\">
\t\t\t\t\t\t\t<small>Copiar</small>
\t\t\t\t\t\t</button>
\t\t\t\t\t</div>
\t\t\t\t{% endif %}

\t\t\t\t{# Botão de Ação Principal #}
\t\t\t\t<div class=\"pt-2\">
\t\t\t\t\t<a href=\"/\" class=\"btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm text-decoration-none\">
\t\t\t\t\t\tVoltar para a Página Inicial
\t\t\t\t\t</a>
\t\t\t\t</div>

\t\t\t</div>
\t\t</div>
\t</div>

\t<style>
\t\t.tracking-wider {
\t\t\tletter-spacing: 0.05em;
\t\t}
\t\t.hover-primary:hover {
\t\t\tcolor: #0d6efd !important;
\t\t}
\t</style>
{% endblock %}
", "errors/500.twig", "/Users/jairo/Sites/slim/src/Views/errors/500.twig");
    }
}
