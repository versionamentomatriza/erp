<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'nome_fantasia', 'cpf_cnpj', 'ie', 'email', 'celular', 'csc', 'csc_id', 'arquivo', 
        'senha', 'status', 'cep', 'rua', 'numero', 'bairro', 'complemento', 'cidade_id', 'tributacao', 
        'numero_ultima_nfe_producao', 'numero_ultima_nfe_homologacao', 'numero_serie_nfe',  
        'numero_ultima_nfce_producao', 'numero_ultima_nfce_homologacao', 'numero_serie_nfce', 'token', 'ambiente',
        'numero_ultima_cte_producao', 'numero_ultima_cte_homologacao', 'numero_serie_cte', 'natureza_id_pdv',
        'numero_ultima_mdfe_producao', 'numero_ultima_mdfe_homologacao', 'numero_serie_mdfe', 'logo',
        'tipo_contador', 'limite_cadastro_empresas', 'percentual_comissao', 'exclusao_icms_pis_cofins',
        'token_nfse', 'numero_ultima_nfse', 'numero_serie_nfse', 'aut_xml',
		'cargo_funcao','atividade','qtd_funcionarios', 'nome_contato', 'nome_cobranca', 'email_cobranca', 'telefone_cobranca'
    ];

    protected $appends = [ 'info' ];

    public function getImgAttribute()
    {
        if($this->logo == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/logos/$this->logo";
    }

    public function getInfoAttribute()
    {
        return "$this->nome - $this->cpf_cnpj";
    }

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function configuracaoCardapio(){
        return $this->hasOne(ConfiguracaoCardapio::class, 'empresa_id');
    }

    public function configuracaoMarketPlace(){
        return $this->hasOne(MarketPlaceConfig::class, 'empresa_id');
    }

    public function configuracaoEcommerce(){
        return $this->hasOne(EcommerceConfig::class, 'empresa_id');
    }

    public function usuarios(){
        return $this->hasMany(UsuarioEmpresa::class, 'empresa_id');
    }

    public function roles(){
        return $this->hasMany(Role::class, 'empresa_id');
    }

    public function segmentos(){
        return $this->hasMany(SegmentoEmpresa::class, 'empresa_id');
    }

    public function empresaSelecionada(){
        return $this->belongsTo(Empresa::class, 'empresa_selecionada');
    }

    public function empresasAtribuidas(){
        return $this->hasMany(ContadorEmpresa::class, 'contador_id');
    }

    public function user(){
        return $this->hasMany(User::class, 'id');
    }

    public function plano(){
        return $this->hasOne(PlanoEmpresa::class, 'empresa_id')->with('plano')->orderBy('data_expiracao', 'desc');
    }

    public function financeiroPlano(){
        return $this->hasMany(FinanceiroPlano::class, 'empresa_id');
    }

    public function financeiro(){
        return $this->hasMany(FinanceiroContador::class, 'contador_id');
    }

    public function transferencias()
    {
        return $this->hasMany(TransferenciaConta::class);
    }

    public static function estados(){
        return [
            '11' => 'RO',
            '12' => 'AC',
            '13' => 'AM',
            '14' => 'RR',
            '15' => 'PA',
            '16' => 'AP',
            '17' => 'TO',
            '21' => 'MA',
            '22' => 'PI',
            '23' => 'CE',
            '24' => 'RN',
            '25' => 'PB',
            '26' => 'PE',
            '27' => 'AL',
            '28' => 'SE',
            '29' => 'BA',
            '31' => 'MG',
            '32' => 'ES',
            '33' => 'RJ',
            '35' => 'SP',
            '41' => 'PR',
            '42' => 'SC',
            '43' => 'RS',
            '50' => 'MS',
            '51' => 'MT',
            '52' => 'GO',
            '53' => 'DF'
        ];
    }

    public function lastNumeroNFe($ambiente)
    {
        if($ambiente == 2){
            return $this->numero_ultima_nfe_homologacao+1;
        }else{
            return $this->numero_ultima_nfe_producao+1;
        }
    }

    public function lastNumeroNFCe($ambiente)
    {
        if($ambiente == 2){
            return $this->numero_ultima_nfce_homologacao+1;
        }else{
            return $this->numero_ultima_nfce_producao+1;
        }
    }

    public static function getCodUF($uf){
        foreach(Empresa::estados() as $key => $u){
            if($uf == $u){
                return $key;
            }
        }
    }

}
