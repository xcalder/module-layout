<?php

namespace ModuleLayout;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use ModuleLayout\Facades\ModuleLayout;

class IndexController extends BaseController
{
    private $server;
    private $validation;
    public function __construct()
    {
        $this->server = new Server();
        $this->validation = new Validation();
    }
    
    /**
     * 取moduleSetting表单
     * @param Request $request
     */
    public function getModuleSettingForm(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->configId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        $config_driver = $this->server->getConfigDriver();
        $driver = $config_driver[$request->input('config_id')];
        echo ModuleLayout::with($driver)->getSettingForm($request);
    }
    
    /**
     * 取模块列表
     * @param Request $request
     */
    public function getModulesList(Request $request){
        $data = [];
        $data['status'] = true;
        $data['data'] = $this->server->getModulesList($request);
        return response()->json($data);
    }
    
    /**
     * 取模块详情
     * @param Request $request
     */
    public function getModule(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->moduleId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $data['status'] = true;
        $data['data'] = $this->server->getModule($request);
        return response()->json($data);
    }
    
    /**
     * 取模块设置列表
     */
    public function getModuleSettingList(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->moduleId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $data['status'] = true;
        $data['data'] = $this->server->getModuleSettingList($request);
        return response()->json($data);
    }
    
    /**
     * 取模块设置详情
     */
    public function getModuleSetting(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->moduleSettingId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->getModuleSetting($request);
        if(!empty($result)){
            $data['status'] = true;
        }
        $data['data'] = $result;
        return response()->json($data);
    }
    
    /**
     * 添加模块
     */
    public function addModule(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->addModule($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addOrUpdateModule($request);
        if($result){
            $data['status'] = true;
        }
        return response()->json($data);
    }
    
    /**
     * 删除模块
     */
    public function delModule(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->moduleId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->delModule($request);
        
        $data = $result;
        
        return response()->json($data);
    }
    
    /**
     * 修改模块
     */
    public function updateModule(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->updateModule($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addOrUpdateModule($request);
        if($result){
            $data['status'] = true;
        }
        return response()->json($data);
    }
    
    /**
     * 添加模块设置
     */
    public function addModuleSetting(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->addModuleSetting($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addOrUpdateModuleSetting($request);
        
        if($result){
            $data['status'] = true;
        }
        
        return response()->json($data);
    }
    
    /**
     * 删除模块设置
     */
    public function delModuleSetting(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->moduleSettingId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->delModuleSetting($request);
        
        $data = $result;
        
        return response()->json($data);
    }
    
    /**
     * 修改模块设置
     */
    public function updateModuleSetting(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->updateModuleSetting($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addOrUpdateModuleSetting($request);
        
        if($result){
            $data['status'] = true;
        }
        
        return response()->json($data);
    }
    
    /**
     * 路由列表
     * @param Request $request
     */
    public function getModuleRouteList(Request $request){
        $data = [];
        $data['status'] = true;
        $data['data'] = $this->server->getModuleRouteList($request);
        return response()->json($data);
    }
    
    /**
     * 路由
     * @param Request $request
     */
    public function getModuleRoute(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->ModuleRouteId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->getModuleRoute($request);
        if(!empty($result)){
            $data['status'] = true;
        }
        $data['data'] = $result;
        return response()->json($data);
    }
    
    /**
     * 添加路由
     * @param Request $request
     */
    public function addModuleRoute(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->addModuleRouteId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addModuleRoute($request);
        $data['status'] = $result;
        return response()->json($data);
    }
    
    /**
     * 删除路由
     * @param Request $request
     */
    public function delModuleRoute(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->ModuleRouteId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->delModuleRoute($request);
        
        $data = $result;
        
        return response()->json($data);
    }
    
    /**
     * 修改路由
     * @param Request $request
     */
    public function updateModuleRoute(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->updateModuleRouteId($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $result = $this->server->addModuleRoute($request);
        $data['status'] = $result;
        return response()->json($data);
    }
    
    /**
     * 修改设置内容
     * @param Request $request
     */
    public function updateSetting(Request $request){
        $data = [];
        $data['status'] =  false;
        
        $result = $this->validation->updateSetting($request);
        if ($result) {
            $data['error'] = $result;
            return response()->json($data);
        }
        
        $config_driver = $this->server->getConfigDriver();
        $driver = $config_driver[$request->input('config_id')] ?? '';
        if(!empty($driver)){
            $data['status'] = ModuleLayout::with($driver)->updateSetting($request);
        }
        
        return response()->json($data);
    }
}
