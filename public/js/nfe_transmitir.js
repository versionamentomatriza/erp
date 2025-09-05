var IDNFE = null;

function transmitir(id) {
    console.clear();

    swal({
        title: "Transmitindo NFe",
        text: "Aguarde enquanto processamos...",
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
        content: {
            element: "div",
            attributes: {
                innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
            }
        }
    });

    $.post(path_url + "api/nfe_painel/emitir", { id: id })
    .done((success) => {
        swal.close();
        swal("Sucesso", "NFe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
        .then(() => {
            window.open(path_url + 'nfe/imprimir/' + id, "_blank");
            setTimeout(() => location.reload(), 100);
        });
    })
    .fail((err) => {
        swal.close();
        console.log(err);
        tratarErroNfe(err);
    });
}

function cancelar(id, numero) {
    IDNFE = id;
    $('.ref-numero').text(numero);
    $('#modal-cancelar').modal('show');
}

function imprimir(id, numero) {
    IDNFE = id;
    $('.ref-numero').text(numero);
    $('#modal-print').modal('show');
}

function corrigir(id, numero) {
    IDNFE = id;
    $('.ref-numero').text(numero);
    $('#modal-corrigir').modal('show');
}

function gerarDanfe(tipo) {
    if (tipo == 'danfe') {
        window.open('/nfe/imprimir/' + IDNFE);
    } else if (tipo == 'simples') {
        window.open('/nfe/danfe-simples/' + IDNFE);
    } else if (tipo == 'etiquetas') {
        window.open('/nfe/danfe-etiqueta/' + IDNFE);
    } else if (tipo == 'etiqueta_correio') {
        window.open('/nfe/danfe-etiqueta-correio/' + IDNFE);
    } else {
        $('#modal-print').modal('hide');
    }
}

$('#btn-cancelar').click(() => {
    if (IDNFE != null) {

        swal({
            title: "Cancelando NFe",
            text: "Aguarde enquanto processamos...",
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
            content: {
                element: "div",
                attributes: {
                    innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
                }
            }
        });

        $.post(path_url + "api/nfe_painel/cancelar", {
            id: IDNFE,
            motivo: $('#inp-motivo-cancela').val()
        })
        .done((success) => {
            swal.close();
            swal("Sucesso", "NFe cancelada " + success, "success")
            .then(() => {
                window.open(path_url + 'nfe/imprimir-cancela/' + IDNFE, "_blank");
                setTimeout(() => location.reload(), 100);
            });
        })
        .fail((err) => {
            swal.close();
            console.log(err);
            tratarErroNfe(err);
        });
    } else {
        swal("Erro", "Nota não selecionada", "error");
    }
});

$('#btn-corrigir').click(() => {
    if (IDNFE != null) {

        swal({
            title: "Corrigindo NFe",
            text: "Aguarde enquanto processamos...",
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
            content: {
                element: "div",
                attributes: {
                    innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
                }
            }
        });

        $.post(path_url + "api/nfe_painel/corrigir", {
            id: IDNFE,
            motivo: $('#inp-motivo-corrigir').val()
        })
        .done((success) => {
            swal.close();
            swal("Sucesso", "NFe corrigida " + success, "success")
            .then(() => {
                window.open(path_url + 'nfe/imprimir-correcao/' + IDNFE, "_blank");
                setTimeout(() => location.reload(), 100);
            });
        })
        .fail((err) => {
            swal.close();
            console.log(err);
            tratarErroNfe(err);
        });
    } else {
        swal("Erro", "Nota não selecionada", "error");
    }
});

function consultar(id, numero) {

    swal({
        title: "Consultando NFe",
        text: "Aguarde enquanto processamos...",
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
        content: {
            element: "div",
            attributes: {
                innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
            }
        }
    });

    $.post(path_url + "api/nfe_painel/consultar", { id: id })
    .done((success) => {
        swal.close();
        swal("Sucesso", success, "success")
        .then(() => location.reload());
    })
    .fail((err) => {
        swal.close();
        console.log(err);
        tratarErroNfe(err);
    });
}

function tratarErroNfe(err) {
    try {
        if (err.responseJSON.error) {
            let o = err.responseJSON.error.protNFe.infProt;
            swal("Algo deu errado", o.cStat + " - " + o.xMotivo, "error")
            .then(() => location.reload());
        } else {
            swal("Algo deu errado", err[0], "error");
        }
    } catch {
        if (err.responseJSON?.message) {
            swal("Algo deu errado", err.responseJSON.message, "error")
            .then(() => location.reload());
        } else if (err.responseJSON?.xMotivo) {
            swal("Algo deu errado", err.responseJSON.xMotivo, "error")
            .then(() => location.reload());
        } else if (err.responseJSON?.error) {
            swal("Algo deu errado", err.responseJSON.error, "error")
            .then(() => location.reload());
        } else {
            swal("Algo deu errado", err.responseJSON ?? err, "error")
            .then(() => location.reload());
        }
    }
}
