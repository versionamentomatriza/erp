@extends('layouts.app', ['title' => 'Perfil'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <div style="text-align: right; margin-top: -15px;">
            <a href="{{ route('usuarios.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
   
    <div class="card-body">
        <div class="pl-lg-4">
            <div class="row">
                <div class="">
                    <div class="text-left mt-3">
                        <div class="p-3 border rounded text-start">
                            @isset($item)
                            @if($item->imagem != null)
                            <img src="{{ $item->img }}" class="rounded-circle avatar-lg img-thumbnail">
                            @else
                            <img src="/imgs/no-image.png" class="rounded-circle avatar-lg img-thumbnail">
							<br>
                            @endif
                            @endisset
							<br>
                            <p class="text-muted mb-2"><strong>Nome de Usuário: </strong><span class="ms-2">{{ $item->name }}</span></p>
                            @if($item->empresa)
                            <p class="text-muted mb-2"><strong>Empresa: </strong> <span class="ms-2">{{ $item->empresa->empresa->nome }}</span></p>
                            @endif
                            {{-- <button type="button" class="btn btn-success btn-sm mb-2">Follow</button> --}}
                            <a href="{{ route('usuarios.edit', $item->id) }}" class="btn btn-danger btn-sm mb-2">Editar</a>

							<div class="text-left mt-3">
								<h4 class="fs-13 text-uppercase">Dados Gerais</h4>
								
								<div class="p-3 border rounded text-start">
									<p class="text-muted mb-2"><strong>Nome :</strong> <span class="ms-2">{{ $item->name }}</span></p>
									
									<p class="text-muted mb-2"><strong>E-mail :</strong><span class="ms-2">{{ $item->email }}</span></p>
									
									<p class="text-muted mb-2"><strong>Data do Cadastro :</strong> <span class="ms-2">{{ $item->created_at->format('d/m/Y H:i:s') }}</span></p>
									
									@if($item->empresa)
									<p class="text-muted mb-1"><strong>Plano :</strong> 
										<span class="ms-2">
											<a href="../../upgrade" target="_blank">{{ $item->empresa->empresa->plano->plano->nome }}</a>
										</span>
									</p>
									
									<div class="text-left mt-3">
										<div class="p-3 border rounded text-start">
											@if($item->empresa)
											<p class="text-muted mb-1">
												<span class="ms-2">{!! $item->empresa->empresa->plano->plano->descricao !!}</span>
											</p>
											@endif
										</div>
									</div>									
									<br>
									<p class="text-muted mb-1"><strong>Data de Expiração :</strong> <span class="ms-2">{{ __data_pt($item->empresa->empresa->plano->data_expiracao, 0) }}</span></p>
									@endif
								</div>
							</div>


                        </div> <!-- end card-body -->
						
				
						
                    </div> <!-- end card -->
                </div> <!-- end col-->
            </div>
        </div>
    </div>

</div>
@endsection
