@extends('layouts.app', ['title' => 'Nova Ordem de Serviço'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Nova Ordem Serviço</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('ordem-servico.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('ordem-servico.store')
        ->multipart()
        ->attrs([
        'onsubmit' => "let btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.innerText='Salvando...';"
    ])
        !!}
        <div class="pl-lg-4">
            @include('ordem_servico._forms')
        </div>
        {!!Form::close()!!}

    </div>
</div>

@endsection
