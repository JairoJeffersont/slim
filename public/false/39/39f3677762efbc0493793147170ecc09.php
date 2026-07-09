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

/* errors/404.twig */
class __TwigTemplate_1c7b472c6a878582810f879c70fc39ae extends Template
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
        yield "Página Não Encontrada (404)
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
        yield "\t\t\t\t<div class=\"mb-4 text-secondary opacity-75\">
\t\t\t\t\t<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"64\" fill=\"currentColor\" class=\"bi bi-file-earmark-break\" viewbox=\"0 0 16 16\">
\t\t\t\t\t\t<path d=\"M14 4.5V9h-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v7H2V2a2 2 0 0 1 2-2h5.5zM13 12h1v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3h1v3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1zM.5 10a.5.5 0 0 1 .5-.5h14a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5\"/>
\t\t\t\t\t</svg>
\t\t\t\t</div>

\t\t\t\t";
        // line 20
        yield "\t\t\t\t<h1 class=\"display-1 fw-bold mb-2 text-primary\" style=\"letter-spacing: -2px; line-height: 1;\">404</h1>

\t\t\t\t<h4 class=\"text-dark fw-semibold mb-3\">Página não encontrada</h4>

\t\t\t\t<p class=\"text-muted small mb-4\">
\t\t\t\t\tO endereço digitado não existe ou a página foi movida para outro local.
\t\t\t\t\t<br>
\t\t\t\t\tVerifique a URL ou retorne ao painel principal.
\t\t\t\t</p>

\t\t\t\t";
        // line 31
        yield "\t\t\t\t<div class=\"pt-2\">
\t\t\t\t\t<a href=\"/dashboard\" class=\"btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm text-decoration-none\">
\t\t\t\t\t\tVoltar para o Início
\t\t\t\t\t</a>
\t\t\t\t</div>

\t\t\t</div>
\t\t</div>
\t</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "errors/404.twig";
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
        return array (  99 => 31,  87 => 20,  79 => 13,  72 => 7,  65 => 6,  53 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/guest.twig\" %}

{% block title %}Página Não Encontrada (404)
{% endblock %}

{% block content %}
\t<div class=\"container-fluid min-vh-100 d-flex justify-content-center align-items-center\">
\t\t<div class=\"card border-0 bg-white shadow text-center p-4 p-md-5\" style=\"width: 100%; max-width: 480px; border-radius: 16px;\">
\t\t\t<div
\t\t\t\tclass=\"card-body\">

\t\t\t\t{# Ícone de Lupa/Página do Bootstrap #}
\t\t\t\t<div class=\"mb-4 text-secondary opacity-75\">
\t\t\t\t\t<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"64\" height=\"64\" fill=\"currentColor\" class=\"bi bi-file-earmark-break\" viewbox=\"0 0 16 16\">
\t\t\t\t\t\t<path d=\"M14 4.5V9h-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v7H2V2a2 2 0 0 1 2-2h5.5zM13 12h1v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3h1v3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1zM.5 10a.5.5 0 0 1 .5-.5h14a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5\"/>
\t\t\t\t\t</svg>
\t\t\t\t</div>

\t\t\t\t{# Número do Erro em Destaque Grande #}
\t\t\t\t<h1 class=\"display-1 fw-bold mb-2 text-primary\" style=\"letter-spacing: -2px; line-height: 1;\">404</h1>

\t\t\t\t<h4 class=\"text-dark fw-semibold mb-3\">Página não encontrada</h4>

\t\t\t\t<p class=\"text-muted small mb-4\">
\t\t\t\t\tO endereço digitado não existe ou a página foi movida para outro local.
\t\t\t\t\t<br>
\t\t\t\t\tVerifique a URL ou retorne ao painel principal.
\t\t\t\t</p>

\t\t\t\t{# Botão de Ação Principal #}
\t\t\t\t<div class=\"pt-2\">
\t\t\t\t\t<a href=\"/dashboard\" class=\"btn btn-primary rounded-pill px-5 py-2 fw-semibold shadow-sm text-decoration-none\">
\t\t\t\t\t\tVoltar para o Início
\t\t\t\t\t</a>
\t\t\t\t</div>

\t\t\t</div>
\t\t</div>
\t</div>
{% endblock %}
", "errors/404.twig", "/Users/jairo/Sites/slim/src/Views/errors/404.twig");
    }
}
