<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'atividade' => ['required', 'int'],
                'qtd_funcionarios' => ['required', 'int'],
                'cargo_funcao' => ['required', 'string', 'max:255'],

                // 🔎 Aqui validamos CPF/CNPJ corretamente
                'documento' => [
                    'required',
                    'max:18',
                    'unique:users,documento',
                    function ($attribute, $value, $fail) {
                        $numero = preg_replace('/\D/', '', $value);

                        if (strlen($numero) === 11) {
                            if (!$this->validaCPF($numero)) {
                                $fail('O CPF informado não é válido.');
                            }
                        } elseif (strlen($numero) === 14) {
                            if (!$this->validaCNPJ($numero)) {
                                $fail('O CNPJ informado não é válido.');
                            }
                        } else {
                            $fail('O documento deve ser um CPF ou CNPJ válido.');
                        }
                    },
                ],
            ],
            [
                'password.min' => 'minímo de 6 caracteres',
                'password.confirmed' => 'senhas não coencidem',
                'email.unique' => 'email já utilizado',
                'documento.unique' => 'documento já utilizado',
            ]
        );
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'documento' => $data['documento'],
            'atividade' => $data['atividade'],
            'qtd_funcionarios' => $data['qtd_funcionarios'],
            'cargo_funcao' => $data['cargo_funcao'],
        ]);

        if ($data['email'] == env("MAILMASTER")) {
            $user->assignRole('gestor_plataforma');
        } else {
            $user->assignRole('admin');
        }

        return $user;
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
