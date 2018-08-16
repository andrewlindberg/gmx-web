<?php
namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Server
 * @package GameX\Models
 *
 * @property integer $id
 * @property string $name
 * @property string $ip
 * @property integer $port
 * @property string $token
 * @property string $rcon
 * @property bool $active
 * @property Group[] $groups
 * @property Reason[] $reasons
 */
class Server extends BaseModel {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'servers';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var array
	 */
	protected $fillable = ['name', 'ip', 'port', 'token', 'rcon', 'active'];
    
    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * @var array
     */
    protected $hidden = ['rcon', 'token', 'created_at', 'updated_at'];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups() {
        return $this->hasMany(Group::class, 'server_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reasons() {
        return $this->hasMany(Reason::class, 'server_id');
    }
    
    /**
     * @return string
     */
    public function generateNewToken() {
        $tries = 0;
        do {
            $token = bin2hex(random_bytes(32));
        } while (++$tries < 3 && Server::where('token', $token)->exists());
        return $token;
    }
}
