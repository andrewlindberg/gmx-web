<?php
namespace GameX\Core\Auth\Models;

use \GameX\Core\BaseModel;
use \GameX\Models\Player;
use \Cartalyst\Sentinel\Persistences\EloquentPersistence;
use \Cartalyst\Sentinel\Users\UserInterface;
use \Cartalyst\Sentinel\Persistences\PersistableInterface;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserModel
 * @package GameX\Core\Auth\Models
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property array $permissions
 * @property int $role_id
 * @property \DateTime $last_login
 * @property \DateTime $created_at
 * @property \DateTime $update_at
 * @property RoleModel $role
 * @property Player[] players
 */
class UserModel extends BaseModel implements UserInterface, PersistableInterface {

    /**
     * @var string
     */
	protected $table = 'users';

	/**
	 * {@inheritDoc}
	 */
	protected $fillable = [
		'login',
		'email',
		'password',
		'last_name',
		'first_name',
		'permissions',
		'role_id',
	];

	/**
	 * {@inheritDoc}
	 */
	protected $hidden = [
		'password',
	];

	/**
	 * Returns the user primary key.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->id;
	}

	/**
	 * Returns the user login.
	 *
	 * @return string
	 */
	public function getUserLogin() {
		return $this->login;
	}

	/**
	 * Returns the user login attribute name.
	 *
	 * @return string
	 */
	public function getUserLoginName() {
		return 'login';
	}

	/**
	 * Returns the user password.
	 *
	 * @return string
	 */
	public function getUserPassword() {
		return $this->password;
	}

	/**
	 * Returns the persistable key name.
	 *
	 * @return string
	 */
	public function getPersistableKey() {
		return 'user_id';
	}

	/**
	 * Returns the persistable key value.
	 *
	 * @return string
	 */
	public function getPersistableId() {
		return $this->id;
	}

	/**
	 * Returns the persistable relationship name.
	 *
	 * @return string
	 */
	public function getPersistableRelationship() {
		return 'persistences';
	}

	/**
	 * Generates a random persist code.
	 *
	 * @return string
	 */
	public function generatePersistenceCode() {
		return str_random(32);
	}

	/**
	 * Returns the persistences relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function persistences() {
		return $this->hasMany(EloquentPersistence::class, 'user_id');
	}

	/**
	 * @return BelongsTo
	 */
	public function role() {
		return $this->belongsTo(RoleModel::class);
	}
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function players() {
        return $this->hasMany(EloquentPersistence::class, 'user_id', 'id');
    }
}
