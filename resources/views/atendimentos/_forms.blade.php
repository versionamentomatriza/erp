<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                @isset($funcionarios)
                <div class="col-md-3">
                    {!!Form::select('funcionario_id', 'Funcionário', ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
                    ->attrs(['class' => 'select2'])
                    ->required()
                    !!}
                </div>
                @else
                <input type="hidden" value="{{ $item->funcionario->id }}" name="funcionario_id">
                <h4>Funconário <strong class="text-success">{{ $item->funcionario->nome }}</strong></h4>
                @endif
                <div class="col-md-12 mt-3">
                    @foreach($dias as $key => $d)
                    <input name="dia[]" value="{{$key}}" type="checkbox" class="form-check-input" style=" width: 25px; height: 25px;" @isset($item) @if(in_array($key, $diasEdit)) checked @endif @endif>
                    <label class="form-check-label m-1" for="customCheck1">{{ $d }}</label>

                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="text-end mt-5">
        <button type="submit" class="btn btn-success px-5">Salvar</button>
    </div>
</div>
