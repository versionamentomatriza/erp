@extends('layouts.app', ['title' => 'Importação de XML'])
@section('content')

<div class="card mt-1">
    <div class="card-header">

		<h4>Importação de XML</h4>
		@isset($dadosXml)
			<h5>Chave <strong class="text-success">{{ $dadosXml['chave'] }}</strong></h5>
			<a href="{{ asset('xml_dfe/' . $dadosXml['chave'] . '.xml') }}" class="btn btn-sm px-3" target="_blank" download>
				<i class="ri-download-line"></i> Download do XML
			</a>		
		@endif

		
        <div style="text-align: right; margin-top: -35px;">
            @if(__countLocalAtivo() > 1 && isset($caixa))
            <h5 class="mt-2">Local: <strong class="text-danger">{{ $caixa->localizacao ? $caixa->localizacao->descricao : '' }}</strong></h5>
            @endif
            <a href="{{ route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('compras.finish-xml')
        ->multipart()
        ->attrs([
        'onsubmit' => "let btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.innerText='Salvando...';"
    ])
        !!}
        <div class="pl-lg-4">
            @include('compras._forms_xml')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@section('js')
<script src="/js/nfe.js"></script>
@endsection
@endsection
