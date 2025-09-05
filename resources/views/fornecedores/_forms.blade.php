<div class="row g-2">
    <div class="col-md-2">
        {!!Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('razao_social', 'Razão Social')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('nome_fantasia', 'Nome Fantasia')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('ie', 'IE')->attrs(['class' => 'ie'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('consumidor_final', 'Consumidor Final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required()
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('email', 'Email')->attrs(['class' => ''])->type('email') !!}
    </div>
    <div class="col-md-4">
        @isset($item)
        {!!Form::select('cidade_id', 'Cidade')
        ->attrs(['class' => 'select2'])->options(($item && $item->cidade) != null ? [$item->cidade_id => $item->cidade->info] : [])
        ->required()
        !!}
        @else
        {!!Form::select('cidade_id', 'Cidade')
        ->attrs(['class' => 'select2'])
        ->required()
        !!}
        @endisset
    </div>
    <div class="col-md-3">
        {!!Form::text('rua', 'Rua')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-1">
        {!!Form::text('numero', 'Número')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('cep', 'CEP')->attrs(['class' => 'cep'])->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('bairro', 'Bairro')->attrs(['class' => ''])->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('complemento', 'Complemento')->attrs(['class' => ''])
        !!}
    </div>
    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script>

$(document).on("blur", "#inp-cpf_cnpj", function () {

let cpf_cnpj = $(this).val().replace(/[^0-9]/g,'')

if(cpf_cnpj.length == 14){
    
    // Mostrar alerta de carregando
    swal({
        title: "Buscando informações",
        text: "Aguarde, estamos consultando os dados do CNPJ...",
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
        content: {
            element: "div",
            attributes: {
                innerHTML: '<i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-top: 15px;"></i>'
            }
        }
    });

    $.get('https://publica.cnpj.ws/cnpj/' + cpf_cnpj)
    .done((data) => {
        swal.close(); // Fecha o carregando

        if (data!= null) {
            let ie = ''
            if (data.estabelecimento.inscricoes_estaduais.length > 0) {
                ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual
            }

            $('#inp-ie').val(ie)
            if(ie != ""){
                $('#inp-contribuinte').val(1).change()
            }
            $('#inp-nome').val(data.razao_social)
            $('#inp-nome_fantasia').val(data.estabelecimento.nome_fantasia)
            $("#inp-rua").val(data.estabelecimento.tipo_logradouro + " " + data.estabelecimento.logradouro)
            $('#inp-numero').val(data.estabelecimento.numero)
            $("#inp-bairro").val(data.estabelecimento.bairro);
            let cep = data.estabelecimento.cep.replace(/[^\d]+/g, '');
            $('#inp-cep').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))
            $('#inp-email').val(data.estabelecimento.email)
            $('#inp-telefone').val(data.estabelecimento.telefone1)

            findCidade(data.estabelecimento.cidade.ibge_id)
        }
    })
    .fail((err) => {
        swal.close(); // Fecha o carregando mesmo se der erro
        console.log(err);
        swal("Erro", "Não foi possível consultar o CNPJ. Verifique o número ou tente novamente mais tarde.", "error");
    })
}
})

    function findCidade(codigo_ibge){

        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
        .done((res) => {
            var newOption = new Option(res.info, res.id, false, false);
            $('#inp-cidade_id').append(newOption).trigger('change');
        })
        .fail((err) => {
            console.log(err)
        })

    }

    $('#inp-ie').blur(() => {
        if($('#inp-ie').val() != ""){
            $('#inp-contribuinte').val(1).change()
        }
    })

</script>
@endsection


