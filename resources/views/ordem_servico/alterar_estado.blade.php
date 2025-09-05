@extends('layouts.app', ['title' => 'Alterar estado'])
@section('content')

<div class="card mt-1">
    <div class="card-body">
        <div class="pl-lg-4">
            {!!Form::open()
            ->post()
            ->route('ordem-servico.update-estado', [$ordem->id])
            !!}
            @csrf
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                <h5>Estado Atual:
                    @if($ordem->estado == 'pd')
                    <span class="btn btn-warning btn-sm">PENDENTE</span>
                    @elseif($ordem->estado == 'ap')
                    <span class="btn btn-success btn-sm">APROVADO</span>
                    @elseif($ordem->estado == 'rp')
                    <span class="btn btn-danger btn-sm">REPROVADO</span>
                    @else
                    <span class="btn btn-info btn-sm">FINALIZADO</span>
                    @endif
                </h5>


                @if($ordem->estado != 'fz' && $ordem->estado != 'rp')

                <div class="row">
                    <div class="form-group validated col-12 col-lg-3">
                        @if($ordem->estado == 'pd')
                        <select class="form-select" id="sigla_uf" name="novo_estado">
                            <option value="ap">APROVADO</option>
                            <option value="rp">REPROVADO</option>
                        </select>
                        @elseif($ordem->estado == 'ap')
                        <select class="form-select" id="sigla_uf" name="novo_estado">
                            <option value="fz">FINALIZADO</option>
                        </select>
                        @endif
                    </div>
                    <div class="form-group validated col-sm-4 col-lg-4">
                        <button type="submit" class="btn btn-success px-5">Alterar</button>
                    </div>
                </div>

                @elseif($ordem->estado == 'fz')
                <h5 class="text-success">Ordem de serviço finalizada!</h5>

                <div style="text-align: right; margin-top: -35px;">
                    <a href="{{ route('ordem-servico.show', [$ordem->id]) }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                @else
                <h5 class="text-danger">Ordem de serviço reprovada!</h5>
                <div style="text-align: right; margin-top: -35px;">
                    <a href="{{ route('ordem-servico.show', [$ordem->id]) }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                @endif
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

@endsection
