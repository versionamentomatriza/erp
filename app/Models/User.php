<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [ 
        'name',
        'email',
        'password',
        'imagem',
        'admin',
        'notificacao_cardapio',
        'tipo_contador',
        'notificacao_marketplace',
        'notificacao_ecommerce',
        'empresa_id',
        'documento',
        'atividade',
        'cargo_funcao',
        'qtd_funcionarios',
        'escolher_localidade_venda',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function empresa()
    {
        return $this->hasOne(UsuarioEmpresa::class, 'usuario_id');
    }

    public function empresaSuper()
    {
        return $this->hasMany(Empresa::class, 'nome');
    }

    public function acessos()
    {
        return $this->hasMany(AcessoLog::class, 'usuario_id')->orderBy('id', 'desc');
    }

    public function locais()
    {
        return $this->hasMany(UsuarioLocalizacao::class, 'usuario_id');
    }

    public function getImgAttribute()
    {
        if($this->imagem == ""){
            return "/imgs/no-image.png";
        }
        return "/uploads/usuarios/$this->imagem";
    }
}
