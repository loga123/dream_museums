<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;


class User extends Authenticatable implements JWTSubject , MustVerifyEmail
{
    use Notifiable,
        HasFactory,
        HasRoles;

    protected $guard_name = 'api';//za ovlasti korisnika da ne gleda prvu web.php nego api.php

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        /*'photo_url',*/
        'permission' //dodaje se kako bi korisnik povukao ovlasti preko rola...
    ];

    /**
     * @var string[]
     * kad se dohvaca korisnik dohvati osnovnne informacije + njegove ovlasti...
     */
    protected $with = ['permissions','roles'];

    public function getPermissionAttribute(){
        return $this->getAllPermissions();
    }

    /**
     * Get the profile photo URL attribute.
     *
     * @return string
     */
    /*public function getPhotoUrlAttribute()
    {
        return vsprintf('https://www.gravatar.com/avatar/%s.jpg?s=200&d=%s', [
            md5(strtolower($this->email)),
            $this->name ? urlencode("https://ui-avatars.com/api/$this->name") : 'mp',
        ]);
    }*/
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * Funkcija dohvaca sve ovlasti korisnika i many to may tablice i dohvaca samo ime ovlasti jer drugi atributi trenutno nisu potrebni
     *
     */
    public function permission()
    {
        return $this->belongsToMany(Permission::class,'model_has_permissions','model_id','permission_id')->select('name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * Funkcija dohvaca sve razine korisnika(korisnik,admin,itd,,,)
     */
    public function role()
    {
        return $this->belongsToMany(Role::class,'model_has_roles','model_id','role_id');
    }

    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function markers(){
        return $this->hasMany(Marker::class);
    }

    public function groups(){
        return $this->hasMany(Group::class);
    }

    /**
     * Fukncika koja dohvaca sve ovlasti kroisnika i stavlja ih u array, zatim se u datoteci spa.blade.vue staviu ova funkcija
     * @auth
     *    window.Permissions = {!! json_encode(Auth::user()->allPermissions, true) !!};
     * @else
    *       window.Permissions = [];
     *@endauth
     *
     *
     * te u datoteci Permmisions.vue se stvori funkcija $can....
     *
     * Ovva funkcija zamjenjena je sa metodom with jer kad se dohvaca korisnik da se odmah dohvate i njegove ovlasti
     */
   /* public function getAllPermissionsAttribute() {
        $permissions = [];
        Log::alert(json_encode($this->permission));
        foreach ($this->permission as $p) {
                $permissions[] = $p->name;

        }
        return $permissions;
    }*/
}
