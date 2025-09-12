<?php

namespace App\Services;

use DateTime;

class OfxService
{
    public static function parse($ofxContent)
    {
        // Garante que está em UTF-8
        $ofxContent = mb_convert_encoding($ofxContent, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

        // Normaliza quebras de linha
        $ofxContent = str_replace(["\r\n", "\r"], "\n", $ofxContent);

        // Extrai código e nome do banco
        preg_match('/<BANKID>(.*?)\n/i', $ofxContent, $mBankId);
        preg_match('/<FID>(.*?)\n/i', $ofxContent, $mFid);
        preg_match('/<ORG>(.*?)\n/i', $ofxContent, $mOrg);

        $codigoBanco = $mBankId[1] ?? ($mFid[1] ?? null);

        // Extrai transações
        $transactions = self::parseTransactionsRaw($ofxContent);

        // Ordena transações por data
        $transactions = array_filter($transactions, fn($t) => !empty($t['data']));
        usort($transactions, fn($a, $b) => strcmp($a['data'], $b['data']));

        $dataInicio = $transactions[0]['data'] ?? null;
        $dataFim    = end($transactions)['data'] ?? null;

        // Saldo final
        preg_match('/<LEDGERBAL>.*?<BALAMT>(.*?)\n/is', $ofxContent, $mSaldo);
        $saldoFinal = isset($mSaldo[1]) ? (float) str_replace(',', '.', $mSaldo[1]) : null;

        // 🔹 injeta banco em cada transação
        foreach ($transactions as &$t) $t['banco'] = self::nomeBancoPorCodigo($codigoBanco);
        unset($t);

        return [
            'transacoes'  => $transactions,
            'saldoFinal'  => $saldoFinal,
            'dataInicio'  => $dataInicio,
            'dataFim'     => $dataFim,
        ];
    }

    private static function parseTransactionsRaw($ofxContent)
    {
        // isola só a parte da lista de transações
        if (!preg_match('/<BANKTRANLIST>(.*?)<\/BANKTRANLIST>/is', $ofxContent, $matches)) {
            return [];
        }
        $block = $matches[1];

        // pega cada STMTTRN
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/is', $block, $txns);

        $transactions = [];
        foreach ($txns[1] as $txn) {
            preg_match('/<TRNTYPE>\s*([^\r\n<]*)/i', $txn, $m1);
            preg_match('/<DTPOSTED>\s*([^\r\n<]*)/i', $txn, $m2);
            preg_match('/<TRNAMT>\s*([^\r\n<]*)/i', $txn, $m3);
            preg_match('/<FITID>\s*([^\r\n<]*)/i', $txn, $m4);
            preg_match('/<MEMO>\s*([^\r\n<]*)/i', $txn, $m5);

            $valor   = isset($m3[1]) && $m3[1] !== '' ? (float) str_replace(',', '.', $m3[1]) : null;
            $trntype = isset($m1[1]) ? strtoupper(trim($m1[1])) : null;

            // Normaliza SEMPRE pelo sinal do valor
            $tipo = null;
            if ($valor !== null) {
                $tipo = $valor < 0 ? 'DEBIT' : 'CREDIT';
            } else {
                // fallback (quase nunca necessário, mas fica resiliente)
                $creditTypes = ['CREDIT', 'DIRECTDEP', 'DEPOSIT', 'INT', 'DIV', 'PAYROLL'];
                $debitTypes  = ['DEBIT', 'PAYMENT', 'POS', 'CHECK', 'WITHDRAWAL', 'ATM', 'FEE', 'DIRECTDEBIT', 'SRVCHG', 'XFER'];
                if ($trntype && in_array($trntype, $creditTypes)) $tipo = 'CREDIT';
                elseif ($trntype && in_array($trntype, $debitTypes)) $tipo = 'DEBIT';
            }

            $transactions[] = [
                'id'          => $m4[1] ?? null,
                'tipo'        => $tipo,
                'data'        => self::parseDate($m2[1] ?? null),
                'valor'       => $valor,
                'descricao'   => $m5[1] ?? null,
            ];
        }

        return $transactions;
    }

    private static function parseDate($rawDate)
    {
        if (!$rawDate) return null;

        // Remove espaços extras
        $rawDate = trim($rawDate);

        // Remove timezone (ex: [-3:GMT])
        $rawDate = preg_replace('/\[.*?\]/', '', $rawDate);

        // Remove caracteres não numéricos
        $rawDate = preg_replace('/[^\d]/', '', $rawDate);

        // Possíveis formatos
        $formats = ['YmdHis', 'Ymd'];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $rawDate);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private static function nomeBancoPorCodigo($codigo): string
    {
        if (!$codigo) {
            return "Banco desconhecido";
        }

        // Normaliza sempre para 3 dígitos
        $codigo = str_pad((int) $codigo, 3, '0', STR_PAD_LEFT);

        $map = [
            '001' => 'Banco do Brasil',
            '033' => 'Santander',
            '104' => 'Caixa Econômica Federal',
            '237' => 'Bradesco',
            '341' => 'Itaú',
            '077' => 'Banco Inter',
            '260' => 'Nubank',
            '290' => 'PagBank',
            '746' => 'Banco Modal',
        ];

        return $map[$codigo] ?? "Banco desconhecido ({$codigo})";
    }
}
