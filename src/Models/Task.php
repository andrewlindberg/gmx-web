<?php

namespace GameX\Models;

use \GameX\Core\BaseModel;

/**
 * Class Task
 * @package GameX\Models
 *
 * @property integer $id
 * @property string $key
 * @property array $data
 * @property integer $status
 * @property integer $retries
 * @property integer $max_retries
 * @property integer execute_at
 */
class Task extends BaseModel
{
    
    const STATUS_WAITING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_DONE = 3;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = ['key', 'data', 'status', 'retries', 'max_retries', 'execute_at'];
    
    /**
     * @param $value
     * @return mixed|null
     */
    public function getDataAttribute($value)
    {
	    return $value ? json_decode($value, true) : [];
    }

	/**
	 * @param array $value
	 */
	public function setDataAttribute(array $value)
	{
		$this->attributes['data'] = $value ? json_encode($value) : '';
	}
}
