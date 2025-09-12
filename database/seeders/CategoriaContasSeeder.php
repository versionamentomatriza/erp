<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaContasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categoria_contas')->delete();

        $categorias = [

            // =====================
            // RECEITAS
            // =====================
            [
                'nome' => 'Receita de Mercadorias',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_bruta',
                'descricao' => 'Venda de mercadorias para revenda, emissão de notas fiscais de saída.'
            ],
            [
                'nome' => 'Receita de Produtos',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_bruta',
                'descricao' => 'Venda de produtos acabados, faturamento de produção própria.'
            ],
            [
                'nome' => 'Receita de Serviços Prestados',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_bruta',
                'descricao' => 'Receita proveniente da prestação de serviços diversos.'
            ],

            // =====================
            // DEDUÇÕES DA RECEITA
            // =====================
            [
                'nome' => 'ICMS sobre Vendas',
                'tipo' => 'despesa',
                'grupo_dre' => 'deducao_receita',
                'descricao' => 'Impostos sobre faturamento: ICMS, PIS, COFINS, ISS.'
            ],
            [
                'nome' => 'Devoluções de Vendas',
                'tipo' => 'despesa',
                'grupo_dre' => 'deducao_receita',
                'descricao' => 'Notas fiscais de devolução emitidas por clientes.'
            ],
            [
                'nome' => 'Abatimentos e Descontos Concedidos',
                'tipo' => 'despesa',
                'grupo_dre' => 'deducao_receita',
                'descricao' => 'Descontos comerciais e abatimentos concedidos sobre vendas.'
            ],

            // =====================
            // CUSTOS
            // =====================
            [
                'nome' => 'Custo das Mercadorias Vendidas (CMV)',
                'tipo' => 'custo',
                'grupo_dre' => 'custo',
                'descricao' => 'Custos de aquisição de mercadorias revendidas.'
            ],
            [
                'nome' => 'Custo de Produção - Matéria Prima',
                'tipo' => 'custo',
                'grupo_dre' => 'custo',
                'descricao' => 'Compra e consumo de matérias-primas aplicadas na produção.'
            ],
            [
                'nome' => 'Custo de Produção - Mão de Obra Direta',
                'tipo' => 'custo',
                'grupo_dre' => 'custo',
                'descricao' => 'Salários e encargos da equipe diretamente envolvida na produção.'
            ],
            [
                'nome' => 'Custo de Serviços Prestados - Materiais',
                'tipo' => 'custo',
                'grupo_dre' => 'custo',
                'descricao' => 'Materiais aplicados diretamente na execução dos serviços.'
            ],
            [
                'nome' => 'Custo de Serviços Prestados - Mão de Obra',
                'tipo' => 'custo',
                'grupo_dre' => 'custo',
                'descricao' => 'Salários e encargos da equipe que executa os serviços.'
            ],

            // =====================
            // DESPESAS COMERCIAIS
            // =====================
            [
                'nome' => 'Despesa Comercial - Salários',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_venda',
                'descricao' => 'Remuneração e encargos da equipe de vendas.'
            ],
            [
                'nome' => 'Despesa Comercial - Propaganda e Marketing',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_venda',
                'descricao' => 'Gastos com publicidade, promoções e marketing.'
            ],
            [
                'nome' => 'Despesa Comercial - Comissões sobre Vendas',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_venda',
                'descricao' => 'Comissões pagas a vendedores e representantes.'
            ],

            // =====================
            // DESPESAS ADMINISTRATIVAS
            // =====================
            [
                'nome' => 'Despesa Administrativa - Pessoal',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Remuneração da equipe administrativa e gerencial.'
            ],
            [
                'nome' => 'Despesa Administrativa - Aluguel',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Aluguel de imóveis administrativos e operacionais.'
            ],
            [
                'nome' => 'Despesa Administrativa - Energia, Água e Telefonia',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Custos fixos de energia elétrica, água, internet e telefonia.'
            ],
            [
                'nome' => 'Despesa Administrativa - Serviços de Terceiros',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Contabilidade, advocacia, consultoria e outros serviços terceirizados.'
            ],
            [
                'nome' => 'Despesa Administrativa - Tributos/Impostos',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Tributos e encargos não incidentes diretamente sobre vendas, como taxas municipais, federais e estaduais.'
            ],
            [
                'nome' => 'Despesa Administrativa - Software e Sistemas',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_adm',
                'descricao' => 'Licenciamento, contratação de softwares e ERPs.'
            ],

            // =====================
            // DESPESAS FINANCEIRAS
            // =====================
            [
                'nome' => 'Despesa Financeira - Juros Passivos',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_financeira',
                'descricao' => 'Juros pagos em empréstimos, financiamentos e atrasos.'
            ],
            [
                'nome' => 'Despesa Financeira - Tarifas Bancárias',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_financeira',
                'descricao' => 'Taxas de manutenção de contas, DOC, TED, PIX.'
            ],
            [
                'nome' => 'Despesa Financeira - Variações Cambiais Desfavoráveis',
                'tipo' => 'despesa',
                'grupo_dre' => 'despesa_financeira',
                'descricao' => 'Perdas financeiras em operações de câmbio.'
            ],
            [
                'nome' => 'Imposto de Renda e Contribuição Social (IRPJ/CSLL)',
                'tipo' => 'despesa',
                'grupo_dre' => 'imposto_lucro',
                'descricao' => 'Tributação incidente sobre o lucro da empresa, incluindo IRPJ e CSLL.'
            ],

            // =====================
            // RECEITAS FINANCEIRAS
            // =====================
            [
                'nome' => 'Receita Financeira - Juros Ativos',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_financeira',
                'descricao' => 'Receita de juros sobre aplicações financeiras.'
            ],
            [
                'nome' => 'Receita Financeira - Descontos Obtidos',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_financeira',
                'descricao' => 'Descontos recebidos em compras e renegociações.'
            ],
            [
                'nome' => 'Receita Financeira - Variações Cambiais Favoráveis',
                'tipo' => 'receita',
                'grupo_dre' => 'receita_financeira',
                'descricao' => 'Ganhos financeiros em operações de câmbio.'
            ],

            // =====================
            // OUTRAS RECEITAS/DESPESAS
            // =====================
            [
                'nome' => 'Outras Receitas Operacionais',
                'tipo' => 'receita',
                'grupo_dre' => 'outras_receitas',
                'descricao' => 'Venda de sucatas, ganhos não recorrentes operacionais.'
            ],
            [
                'nome' => 'Outras Despesas Operacionais',
                'tipo' => 'despesa',
                'grupo_dre' => 'outras_despesas',
                'descricao' => 'Multas, perdas em inventário, baixas de ativos.'
            ],
            [
                'nome' => 'Receita de Alienação de Ativos',
                'tipo' => 'receita',
                'grupo_dre' => 'outras_receitas',
                'descricao' => 'Venda de imobilizado, investimentos ou intangíveis.'
            ],
            [
                'nome' => 'Perda na Alienação de Ativos',
                'tipo' => 'despesa',
                'grupo_dre' => 'outras_despesas',
                'descricao' => 'Prejuízos registrados na venda de ativos não circulantes.'
            ],

        ];

        DB::table('categoria_contas')->insert($categorias);
    }
}
