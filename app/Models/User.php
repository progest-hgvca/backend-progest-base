<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Setores;
use App\Models\Polo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'telefone',
        'data_nascimento',
        'cpf',
        'status',
        'regime_contratacao_id',
        'usuario_tipo',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_super_admin',
        'is_admin_caf',
        'is_admin_polo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relacionamento com Setores (many-to-many)
     * Um usuário pode pertencer a vários setores
     */
    public function setores()
    {
        // Usar a tabela pivot canônica `usuario_setor` que contém o campo `perfil`
        // Note: a migration cria as colunas `usuario_id` e `setor_id` (não `user_id`)
        return $this->belongsToMany(Setores::class, 'usuario_setor', 'usuario_id', 'setor_id')
            ->withPivot('perfil')
            ->withTimestamps();
    }

    /**
     * Relacionamento com RegimeContratacao
     * Um usuário pertence a um regime de contratação
     */
    public function regimeContratacao()
    {
        return $this->belongsTo(\App\Models\RegimeContratacao::class, 'regime_contratacao_id');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {
            if ($user->email === 'admin@admin.com' || $user->email === 'adminti@gmail.com') {
                throw new \Exception('O usuário Admin não pode ser excluído.');
            }
        });
    }

    /**
     * Usuário super-admin hardcoded (tem todas as permissões)
     * Retorna true se o email for adminti@gmail.com
     */
    public function isSuperAdmin(): bool
    {
        return isset($this->email) && mb_strtolower($this->email) === 'adminti@gmail.com';
    }

    /**
     * Relacionamento com Polos onde o usuário é Admin
     */
    public function polosAdministrados()
    {
        return $this->belongsToMany(Polo::class, 'usuario_polo', 'usuario_id', 'polo_id')
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário é Admin da CAF
     */
    public function isAdminCaf(): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->setores()
            ->where('setores.id', 1) // CAF
            ->wherePivot('perfil', 'admin')
            ->exists();
    }

    /**
     * Verifica se o usuário é Admin de um Polo específico
     */
    public function isAdminPolo($polo_id): bool
    {
        if ($this->isSuperAdmin() || $this->isAdminCaf()) return true;
        return $this->polosAdministrados()->where('polos.id', $polo_id)->exists();
    }

    // ==========================================
    // ACCESSORS (Para uso no Frontend via JSON)
    // ==========================================
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    public function getIsAdminCafAttribute(): bool
    {
        return $this->isAdminCaf();
    }

    public function getIsAdminPoloAttribute(): bool
    {
        // Retorna true se for super admin, admin da caf ou admin de pelo menos um polo
        if ($this->isSuperAdmin() || $this->isAdminCaf()) return true;
        return $this->polosAdministrados()->exists();
    }
}
