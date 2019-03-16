<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\Validator;

class Validation
{
    /**
     * Create a new Validation instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function moduleId($request){
            $rules = ['module_id' => 'required'];
        return $this->return($request, $rules);
    }
    
    public function moduleSettingId($request){
        $rules = ['module_setting_id' => 'required'];
        return $this->return($request, $rules);
    }
    
    public function addModule($request){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'code' => 'required',
            'status' => 'required'
        ];
        return $this->return($request, $rules);
    }
    
    public function updateModule($request){
        $rules = [
            'id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'code' => 'required',
            'status' => 'required'
        ];
        return $this->return($request, $rules);
    }
    
    public function addModuleSetting($request){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'module_id' => 'required',
            'status' => 'required',
            'setting' => 'required|array'
        ];
        return $this->return($request, $rules);
    }
    
    public function updateModuleSetting($request){
        $rules = [
            'id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'module_id' => 'required',
            'status' => 'required',
            'setting' => 'required|array'
        ];
        return $this->return($request, $rules);
    }
    
    public function ModuleRouteId($request){
        $rules = [
            'id' => 'required'
        ];
        return $this->return($request, $rules);
    }
    
    public function addModuleRouteId($request){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
            'route' => 'required',
            'modules_setting_to_route' => 'required|array',
            'modules_setting_to_route.*.module_id' => 'required',
            'modules_setting_to_route.*.modules_setting_id' => 'required',
            'modules_setting_to_route.*.layout' => 'required',
        ];
        return $this->return($request, $rules);
    }
    
    public function updateModuleRouteId($request){
        $rules = [
            'id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required',
            'route' => 'required',
            'modules_setting_to_route' => 'required|array',
            'modules_setting_to_route.*.module_id' => 'required',
            'modules_setting_to_route.*.modules_setting_id' => 'required',
            'modules_setting_to_route.*.layout' => 'required',
        ];
        return $this->return($request, $rules);
    }
    
    public function return($request, $rules){
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }
        return false;
    }
}
