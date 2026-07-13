/**
 * Controla a abertura e fechamento da sidebar.
 *
 * O método adiciona/remova a classe:
 * body.sb-sidenav-toggled
 *
 * Também salva o estado no localStorage.
 *
 * Exemplo de uso HTML:
 *
 * <button id="sidebarToggle">Menu</button>
 *
 * Exemplo de classe aplicada:
 *
 * <body class="sb-sidenav-toggled">
 */
function acaoSidebar() {
    const sidebarToggle = $('#sidebarToggle');

    if (sidebarToggle.length) {
        sidebarToggle.on('click', function (event) {
            event.preventDefault();

            $('body').toggleClass('sb-sidenav-toggled');

            localStorage.setItem(
                'sb|sidebar-toggle',
                $('body').hasClass('sb-sidenav-toggled')
            );
        });
    }
}

/**
 * Inicializa alertas automáticos.
 *
 * Todos os elementos com classe .alert
 * e atributo data-time serão removidos
 * automaticamente após o tempo informado.
 *
 * O tempo deve ser informado em segundos.
 *
 * Exemplo de uso HTML:
 *
 * <div class="alert alert-success" data-time="5">
 *     Usuário cadastrado com sucesso!
 * </div>
 *
 * Neste exemplo:
 * O alerta desaparecerá após 5 segundos.
 */
function iniciarAlertas() {
    $('.alert').each(function () {
        const alerta = $(this);
        const tempo = parseInt(alerta.data('time'));

        if (tempo > 0) {
            setTimeout(function () {
                alerta.fadeOut(500, function () {
                    $(this).remove();
                });
            }, tempo * 1000);
        }
    });
}

/**
 * Popula um select com os estados do Brasil.
 *
 * O método recebe o seletor do campo select.
 *
 * Também permite selecionar automaticamente
 * um estado através do atributo:
 * data-selected
 *
 * Parâmetro:
 * selectId => ID ou seletor do select
 *
 * Exemplo de uso JS:
 *
 * popularEstadosBrasil('#estado');
 *
 * Exemplo de uso HTML:
 *
 * <select id="estado" data-selected="SP"></select>
 *
 * Neste exemplo:
 * O select será preenchido automaticamente
 * e o estado de São Paulo ficará selecionado.
 */
function popularEstadosBrasil(selectId) {

    const estados = [
        { uf: "AC", nome: "Acre" },
        { uf: "AL", nome: "Alagoas" },
        { uf: "AP", nome: "Amapá" },
        { uf: "AM", nome: "Amazonas" },
        { uf: "BA", nome: "Bahia" },
        { uf: "CE", nome: "Ceará" },
        { uf: "DF", nome: "Distrito Federal" },
        { uf: "ES", nome: "Espírito Santo" },
        { uf: "GO", nome: "Goiás" },
        { uf: "MA", nome: "Maranhão" },
        { uf: "MT", nome: "Mato Grosso" },
        { uf: "MS", nome: "Mato Grosso do Sul" },
        { uf: "MG", nome: "Minas Gerais" },
        { uf: "PA", nome: "Pará" },
        { uf: "PB", nome: "Paraíba" },
        { uf: "PR", nome: "Paraná" },
        { uf: "PE", nome: "Pernambuco" },
        { uf: "PI", nome: "Piauí" },
        { uf: "RJ", nome: "Rio de Janeiro" },
        { uf: "RN", nome: "Rio Grande do Norte" },
        { uf: "RS", nome: "Rio Grande do Sul" },
        { uf: "RO", nome: "Rondônia" },
        { uf: "RR", nome: "Roraima" },
        { uf: "SC", nome: "Santa Catarina" },
        { uf: "SP", nome: "São Paulo" },
        { uf: "SE", nome: "Sergipe" },
        { uf: "TO", nome: "Tocantins" }
    ];

    const select = $(selectId);

    if (select.length) {

        const selectedUf = select.data('selected');

        select.empty();

        select.append('<option value="">Selecione um estado*</option>');

        estados.forEach(function (estado) {
            select.append(`<option value="${estado.uf}">${estado.nome}</option>`);
        });

        if (selectedUf) {
            select.val(selectedUf);
        }
    }
}

/**
 * Popula os municípios com base no estado selecionado.
 *
 * Parâmetros:
 *
 * estadoSelectId => ID ou seletor do select de estado
 * municipioSelectId => ID ou seletor do select de município
 *
 * Também permite selecionar automaticamente
 * um município através do atributo:
 * data-selected
 *
 * Exemplo HTML:
 *
 * <select id="estado"></select>
 *
 * <select id="municipio" data-selected="Campinas"></select>
 *
 * Exemplo JS:
 *
 * popularMunicipiosBrasil('#estado', '#municipio');
 */
function popularMunicipiosBrasil(estadoSelectId, municipioSelectId) {

    const estadoSelect = $(estadoSelectId);
    const municipioSelect = $(municipioSelectId);

    if (!estadoSelect.length || !municipioSelect.length) {
        return;
    }

    estadoSelect.on('change', function () {

        const uf = $(this).val();
        const selectedMunicipio = municipioSelect.data('selected');

        municipioSelect.empty();
        municipioSelect.append('<option value="">Carregando municípios...</option>');

        if (!uf) {

            municipioSelect.empty();
            municipioSelect.append('<option value="">Selecione um município*</option>');

            return;
        }

        $.ajax({
            url: `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`,
            type: 'GET',
            dataType: 'json',
            success: function (municipios) {

                municipioSelect.empty();
                municipioSelect.append('<option value="">Selecione um município</option>');

                municipios.forEach(function (municipio) {

                    municipioSelect.append(`<option value="${municipio.nome}">${municipio.nome}</option>`);
                });

                if (selectedMunicipio) {
                    municipioSelect.val(selectedMunicipio);
                }
            },
            error: function () {

                municipioSelect.empty();
                municipioSelect.append('<option value="">Erro ao carregar municípios</option>');
            }
        });
    });

    if (estadoSelect.val()) {
        estadoSelect.trigger('change');
    }
}

function carregarPartidos() {
    const select = $('#partidos');
    const partidoSelecionado = select.data('selected');


    $.ajax({
        url: 'https://dadosabertos.camara.leg.br/api/v2/partidos?itens=100&ordem=ASC&ordenarPor=sigla',
        method: 'GET',
        dataType: 'json',
        success: function (data) {

            if (!data.dados || data.dados.length === 0) {
                select.append('<option disabled>Nenhum partido encontrado</option>');
                return;
            }

            data.dados
                .filter(p => p.sigla)
                .sort((a, b) => a.sigla.localeCompare(b.sigla, 'pt-BR'))
                .forEach(function (p) {
                    select.append(`<option value="${p.sigla}">${p.sigla}</option>`);
                });

            if (partidoSelecionado) {
                select.val(partidoSelecionado);
            }
        },
        error: function () {

            select.append('<option disabled>Erro ao carregar partidos</option>');
        }
    });
}

/**
 * Inicializa confirmação antes de executar ações.
 *
 * Todo elemento com classe .confirm
 * exibirá uma caixa de confirmação.
 *
 * O texto da confirmação pode ser personalizado
 * usando o atributo:
 * data-text
 *
 * Exemplo de uso HTML:
 *
 * <a href="/excluir/1"
 *    class="confirm"
 *    data-text="Deseja realmente excluir este registro?">
 *    Excluir
 * </a>
 *
 * Se o usuário clicar em "Cancelar",
 * a ação será interrompida.
 */
function iniciarConfirmacao() {

    $(document).on('click', '.confirm', function (event) {

        const texto = $(this).data('text') || 'Tem certeza?';

        const ok = confirm(texto);

        if (!ok) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}


function inicializarFiltrosAniversario(selectDiaId, selectMesId) {

    const $selectDia = $('#' + selectDiaId);
    const $selectMes = $('#' + selectMesId);

    const diaSelecionado = $selectDia.data('selected');
    const mesSelecionado = $selectMes.data('selected');

    const meses = [
        { valor: '01', nome: 'Janeiro' },
        { valor: '02', nome: 'Fevereiro' },
        { valor: '03', nome: 'Março' },
        { valor: '04', nome: 'Abril' },
        { valor: '05', nome: 'Maio' },
        { valor: '06', nome: 'Junho' },
        { valor: '07', nome: 'Julho' },
        { valor: '08', nome: 'Agosto' },
        { valor: '09', nome: 'Setembro' },
        { valor: '10', nome: 'Outubro' },
        { valor: '11', nome: 'Novembro' },
        { valor: '12', nome: 'Dezembro' }
    ];

    $selectMes.html('<option value="">Mês</option>');

    $.each(meses, function (_, mes) {

        const selected = mes.valor == mesSelecionado ? 'selected' : '';

        $selectMes.append(`<option value="${mes.valor}" ${selected}>${mes.nome}</option>`);
    });

    function carregarDias() {

        const mesAtual = $selectMes.val();

        let totalDias = 31;

        if (mesAtual === '02') {
            totalDias = 29;
        } else if (['04', '06', '09', '11'].includes(mesAtual)) {
            totalDias = 30;
        }

        $selectDia.html('<option value="">Todo o mês</option>');

        for (let i = 1; i <= totalDias; i++) {

            const dia = String(i).padStart(2, '0');

            const selected = dia == diaSelecionado ? 'selected' : '';

            $selectDia.append(`<option value="${dia}" ${selected}>${dia}</option>`);
        }
    }

    carregarDias();

    $selectMes.on('change', function () {

        $selectDia.data('selected', '');

        carregarDias();
    });
}

/**
 * Executado quando o documento HTML estiver carregado.
 *
 * Responsável por:
 *
 * - Inicializar sidebar
 * - Inicializar alertas automáticos
 * - Aplicar máscaras nos campos
 *
 * Exemplo de máscara HTML:
 *
 * <input type="text" data-mask="000.000.000-00">
 */
$(document).ready(function () {
    acaoSidebar();
    iniciarAlertas();
    iniciarConfirmacao();

    $('[data-mask]').each(function () {
        $(this).mask($(this).data('mask'));
    });

    tinymce.init({
        license_key: 'gpl',
        selector: '#tiny',
        height: 500,
        language: 'pt_BR',
        menubar: true,
        branding: false,
        promotion: false,
        statusbar: true,
        resize: false,
        browser_spellcheck: true,
        contextmenu: 'undo redo | cut copy paste | link image table',
        plugins: [
            'advlist',
            'anchor',
            'autolink',
            'autosave',
            'charmap',
            'code',
            'codesample',
            'directionality',
            'emoticons',
            'fullscreen',
            'help',
            'image',
            'insertdatetime',
            'link',
            'lists',
            'media',
            'preview',
            'searchreplace',
            'table',
            'visualblocks',
            'visualchars',
            'wordcount'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | emoticons charmap codesample | searchreplace visualblocks code fullscreen preview | removeformat',
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino,serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,geneva,sans-serif',
        fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
        content_style: 'body { font-family:Arial,sans-serif; font-size:14px }',
        autosave_ask_before_unload: true,
        autosave_interval: '30s',
        autosave_retention: '2m',
        image_caption: true,
        image_title: true,
        automatic_uploads: false,
        toolbar_sticky: true,
        toolbar_sticky_offset: 0,
        paste_data_images: true,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        quickbars_insert_toolbar: 'image media table hr',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });
});
