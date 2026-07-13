
function carregarParlamentares() {
    const tipo = $("#tipo_gabinete_id");
    const uf = $("#uf");
    const parlamentar = $("#parlamentar");

    tipo.on("change", function () {
        uf.val("");
        resetarSelect(parlamentar);
    });

    uf.on("change", function () {
        const ufVal = uf.val();
        const tipoVal = tipo.val();

        if (!ufVal || !tipoVal)
            return;



        parlamentar.empty().append('<option disabled selected>Carregando...</option>').prop("disabled", true);

        if (tipoVal == 1) {
            carregarDeputados(ufVal, parlamentar);
        } else if (tipoVal == 2) {
            carregarSenadores(ufVal, parlamentar);
        }
    });
}

function resetarSelect(select) {
    select.empty().append('<option disabled selected>Selecione o parlamentar</option>').prop("disabled", false);
}

function carregarDeputados(uf, select) {
    $.ajax({
        url: `https://dadosabertos.camara.leg.br/api/v2/deputados?siglaUf=${uf}&ordem=ASC&ordenarPor=nome`,
        method: "GET",
        dataType: "json",
        success: function (data) {
            resetarSelect(select);

            if (!data.dados || data.dados.length === 0) {
                select.append('<option disabled>Nenhum deputado encontrado</option>');
                return;
            }

            data.dados.forEach(function (p) {
                select.append(`<option value="${p.id}" data-nome="${p.nome}" data-partido="${p.siglaPartido}">${p.nome} - ${p.siglaPartido}</option>`);
            });

            select.off("change").on("change", function () {
                const option = $(this).find('option:selected');

                $('input[name="nome_parlamentar"]').val(option.data('nome'));
                $('input[name="partido"]').val(option.data('partido'));
            });
        },
        error: function () {
            resetarSelect(select);
            select.append('<option disabled>Erro ao carregar deputados</option>');
        }
    });
}

function carregarSenadores(uf, select) {
    $.ajax({
        url: `https://legis.senado.leg.br/dadosabertos/senador/lista/atual?v=4`,
        method: "GET",
        dataType: "xml",
        success: function (data) {
            resetarSelect(select);

            const senadores = [];

            $(data).find("Parlamentar").each(function () {
                const ufSenador = $(this).find("UfParlamentar").first().text().trim().toUpperCase() || $(this).find("SiglaUf").first().text().trim().toUpperCase() || $(this).find("SiglaUFParlamentar").first().text().trim().toUpperCase();

                if (ufSenador !== uf.toUpperCase())
                    return;

                const id = $(this).find("CodigoParlamentar").first().text().trim();
                const nome = $(this).find("NomeParlamentar").first().text().trim();
                const partido = $(this).find("SiglaPartidoParlamentar").first().text().trim();

                if (id && nome) {
                    senadores.push({ id, nome, partido });
                }
            });

            if (senadores.length === 0) {
                select.append('<option disabled>Nenhum senador encontrado</option>');
                return;
            }

            senadores.sort((a, b) => a.nome.localeCompare(b.nome, "pt-BR")).forEach(function (s) {
                select.append(`<option value="${s.id}" data-nome="${s.nome}" data-partido="${s.partido}">${s.nome} - ${s.partido}</option>`);
            });

            select.off("change").on("change", function () {
                const option = $(this).find('option:selected');

                $('input[name="nome_parlamentar"]').val(option.data('nome'));
                $('input[name="partido"]').val(option.data('partido'));
            });
        },
        error: function () {
            resetarSelect(select);
            select.append('<option disabled>Erro ao carregar senadores</option>');
        }
    });
}

function validarSenhasTempoReal() {
    const form = $('form');
    const senha = $('input[name="senha"]');
    const senha2 = $('input[name="senha2"]');

    function validar() {
        const valorSenha = senha.val();
        const valorSenha2 = senha2.val();

        senha.removeClass('border-danger border-success border-3');
        senha2.removeClass('border-danger border-success border-3');

        if (valorSenha.length === 0 || valorSenha2.length === 0) {
            form.find('button[type="submit"]').prop('disabled', true);
            return;
        }

        if (valorSenha === valorSenha2) {
            senha.addClass('border-success border-3');
            senha2.addClass('border-success border-3');

            form.find('button[type="submit"]').prop('disabled', false);
        } else {
            senha.addClass('border-danger border-3');
            senha2.addClass('border-danger border-3');

            form.find('button[type="submit"]').prop('disabled', true);
        }
    }

    senha.on('keyup', validar);
    senha2.on('keyup', validar);

    form.find('button[type="submit"]').prop('disabled', true);
}

$(document).ready(function () {
    carregarParlamentares();
    popularEstadosBrasil('#uf');
    validarSenhasTempoReal();
});
