<?php

namespace ModuleLayout\Models;

use Illuminate\Database\Eloquent\Model;

class ModulesRoute extends Model
{
    protected $table = 'modules_route';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        
    ];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];
    
    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;
    
    
    public function modulesSettingToRoute()
    {
        return $this->hasMany('ModuleLayout\Models\ModulesSettingToRoute', 'route_id', 'id');
    }
}
