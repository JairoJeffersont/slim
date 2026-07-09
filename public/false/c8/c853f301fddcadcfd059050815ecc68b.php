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

/* layouts/app.twig */
class __TwigTemplate_290b67b0f2631a47f1eb23973d054043 extends Template
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
\t\t<div class=\"d-flex\" id=\"wrapper\">
\t\t\t";
        // line 17
        yield from $this->load("partials/sidebar.twig", 17)->unwrap()->yield($context);
        // line 18
        yield "\t\t\t<div id=\"page-content-wrapper\">
\t\t\t\t";
        // line 19
        yield from $this->load("partials/topmenu.twig", 19)->unwrap()->yield($context);
        // line 20
        yield "\t\t\t\t<div class=\"container-fluid\">
\t\t\t\t\t<main>
\t\t\t\t\t\t";
        // line 22
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 23
        yield "\t\t\t\t\t</main>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t\t<script src=\"vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js\"></script>
\t\t<script src=\"js/scripts.js\"></script>
\t</body>
</html>
";
        yield from [];
    }

    // line 22
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
        return "layouts/app.twig";
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
        return array (  91 => 22,  78 => 23,  76 => 22,  72 => 20,  70 => 19,  67 => 18,  65 => 17,  53 => 8,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"en\">
\t<head>
\t\t<meta charset=\"utf-8\"/>
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\"/>
\t\t<meta name=\"description\" content=\"\"/>
\t\t<meta name=\"author\" content=\"\"/>
\t\t<title>{{ app_name }}</title>
\t\t<link href=\"vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css\" rel=\"stylesheet\"/>
\t\t<link href=\"css/custom.css\" rel=\"stylesheet\"/>
\t\t<script src=\"vendor/jquery-4.0.0/jquery-4.0.0.min.js\"></script>
\t\t<script src=\"vendor/jquery-mask/jquery.mask.min.js\"></script>
\t\t<script src=\"vendor/tinymce-8.5.1/js/tinymce/tinymce.min.js\"></script>
\t</head>
\t<body>
\t\t<div class=\"d-flex\" id=\"wrapper\">
\t\t\t{% include \x27partials/sidebar.twig\x27 %}
\t\t\t<div id=\"page-content-wrapper\">
\t\t\t\t{% include \x27partials/topmenu.twig\x27 %}
\t\t\t\t<div class=\"container-fluid\">
\t\t\t\t\t<main>
\t\t\t\t\t\t{% block content %}{% endblock %}
\t\t\t\t\t</main>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t\t<script src=\"vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js\"></script>
\t\t<script src=\"js/scripts.js\"></script>
\t</body>
</html>
", "layouts/app.twig", "/Users/jairo/Sites/slim/src/Views/layouts/app.twig");
    }
}
