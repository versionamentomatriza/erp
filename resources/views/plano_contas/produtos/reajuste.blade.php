@extends('layouts.app', ['title' => 'Reajuste de Produtos'])
@section('css')
<style type="text/css">
    .div-overflow {
        width: 180px;
        overflow-x: auto;
        white-space: nowrap;
    }
</style>
@endsection
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
		<a href="{{ !isset($isProdutos) ? route('produtos.index') : route('produtos.index') }}" class="btn btn-danger btn-sm px-3 position-absolute" style="right: 15px; top: 8px; z-index: 1;"> <i class="ri-arrow-left-double-fill"></i>Voltar </a>
            <div class="card-body">

                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('nome', 'Pesquisar por nome')
                            !!}
                        </div>
                        
                        <div class="col-md-2">
                            {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categorias->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('marca_id', 'Marca', ['' => 'Selecione'] + $marcas->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-4">
                            {!!Form::select('cst_csosn', 'CST/CSOSN', ['' => 'Selecione'] + App\Models\Produto::listaCSTCSOSN())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('produtos.reajuste') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                            
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                @if(sizeof($data) > 0)
                <div class="row mt-2">
                    <div class="col-md-2">
                        {!!Form::tel('percentual_valor_venda', '% Valor de venda')
                        ->attrs(['class' => ''])
                        !!}
                    </div>
                </div>
                @endif

                <form method="post" action="{{ route('produtos-reajuste.update') }}">
                    @csrf
                    <div class="col-md-12 mt-3 table-responsive">
                        <h6>Total de registros: <strong>{{ sizeof($data) }}</strong></h6>
                        <div class="table-responsive-sm">
                            <table class="table table-striped table-centered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produto</th> 
                                        <th>Valor de venda</th> 
                                        <th>Valor de compra</th> 
                                        <th>CST/CSOSN</th> 
                                        <th>CST PIS</th> 
                                        <th>CST COFINS</th> 
                                        <th>CST IPI</th> 
                                        <th>% ICMS</th> 
                                        <th>% PIS</th> 
                                        <th>% COFINS</th> 
                                        <th>% IPI</th> 
                                        <th>% RED. BC</th> 
                                        <th>CFOP Saída estadual</th> 
                                        <th>CFOP Saída outro estado</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>
                                            <label style="width: 300px;">{{ $item->nome }}</label>
                                        </td>
                                        <td>
                                            <input type="hidden" name="produto_id[]" value="{{ $item->id }}">
                                            <input type="hidden" class="valor_venda" value="{{ $item->valor_unitario }}">
                                            <input required style="width: 150px" type="tel" class="form-control moeda" name="valor_unitario[]" value="{{ __moeda($item->valor_unitario) }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control moeda" name="valor_compra[]" value="{{ __moeda($item->valor_compra) }}">
                                        </td>
                                        <td>
                                            <select required class="select2" name="cst_csosn[]" style="width: 450px">
                                                @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $v)
                                                <option @if($key == $item->cst_csosn) selected @endif value="{{ $key }}">{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td style="width: 350px">
                                            <select required class="select2" name="cst_pis[]">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $v)
                                                <option @if($key == $item->cst_pis) selected @endif value="{{ $key }}">{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td style="width: 350px">
                                            <select required class="select2" name="cst_cofins[]">
                                                @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $v)
                                                <option @if($key == $item->cst_cofins) selected @endif value="{{ $key }}">{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td style="width: 350px">
                                            <select required class="select2" name="cst_ipi[]">
                                                @foreach(App\Models\Produto::listaCST_IPI() as $key => $v)
                                                <option @if($key == $item->cst_ipi) selected @endif value="{{ $key }}">{{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control percentual" name="perc_icms[]" value="{{ $item->perc_icms }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control percentual" name="perc_pis[]" value="{{ $item->perc_pis }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control percentual" name="perc_cofins[]" value="{{ $item->perc_cofins }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control percentual" name="perc_ipi[]" value="{{ $item->perc_ipi }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control percentual" name="perc_red_bc[]" value="{{ $item->perc_red_bc }}">
                                        </td>

                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control cfop" name="cfop_estadual[]" value="{{ $item->cfop_estadual }}">
                                        </td>
                                        <td>
                                            <input required style="width: 150px" type="tel" class="form-control cfop" name="cfop_outro_estado[]" value="{{ $item->cfop_outro_estado }}">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="20" class="text-center">Filtre para buscar os produtos</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>


                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success float-end mt-3">Salvar</button>
                    </div>
                </form>

                <br>

            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $('body').on('blur', '#inp-percentual_valor_venda', function () {

        let percentual = $(this).val()
        $('.valor_venda').each(function (e, x) {
            $vInp = $(this).next()
            let v = parseFloat($(this).val())
            let nv = v + (v*(percentual/100))
            console.log(nv)

            $vInp.val(convertFloatToMoeda(nv))

        })
    })

    $("#inp-percentual_valor_venda").mask("Z999.00", {

        translation: {
            '0': {pattern: /\d/},
            '9': {pattern: /\d/, optional: true},
            'Z': {pattern: /[\-\+]/, optional: true}
        }

    });
</script>
@endsection

