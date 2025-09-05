<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Valida dados básicos
        $request->validate([
            'documento' => [
                'required',
                'min:18',
                'regex:/^(\d{3}\.\d{3}\.\d{3}\-\d{2}|\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2})$/'
            ],
            'email' => 'required|email|unique:users,email',
            'telefone' => 'required|regex:/^\(\d{2}\) \d{5}-\d{4}$/',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&/#]).+$/'
            ],
            'termos' => 'accepted',
            'g-recaptcha-response' => 'required',
        ], [
            'documento.required' => 'O campo CPF/CNPJ é obrigatório.',
            'documento.max' => 'O número do documento deve conter no máximo 18 caracteres.',
            'documento.regex' => 'O documento deve estar no formato de CPF ou CNPJ.',
            'email.required' => 'Informe seu email.',
            'email.email' => 'Informe um email válido.',
            'email.unique' => 'Este email já está em uso.',
            'telefone.required' => 'Informe seu telefone.',
            'telefone.regex' => 'O telefone deve estar no formato (99) 99999-9999.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'As senhas não conferem.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.regex' => 'A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais.',
            'termos.accepted' => 'Você deve aceitar os termos.',
            'g-recaptcha-response.required' => 'Por favor, confirme que você não é um robô.',
        ]);

        // Valida token reCAPTCHA via API Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
        ]);

        $recaptchaData = $response->json();

        if (!$recaptchaData['success']) {
            return redirect()->back()
                ->withErrors(['g-recaptcha-response' => 'Falha na validação do reCAPTCHA. Tente novamente.'])
                ->withInput();
        }

        // Se passou em todas as validações
        // Aqui você já pode criar o usuário no banco
        // User::create([...]);

        return redirect()->route('login')->with('success', 'Cadastro realizado com sucesso!');
    }

    private function validaCPF($cpf)
    {
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private function validaCNPJ($cnpj)
    {
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;
        $tamanho = 12;
        $numeros = substr($cnpj, 0, $tamanho);
        $digitos = substr($cnpj, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) $pos = 9;
        }
        $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        if ($resultado != $digitos[0]) return false;
        $tamanho++;
        $numeros = substr($cnpj, 0, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;
        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) $pos = 9;
        }
        $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        return $resultado == $digitos[1];
    }
}
