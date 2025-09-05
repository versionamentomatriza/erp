@extends('layouts.app', ['title' => 'Apuração Mensal'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Apuração Mensal</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('apuracao-mensal.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('apuracao-mensal.store')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('apuracao_mensal._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection