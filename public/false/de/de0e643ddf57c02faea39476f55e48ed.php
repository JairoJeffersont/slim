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

/* pages/login/form_login.twig */
class __TwigTemplate_65c340687ac32a6212af9593cff49a5a extends Template
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
        yield "\t<div class=\"container-fluid min-vh-100 d-flex justify-content-center align-items-center\">
\t\t<div class=\"card border-0 bg-transparent\" style=\"width: 100%; max-width: 400px;\">
\t\t\t<div class=\"card-body\">

\t\t\t\t<div class=\"text-center mb-3\">
\t\t\t\t\t<img src=\"img/logo_white.png\" alt=\"Logo\" class=\"img-fluid\" style=\"max-width: 120px;\">
\t\t\t\t</div>

\t\t\t\t<h2 class=\"text-center mb-2 text-white fw-light\">
\t\t\t\t\t";
        // line 13
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["app_name"] ?? null), "html", null, true);
        yield "
\t\t\t\t</h2>

\t\t\t\t<p class=\"card-text text-center text-white\">";
        // line 16
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["app_slogan"] ?? null), "html", null, true);
        yield "</p>

\t\t\t\t<form method=\"post\" class=\"custom-form\" enctype=\"application/x-www-form-urlencoded\">
\t\t\t\t\t<div class=\"mb-2\">
\t\t\t\t\t\t<input type=\"email\" name=\"email\" class=\"form-control rounded-pill py-2 px-4\" value=\"jairojeffersont@gmail.com\" placeholder=\"E-mail\">
\t\t\t\t\t</div>

\t\t\t\t\t<div class=\"mb-4\">
\t\t\t\t\t\t<input type=\"password\" name=\"senha\" class=\"form-control rounded-pill py-2 px-4\" value=\"intell01\" placeholder=\"Senha\">
\t\t\t\t\t</div>

\t\t\t\t\t<button type=\"submit\" name=\"btn_login\" class=\"btn btn-success w-100 rounded-pill py-2\">
\t\t\t\t\t\tEntrar
\t\t\t\t\t</button>
\t\t\t\t</form>
\t\t\t\t<div class=\"d-flex justify-content-center gap-3 mt-3\">
\t\t\t\t\t<a href=\"/esqueci-senha\" class=\"text-white opacity-75 text-decoration-none\">
\t\t\t\t\t\tEsqueci minha senha
\t\t\t\t\t</a>

\t\t\t\t\t<span class=\"text-white opacity-50\">|</span>

\t\t\t\t\t<a href=\"/cadastro\" class=\"text-white opacity-75 text-decoration-none\">
\t\t\t\t\t\tCadastre seu gabinete
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
        return "pages/login/form_login.twig";
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
        return array (  76 => 16,  70 => 13,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/login/form_login.twig", "/Users/jairo/Sites/slim/src/Views/pages/login/form_login.twig");
    }
}
