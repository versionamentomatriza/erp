<div class="row">
    <div class="col-md-2">
        {!!Form::text('name', 'Nome')
        ->required()
        ->attrs(['maxlength' => 15])!!}
    </div>
    <div class="col-md-5">
        {!!Form::text('description', 'Descrição')
        ->required()
        ->attrs(['maxlength' => 40])!!}
    </div>
    
    <div class="col-md-12 mt-3">
        {!!Form::select('permissions[]', 'Permissões', $permissions->pluck('description', 'id')->all())
        ->attrs(['class' => 'multi-select'])
        ->multiple()
        ->value(isset($item) ? $item->permissions->pluck('id')->all() : [])
        ->required()!!}
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>
