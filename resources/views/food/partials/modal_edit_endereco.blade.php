@section('css')
<style type="text/css">

  .fa{
    font-size: 50px!important;
  }
  .border{

  }
</style>
@endsection
<div class="modal fade" id="modal-edit-endereco" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <form method="post" action="{{ route('food.endereco-update') }}">
        <input type="hidden" name="link" value="{{ $config->loja_id }}">
        <input type="hidden" id="endereco_id" name="endereco_id">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Editar endereço</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 col-md-6 mt-1">
              <input required type="text" class="form-control" id="rua" name="rua" placeholder="Rua">
            </div>

            <div class="col-4 col-md-2 mt-1">
              <input required type="text" class="form-control" id="numero" name="numero" placeholder="Nº">
            </div>

            <div class="col-8 col-md-4 mt-1">
              <select required class="w-100 _bairro_id" id="bairro_id" name="bairro_id">
                @foreach($bairros as $b)
                <option value="{{ $b->id }}">{{ $b->nome }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12 col-md-4 mt-1">
              <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Referência">
            </div>

            <div class="col-6 col-md-2 mt-1">
              <select required class="w-100" id="tipo" name="tipo">
                <option value="casa">Casa</option>
                <option value="trabalho">Trabalho</option>
              </select>
            </div>
            <div class="col-6 col-md-4 mt-1">
              <input type="checkbox" class="form-checkbox" id="padrao" name="padrao">
              Padrão
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-main text-white">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>