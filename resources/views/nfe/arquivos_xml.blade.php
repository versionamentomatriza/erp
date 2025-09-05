@extends('layouts.app', ['title' => 'Arquivos XML NFe'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Arquivos XML NFe</h4>
        
    </div>
    <div class="card-body">
        <hr class="mt-3">
        <div class="col-lg-12">
            {!!Form::open()->fill(request()->all())
            ->get()
            !!}
            <div class="row mt-3">
                <div class="col-md-2">
                    {!!Form::date('start_date', 'Data inicial')
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::date('end_date', 'Data final')
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('estado', 'Estado', ['aprovado' => 'Aprovado', 'cancelado' => 'Cancelado'])
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>

                @if(__countLocalAtivo() > 1)
                <div class="col-md-2">
                    {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                    ->attrs(['class' => 'select2'])
                    !!}
                </div>
                @endif

                <div class="col-lg-4 col-12">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                </div>
            </div>
            {!!Form::close()!!}

            <div class="col-md-12 mt-3">
                <div class="table-responsive">
                    <table class="table table-striped table-centered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Cliente</th>
                                <th>Número</th>
                                <th>Chave</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                            @if(file_exists(public_path("xml_nfe/").$item->chave.".xml"))
                            <tr>
                                <td>{{ $item->cliente ? $item->cliente->info : '--' }}</td>
                                <td>{{ $item->numero }}</td>
                                <td>{{ $item->chave }}</td>
                                <td>{{ __moeda($item->total) }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                        @if(sizeof($data) > 0)
                        <tfoot style="font-weight: bold;">
                            <td colspan="3" style="text-align: right;">Total</td>
                            <td>{{ __moeda($data->sum('total')) }}</td>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if(sizeof($data) > 0)
            <br>
            <form method="get" action="{{ route('nfe-xml.download') }}">
                <input type="hidden" name="start_date" value="{{ request()->start_date }}">
                <input type="hidden" name="end_date" value="{{ request()->end_date }}">
                <input type="hidden" name="estado" value="{{ request()->estado }}">
                <input type="hidden" name="local_id" value="{{ request()->local_id }}">
                <button class="btn btn-dark">
                    <i class="ri-file-zip-line"></i>
                    Download Zip
                </button>
            </form>
            @else
            <p class="text-danger">Filtre por período para buscar os arquivos</p>
            @endif
        </div>
    </div>
</div>

@endsection
