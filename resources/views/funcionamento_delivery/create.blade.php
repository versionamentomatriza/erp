@extends('layouts.app', ['title' => 'Funcionamento'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo horário</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('funcionamento-delivery.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">

        {!!Form::open()
        ->post()
        ->route('funcionamento-delivery.store')
        !!}
        <div class="pl-lg-4">
            @include('funcionamento_delivery._forms')
        </div>
        {!!Form::close()!!}

    </div>
</div>

@endsection
@section('js')
<script type="text/javascript">
    
</script>
@endsection
