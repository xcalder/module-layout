<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ModuleLayout\Models\Modules;
use ModuleLayout\Models\ModulesSetting;
use ModuleLayout\Models\ModulesSettingToRoute;
use ModuleLayout\Models\ModulesRoute;

class Server
{
    public function getModulesList($request){
        $result = Modules::paginate(env('PAGE_LIMIT', 25))->toArray();
        if(!empty($result['data'])){
            $general_status = config('all_status.general_status');
            foreach ($result['data'] as $key=>$value){
                $result['data'][$key]['status_text'] = $general_status[$value['status']];
            }
        }
        return $result;
    }
    
    public function getModuleSettingList($request){
        $result = ModulesSetting::where('module_id', $request->input('module_id'))->paginate(env('PAGE_LIMIT', 25))->toArray();
        return $result;
    }
    
    public function getModuleSetting($request){
        $result = ModulesSetting::find($request->input('module_setting_id'));
        return $result;
    }
    
    public function addOrUpdateModule($request){
        $return = false;
        
        $data = [];
        if($request->has('title')){
            $data['title'] = $request->input('title');
        }
        
        if($request->has('description')){
            $data['description'] = $request->input('description');
        }
        
        if($request->has('code')){
            $data['code'] = $request->input('code');
        }
        
        if($request->has('status')){
            $data['status'] = $request->input('status');
        }
        
        if($request->has('config_id')){
            $this->getConfig();
            if(!in_array($request->input('config_id'), $this->config_ids)){
                return false;
            }
            $data['config_id'] = $request->input('config_id');
        }
        
        $model_module = new Modules();
        $result = false;
        if($request->has('id')){
            $result = $model_module->where('id', $request->input('id'))->update($data);
        }else{
            $user = Auth::user();
            $store_id = $user['store_id'] ?? 0;
            $data['store_id'] = $store_id;
            $result = $model_module->insert($data);
        }
        
        if($result){
            $return = true;
        }
        return $return;
    }
    
    public function delModule($request){
        $data = [];
        $data['status'] = false;
        $count_module_setting = ModulesSetting::where('module_id', $request->input('id'))->count();
        
        if($count_module_setting > 0){
            $data['error'] = '此模块下还有'.$count_module_setting.'个设置，不能删除';
            return $data;
        }
        
        DB::beginTransaction();
        try{
            $module_id = $request->input('id');
            Modules::where('id', $module_id)->delete();
            ModulesSetting::where('module_id', $module_id)->delete();
            ModulesSettingToRoute::where('module_id', $module_id)->delete();
            $data['status'] = true;
            DB::commit();
        }catch (\Exception $e){
            $data['error'] = '删除失败';
            DB::rollBack();
        }
        
        return $data;
    }
    
    public function addOrUpdateModuleSetting($request){
        $return = false;
        
        $data = [];
        $data['title'] = $request->input('title');
        $data['description'] = $request->input('description');
        $data['module_id'] = $request->input('module_id');
        $data['setting'] = serialize($request->input('setting'));
        $data['status'] = $request->input('status');
        
        $model_module_setting = new ModulesSetting();
        $result = false;
        if($request->has('id')){
            $result = $model_module_setting->where('id', $request->input('id'))->update($data);
        }else{
            $user = Auth::user();
            $store_id = $user['store_id'] ?? 0;
            $data['store_id'] = $store_id;
            $result = $model_module_setting->insert($data);
        }
        if($result){
            $return = true;
        }
        
        return $return;
    }
    
    public function delModuleSetting($request){
        $data = [];
        $data['status'] = false;
        
        DB::beginTransaction();
        try{
            $module_setting_id = $request->input('module_setting_id');
            ModulesSetting::where('id', $module_setting_id)->delete();
            ModulesSettingToRoute::where('modules_setting_id', $module_setting_id)->delete();
            $data['status'] = true;
            DB::commit();
        }catch (\Exception $e){
            $data['error'] = '删除失败';
            DB::rollBack();
        }
        
        return $data;
    }
    
    public function getModuleRouteList($request){
        $result = ModulesRoute::paginate(env('PAGE_LIMIT', 25))->toArray();
        return $result;
    }
    
    public function getModuleRoute($request){
        $result = ModulesRoute::find($request->input('id'));
        return $result;
    }
    
    public function addModuleRoute($request){
        $return = false;
        
        $data = [];
        $data['title'] = $request->input('title');
        $data['description'] = $request->input('description');
        $data['route'] = $request->input('route');
        $data['status'] = $request->input('status');
        
        $modules_setting_to_route = $request->input('modules_setting_to_route');
        
        $model_module_route = new ModulesRoute();
        $id = 0;
        $user = Auth::user();
        $store_id = $user['store_id'] ?? 0;
        DB::beginTransaction();
        try{
            if($request->has('id')){
                $id = $request->input('id');
                $result = $model_module_route->where('id', $id)->update($data);
            }else{
                $data['store_id'] = $store_id;
                $id = $model_module_route->insertGetId($data);
            }
            
            foreach ($modules_setting_to_route as $key=>$value){
                $modules_setting_to_route[$key]['store_id'] = $store_id;
                $modules_setting_to_route[$key]['route_id'] = $id;
            }
            ModulesSettingToRoute::where('route_id', $id)->delete();
            ModulesSettingToRoute::insert($modules_setting_to_route);
            $return = true;
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }
        
        return $return;
    }
    
    public function delModuleRoute($request){
        $data = [];
        $data['status'] = false;
        
        DB::beginTransaction();
        try{
            $module_route_id = $request->input('id');
            ModulesRoute::where('id', $module_route_id)->delete();
            ModulesSettingToRoute::where('route_id', $module_route_id)->delete();
            $data['status'] = true;
            DB::commit();
        }catch (\Exception $e){
            $data['error'] = '删除失败';
            DB::rollBack();
        }
        
        return $data;
    }
    
    private $config_ids = [];
    private function getConfig(){
        $config = config('all_status.modules');
        foreach ($config as $key=>$value){
            $this->config_ids[] = $value['id'];
        }
    }
}
