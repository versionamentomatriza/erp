@extends('layouts.app', ['title' => 'Editar Funcionário'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar Funcionário</h4>
        <div>
            <a href="{{ route('funcionarios.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()->fill($item)
        ->put()
        ->route('funcionarios.update', [$item->id])
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('funcionario._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection
