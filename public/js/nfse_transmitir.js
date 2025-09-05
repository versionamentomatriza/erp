var IDNFSE = null;

function transmitir(id) {
	console.clear();

	// Alerta de carregamento (não nativo na v1, mas simulável)
	swal({
		title: "Transmitindo...",
		text: "Por favor, aguarde enquanto a nota é processada.",
		icon: "info",
		buttons: false,
		closeOnClickOutside: false,
		closeOnEsc: false
	});

	$.post(path_url + "api/nfse/transmitir", { id: id })
	.done((success) => {
		swal("Sucesso", success.mensagem, "success").then(() => {
			location.reload();
		});
	})
	.fail((err) => {
		if (err.status == 404) {
			let json = err.responseJSON;
			let motivo = json.mensagem;
			let erros = json.erros;

			if (erros) {
				erros.map((e) => {
					motivo += e.erro;
				});
			}

			swal("Algo deu errado", motivo, "error");
		} else {
			swal("Algo deu errado", err.responseJSON, "error");
		}
	});
}

function consultar(id) {
	console.clear();

	swal({
		title: "Consultando...",
		text: "Aguarde enquanto consultamos a nota.",
		icon: "info",
		buttons: false,
		closeOnClickOutside: false,
		closeOnEsc: false
	});

	$.post(path_url + "api/nfse/consultar", { id: id })
	.done((success) => {
		swal("Sucesso", success.mensagem, "success").then(() => {
			window.open(success.link_pdf);
		});
	})
	.fail((err) => {
		try {
			swal("Erro", err.responseJSON.mensagem, "error").then(() => {
				location.reload();
			});
		} catch {
			swal("Erro", "Erro consulte o console", "error");
		}
	});
}

$('#btn-cancelar').click(() => {
	console.clear();

	swal({
		title: "Cancelando...",
		text: "Estamos processando o cancelamento da nota.",
		icon: "info",
		buttons: false,
		closeOnClickOutside: false,
		closeOnEsc: false
	});

	$.post(path_url + "api/nfse/cancelar", {
		id: IDNFSE,
		motivo: $('#inp-motivo-cancela').val()
	})
	.done((success) => {
		swal("Sucesso", success.mensagem, "success");
	})
	.fail((err) => {
		try {
			swal("Erro", err.responseJSON.mensagem, "error").then(() => {
				location.reload();
			});
		} catch {
			try {
				swal("Erro", err.responseJSON.mensagem, "error");
			} catch {
				swal("Erro", err.responseJSON, "error");
			}
		}
	});
});

function cancelar(id, numero) {
	IDNFSE = id;
	$('.ref-numero').text(numero);
	$('#modal-cancelar').modal('show');
}
