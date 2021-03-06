<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\UserVerifyEmail;
use Illuminate\Support\Facades\Config;
use Caffeinated\Shinobi\Concerns\HasRolesAndPermissions;
use Illuminate\Support\Facades\Storage;
use App\SocialProfile;
use App\Position;
use App\Helpers\ApiImages;
use App\Device;
use App\Reaction;
use App\RoleUser;
use App\MembershipRequest;
use App\Notifications;
use App\Notifications\UserResetPassword;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'avatar', 'number_phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'membership' => 'array',
    ];

    /*AGREGAR RESOURCE LINK ATTRIBUTE */
    protected $appends = ['fullname', 'avatar_link'];
    // protected $appends = ['avatar_link', 'fullname', 'basic_service_image_link'];
    public function getAvatarLinkAttribute(){
        return $this->getAvatarApiLink();
    }
    // public function getBasicServiceImageLinkAttribute(){
    //     return $this->getBasicServiceApiLink();
    // }
    public function getFullNameAttribute(){
        return $this->getFullName();
    }


    /**
	 *Filtra un Usuario por su id
	 *
	 * @param  mixed $query
	 * @param  int $id
	 * @return mixed
	 */
	public function scopeFindById($query, $id) {
		return $query->where('id', $id);
    }
    
    /**
	 *Filtra un Usuario activo
	 *
	 * @param  mixed $query
	 * @return mixed
	 */
	public function scopeActiveUser($query) {
		return $query->where('state', 1);
	}

    
    public function getWebSystemRoles(){
        //Se retorna los roles del usuario que pueden acceder al sistema
        return $this->roles()->where('mobile_app', false)->get();
    }

    //Se obtiene un específico rol del usuario
    public function getASpecificRole($roleSlug){
        return $this->roles()->where('slug', $roleSlug)->first();
    }

    //Obtener el estado de la realción entre roles y usuarios
    //Se obtiene el valor de la columna state de la tabla pivote entre roles y usuarios
    public function getRelationshipStateRolesUsers($roleSlug){
        $state = 0;
        $role = $this->roles()->where('slug', $roleSlug)->first();
        if($role){
            $state = $role->pivot->state;
        }
        return $state;
    }

    /**
	 *Filtra un Usuario por su email
	 *
	 * @param  mixed $query
	 * @param  string $email
	 * @return mixed
	 */
	public function scopeEmail($query, string $email) {
		return $query->where('email', $email);
	}

	/**
	 *Filtra los Usuarios de Tipo Directivo
	 *
	 * @param  mixed $query
	 * @return mixed
	 */
	public function scopeRolDirectivo($query) {
		return $query->whereHas('roles', function ($q) {
			$q->where('slug', 'directivo');
		});
    }

    /**
	 *Filtra los Roles de Tipo Movil de un Usuario
	 *
	 * @param  mixed $query
	 * @return mixed
	 */
	public function scopeMobileRol($query) {
		return $query->with(['roles' => function ($query) {
			$query->where('slug', 'morador')
				->orWhere('slug', 'invitado')
				->orWhere('slug', 'policia');
		}]);
	}
    
    /**
	 *Filtra los Usuarios por un rol especifico
	 *
	 * @param  mixed $query
	 * @param  string $rol_slug
	 * @return mixed
	 */
	public function scopeGetBySpecificRol($query, $rol_slug) {
		return $query->whereHas('roles', function ($q) use($rol_slug) {
			$q->where('slug', $rol_slug);
		});
	}
    
    /**
	 *Filtra un Usuario que tenga rol activo
	 *
	 * @param  mixed $query
	 * @return mixed
	 */
	public function scopeRolActive($query) {
		// return $query->where('slug', $slug);
		$active = 1;
		$query->whereHas('roles', function ($query) use ($active) {
			$query->where('state', '=', $active);
        });
        return $query;
	}

    //Se verifica que algún rol del sistema web asignados al usuario se encuentre activo
    public function hasSomeActiveWebSystemRole(){
        $hasSomeActiveRol = false;
        $userRoles = $this->getWebSystemRoles();
        foreach($userRoles as $role){
            if($this->getRelationshipStateRolesUsers($role->slug)){
                $hasSomeActiveRol = true;
            }
        }
        return $hasSomeActiveRol;
    }
    //Se sobrescribe el método sendPasswordNotificatión para cambiar a un nuevo objeto 
    //de la clase UserResetNotification con el contenido de la notificación traducida
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPassword($token));
    }
    //Se sobrescribe el método sendEmailVerificationNotification para cambiar a un nuevo objeto 
    //de la clase UserResetNotification con el contenido de la notificación traducida
    public function sendEmailVerificationNotification(){
        $this->notify(new UserVerifyEmail);
    }
    /*
    *Se obtiene la posición a la que pertenece el usuario
    */
    public function position(){
        return $this->belongsTo(Position::class);
    }

    public function getAvatar(){
        $avatar = $this->avatar;
        if(!$avatar || \starts_with($avatar,'http')){
            return $avatar;
        }
        return \Storage::disk('s3')->url($avatar);
    }

    public function getFullName(){
        $first_name = explode(' ',$this->first_name);
        $last_name = explode(' ',$this->last_name);

        return "$first_name[0] $last_name[0]"; 
    }

    /**
     * Users can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('shinobi.models.role'))
        ->withPivot(['state'])->withTimestamps();
    }
    /**
     * A user can have many posts
     */
    public function posts(){
        return $this->hasMany(Post::class);
    }

    /**
     * A user can have many memberships
     */
    public function memberships(){
        return $this->hasMany(Membership::class);
    }

    /*FUNCIONES EXTRAS */    
    //Obtener los Roles
	public function getRol() {
		return $this->roles()->whereNotIn('slug', ['invitado'])->first();
	}

    /*TODO: RELACIONES MODELOS */
    //Relacion Uno a Muchos para obtener los perfiles sociales por usuario
	public function social_profiles() {
		return $this->hasMany(SocialProfile::class)->orderBy('id', 'DESC');
	}

	//Relacion Uno a Muchos con la Tabla Devices para obtener los dispositivos por cada usuario
	public function devices() {
		return $this->hasMany(Device::class)->orderBy('id', 'DESC');
    }
    
    /**
    * get resource api link
    */
    public function getAvatarApiLink(){
        $imageApi = new ApiImages();
        return $imageApi->getApiUrlLink($this->avatar);
    }

    /**
    * get resource api link
    */
    public function getBasicServiceApiLink(){
        $imageApi = new ApiImages();
        if(isset($this->membership) && isset($this->membership['basic_service_image'])){
            return $imageApi->getApiUrlLink($this->membership['basic_service_image']);
        }else{
            return '';
        }
    }
    /**
     * The mothergoose check. Runs through each scenario provided
     * by Shinobi - checking for special flags, role permissions, and
     * individual user permissions; in that order.
     * 
     * @param  Permission  $permission
     * @return boolean
     */
    //Se sobrescribe la funciones del paquete shinobi
    public function hasPermissionTo($permission): bool
    {
        //Se obtiene los roles que tiene el permiso
        $permission_roles = $permission->roles;
        //USUARIO
        //Se obtiene al usuario que está realizando la petición
        $user = $this;
        //Se obtiene los roles que tiene el usuario
        $user_roles = $user->roles;
        //Se obtienen los roles tanto del usuario y permiso que tienen en común
        $common_roles = $permission_roles->intersect($user_roles);
        if($this->checkRoleState($common_roles, $user)){
            // Check role flags
            if ($this->hasPermissionFlags()) {
                return $this->hasPermissionThroughFlag();
            }

            // Check role permissions
            if ($this->hasPermissionThroughRole($permission)) {
                return true;
            }

            // Check user permission
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
     //Se verifica que de los roles obtenidos, uno de ellos tenga el usuario activado en su relación de rol y usuario
     private function checkRoleState($roles, $user){
        $state = false;
        foreach($roles as $role){
            if($user->getRelationshipStateRolesUsers($role->slug)){
                $state = true;
            }
        }
        return $state;
    }
}
