@extends('layouts.app', ['title' => 'Cadastrar Dias de Expediente'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Cadastrar Dias de Expediente</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('atendimentos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('atendimentos.store')
        ->multipart()
        ->attrs([
        'onsubmit' => "let btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.innerText='Salvando...';"
    ])
        !!}
        <div class="row">
            <div class="card">
                <div class="card-body">
                    
                    @include('atendimentos._forms')
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
</div>

@endsection
