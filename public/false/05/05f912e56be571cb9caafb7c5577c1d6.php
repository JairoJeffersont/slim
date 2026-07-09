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

/* layouts/guest.twig */
class __TwigTemplate_3d1aea767823bcede8667ff8600bd44b extends Template
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
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"en\">
\t<head>
\t\t<meta charset=\"utf-8\"/>
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\"/>
\t\t<meta name=\"description\" content=\"\"/>
\t\t<meta name=\"author\" content=\"\"/>
\t\t<title>";
        // line 8
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["app_name"] ?? null), "html", null, true);
        yield "</title>
\t\t<link href=\"vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css\" rel=\"stylesheet\"/>
\t\t<link href=\"css/custom.css\" rel=\"stylesheet\"/>
\t\t<script src=\"vendor/jquery-4.0.0/jquery-4.0.0.min.js\"></script>
\t\t<script src=\"vendor/jquery-mask/jquery.mask.min.js\"></script>
\t\t<script src=\"vendor/tinymce-8.5.1/js/tinymce/tinymce.min.js\"></script>

\t</head>
\t<body>
\t\t<main>
\t\t\t";
        // line 18
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 19
        yield "\t\t</main>
\t\t<script src=\"vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js\"></script>
\t\t<script src=\"js/scripts.js\"></script>
\t</body>
</html>
";
        yield from [];
    }

    // line 18
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layouts/guest.twig";
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
        return array (  78 => 18,  68 => 19,  66 => 18,  53 => 8,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layouts/guest.twig", "/Users/jairo/Sites/slim/src/Views/layouts/guest.twig");
    }
}
