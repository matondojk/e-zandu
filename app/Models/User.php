<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use MixCode\FilamentMulti2fa\Traits\UsingTwoFA;
use MixCode\FilamentMulti2fa\Enums\TwoFactorAuthType;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Role;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, UsingTwoFA;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'phone',
        'delivery_address',
        'password',
        'role',
    ];

    protected $guarded = [
        'two_factor_type',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_sent_at',
        'two_factor_expires_at',
        'two_factor_confirmed_at',
    ];

    protected $casts = [
        'two_factor_type' => TwoFactorAuthType::class,
        'two_factor_sent_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'tags' => 'array',
    ];


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_roles');
    }

    // App/Models/User.php (Apenas o método hasPermissionTo e hasRole)

    // Método helper para verificar permissão
    public function hasPermissionTo(string $permissionSlug): bool
    {
        // 1. O 'role' do ENUM é usado para acesso ao painel, não para permissões granulares.
        // Se o usuário não é admin, já foi barrado no canAccessPanel, mas garantimos aqui.
        if ($this->role !== 'admin') {
            return false;
        }

        // 2. Verifica explicitamente se o usuário tem o Perfil de Super Admin ou Gerente (Acesso Total)
        if ($this->hasRole('super-admin') || $this->hasRole('gerente')) {
            return true;
        }

        // Se for o slug 'full-access', mas ele não for Super Admin/Gerente, nega.
        if ($permissionSlug === 'full-access') {
            return false;
        }

        // 3. Verifica se a permissão está associada a algum dos perfis do usuário
        // ✅ Corrigido: Usamos consulta explícita para evitar problemas de caching ou lazy loading
        $rolesWithPermission = $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();

        return $rolesWithPermission;
    }

    // Método helper para verificar se pertence a um Perfil (Melhorado para consulta explícita)
    public function hasRole(string $roleSlug): bool
    {
        // 1. Não usamos o ENUM 'admin' aqui.

        // 2. Verifica se o slug do perfil está associado
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    // Método helper para verificar permissão (Corrigido para uso do hasPermissionTo)
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // Se ele tiver o role 'admin' no ENUM E se ele tiver o perfil 'super-admin' ou 'gerente'
            // O acesso ao painel deve ser menos restrito: se for admin no ENUM, pode aceder.
            // A restrição granular fica para o Middleware.
            return $this->role === 'admin';
        }

        return false;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->avatar
                ? Storage::disk('public')->url($this->avatar)
                : null,
        );
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }


    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_audience');
    }
}
