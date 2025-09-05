@extends('layouts.app', ['title' => 'Editar CTe'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar CTe</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('cte.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()->fill($item)
        ->put()
        ->route('cte.update', [$item->id])
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('cte._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>

@section('js')
<script src="/js/cte.js"></script>
@endsection

@endsection
