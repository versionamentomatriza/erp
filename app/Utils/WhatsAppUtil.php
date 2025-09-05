<?php

namespace App\Utils;

use Illuminate\Support\Str;
use App\Models\ConfiguracaoSuper;

class WhatsAppUtil
{

	public function sendMessage($numero, $mensagem, $empresa_id, $file = null){
		$nodeurl = 'https://api.criarwhats.com/send';

		$config = ConfiguracaoSuper::first();
		if($config == null){
			return false;
		}

		if($config->token_whatsapp == null){
			return false;
		}
		
		$data = [
			'receiver'  => $numero,
			'msgtext'   => $mensagem,
			'token'     => $config->token_whatsapp,
		];

		if($file != null){
			$data['mediaurl'] = $file;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_URL, $nodeurl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

}