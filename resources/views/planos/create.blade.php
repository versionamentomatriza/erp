@extends('layouts.app', ['title' => 'Novo plano'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Plano</h4>

        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('planos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('planos.store')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('planos._forms')
        </div>
        {!!Form::close()!!}

    </div>
</div>
@endsection
