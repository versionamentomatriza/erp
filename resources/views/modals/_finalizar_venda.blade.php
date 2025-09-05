<div class="modal fade" id="finalizar_venda" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Finalizar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> 
            <div class="modal-body">
                <div class="row">
                    <div class="@can('nfce_create') col-md-6 @endcan col-12">
                        <button type="button" class="btn btn-info" id="btn_nao_fiscal" style="height: 50px; width: 100%">
                            <i class="bx bx-file-blank"> </i> CUPOM NÃO FISCAL
                        </button>
                    </div>
                    @can('nfce_create')
                    <div class="col-md-6 col-12">
                        <button type="button" class="btn btn-success" style="height: 50px; width: 100%" data-bs-toggle="modal" data-bs-target="#cpf_nota">
                            <i class="bx bx-file-blank"> </i> CUPOM FISCAL
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
        </div> 
    </div> 
</div> 
@include('modals._cpf_nota', ['not_submit' => true])
