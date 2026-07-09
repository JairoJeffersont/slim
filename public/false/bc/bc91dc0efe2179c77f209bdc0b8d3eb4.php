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
class __TwigTemplate_5a0d96fcfec6ccffb7a93668776389a7 extends Template
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
        // line 2
        return "layouts/guest.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layouts/guest.twig", 2);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 4
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield "Erro interno do servidor
";
        yield from [];
    }

    // line 7
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 8
        yield "\t<div class=\"container py-5 text-center\">

\t\t<h1>500</h1>

\t\t<h2>Erro interno do servidor</h2>

\t\t<p>
\t\t\tOcorreu um erro inesperado ao processar sua solicitação.
\t\t\t        Tente novamente em alguns instantes.
\t\t</p>

\t\t";
        // line 19
        if ((($tmp = ($context["error_id"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 20
            yield "\t\t\t<p>
\t\t\t\t<strong>Código do erro:</strong>
\t\t\t\t";
            // line 22
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error_id"] ?? null), "html", null, true);
            yield "
\t\t\t</p>
\t\t";
        }
        // line 25
        yield "
\t\t<a href=\"/\">Voltar para a página inicial</a>

\t</div>
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
        return array (  97 => 25,  91 => 22,  87 => 20,  85 => 19,  72 => 8,  65 => 7,  53 => 4,  42 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "errors/500.twig", "/Users/jairo/Sites/slim/src/Views/errors/500.twig");
    }
}
