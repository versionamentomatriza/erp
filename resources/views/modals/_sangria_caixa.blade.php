<div class="modal fade modal-action-pos" id="sangria_caixa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Sangria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!!Form::open()
                ->post()
                ->route('sangria.store')
                 ->attrs([
                'onsubmit' => "this.querySelector('button[type=submit]').disabled = true;"
                ])
                !!}
                <div class="row">

                    <input type="hidden" name="caixa_id" value="{{ $abertura->id }}">
                    <div class="col-md-4">
                        {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required() !!}
                    </div>
                    <div class="col-md-8 div-conta-empresa">
                        {!!Form::select('conta_empresa_sangria_id', 'Conta empresa')
                        ->attrs(['class' => 'conta_empresa'])
                        !!}
                    </div>

                    <div class="col-md-12 mt-2">
                        {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
                    </div>
                    <div class="mt-3 ms-auto">
                        <button type="submit" class="btn btn-primary px-3 float-end">Salvar Sangria</button>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>

        </div>
    </div>
</div>
