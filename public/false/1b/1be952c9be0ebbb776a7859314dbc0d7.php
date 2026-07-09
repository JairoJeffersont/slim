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

/* partials/sidebar.twig */
class __TwigTemplate_f158bdff5ae0f8ff923843ba7f534b1a extends Template
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

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<div class=\"border-end bg-white\" id=\"sidebar-wrapper\">
\t<div class=\"sidebar-heading border-bottom bg-light\">Start Bootstrap</div>
\t<div class=\"list-group list-group-flush\">
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Dashboard</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Shortcuts</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Overview</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Events</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Profile</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Status</a>
\t</div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/sidebar.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  43 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<div class=\"border-end bg-white\" id=\"sidebar-wrapper\">
\t<div class=\"sidebar-heading border-bottom bg-light\">Start Bootstrap</div>
\t<div class=\"list-group list-group-flush\">
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Dashboard</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Shortcuts</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Overview</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Events</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Profile</a>
\t\t<a class=\"list-group-item list-group-item-action list-group-item-light p-3\" href=\"#!\">Status</a>
\t</div>
</div>
", "partials/sidebar.twig", "/Users/jairo/Sites/slim/src/Views/partials/sidebar.twig");
    }
}
