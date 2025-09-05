@extends('layouts.app', ['title' => 'Nova Interrupção'])

@section('content')

<div class="mt-3">
    <div class="row">
        {!!Form::open()
        ->post()
        ->route('interrupcoes.store')
        ->multipart()
        ->attrs([
        'onsubmit' => "let btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.innerText='Salvando...';"
    ])
        !!}
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <h4>Novo intervalo</h4>
                    <hr>
                    @include('interrupcoes._forms')
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
</div>

@endsection
