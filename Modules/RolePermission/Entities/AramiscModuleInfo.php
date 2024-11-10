<?php

namespace Modules\RolePermission\Entities;

use App\AramiscModuleManager;
use Illuminate\Database\Eloquent\Model;

class AramiscModuleInfo extends Model
{
    // protected $fillable = ['*'];
    protected $guarded = ['id'];

    public function subModule(){
        
        return $this->hasMany('Modules\RolePermission\Entities\AramiscModuleInfo','parent_route','route')
        ->whereNotNull('route')->where('route', '!=', '')
        ->whereNotInDeaActiveModulePermission()
        ->where('active_status', 1);
    }

    public function children(){
        return $this->hasMany('Modules\RolePermission\Entities\AramiscModuleInfo','parent_id','id');
    }

    public function allGroupModule(){
        return $this->subModule()->where('id','!=',$this->module_id);
    }
    public function scopeWhereNotInDeaActiveModulePermission($query)
    {        
        $activeModuleList = AramiscModuleManager::where('is_default', 0)
        ->whereNull('purchase_code')->pluck('name')->toArray();
          
        $deActiveModules = [];            
        foreach($activeModuleList as $module) {
            if(moduleStatusCheck($module)==false) {
                $deActiveModules[] = $module;
            }
        }
        return $query->where(function($q) use($deActiveModules) {
          $q->whereNotIn('module_name', $deActiveModules)->orWhereNull('module_name');           
        });
    }
    public function roles()
    {
        return $this->belongsToMany(AramiscRole::class, 'aramisc_permission_assigns', 'module_id', 'role_id');
    }
    public function assign()
    {
        return $this->hasMany(AramiscPermissionAssign::class, 'role_id', 'id');
    }

    public function childs()
    {
        return $this->hasMany(AramiscModuleInfo::class, 'parent_route', 'route')->with('childs');
    }

    public function parent()
    {
        return $this->belongsTo(AramiscModuleInfo::class, 'parent_route', 'route');
    }
}
