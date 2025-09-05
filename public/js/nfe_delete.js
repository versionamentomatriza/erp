$(document).ready(function () {
  // Selecionar/Deselecionar todas as checkboxes
  $("#select-all-checkbox").on("click", function () {
    let isChecked = $(this).prop("checked");
    $(".check-delete").prop("checked", isChecked);
    validaButtonDelete();
  });

  // Monitorar mudanças em checkboxes individuais
  $(".check-delete").on("change", function () {
    validaButtonDelete();
  });

  // Ativar/Desativar botão de exclusão e atualizar campos ocultos
  function validaButtonDelete() {
    let checkedCount = $(".check-delete:checked").length;
    let $btnDeleteAll = $(".btn-delete-all");

    $btnDeleteAll.prop("disabled", checkedCount === 0);

    // Atualizar o formulário com os valores dos checkboxes marcados
    let $form = $("#form-delete-select div");
    $form.empty();

    $(".check-delete:checked").each(function () {
      $("<input>", {
        type: "hidden",
        name: "item_delete[]",
        value: $(this).val(),
      }).appendTo($form);
    });
  }

  // Inicializar a validação
  validaButtonDelete();

  // Confirmação ao clicar no botão de exclusão
  $(".btn-delete-all").on("click", function (e) {
    e.preventDefault();

    swal({
      title: "Excluir NFes selecionadas?",
      text: "Esta ação não pode ser desfeita. Deseja continuar?",
      icon: "warning",
      buttons: ["Cancelar", "Excluir"],
      dangerMode: true,
    }).then((isConfirm) => {
      if (isConfirm) {
        $("#form-delete-select").submit();
      } else {
        swal("", "Nenhuma NFe foi excluída.", "info");
      }
    });
  });
});
