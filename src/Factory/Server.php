<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ModuleLayout\Models\Modules;
use ModuleLayout\Models\ModulesSetting;
use ModuleLayout\Models\ModulesSettingToRoute;
use ModuleLayout\Models\ModulesRoute;
use ModuleLayout\Facades\ModuleLayout;

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
    
    public function getModule($request){
        $result = Modules::find($request->input('module_id'));
        
        return $result;
    }
    
    public function getModuleSettingList($request){
        $result = ModulesSetting::join('modules as m', function($join){
            $join->on('m.id', '=', 'modules_setting.module_id');
        });
        
        if($request->has('module_id')){
            $result = $result->where('m.id', $request->input('module_id'));
        }
        
        $result = $result->where('modules_setting.status', 1)->select(['m.title as module_title', 'modules_setting.id', 'modules_setting.module_id','modules_setting.title', 'modules_setting.description', 'modules_setting.setting', 'modules_setting.status', 'modules_setting.tag'])->paginate($request->input('offset', env('PAGE_LIMIT', 25)))->toArray();
        $general_status = config('all_status.general_status');
        if(!empty($result['data'])){
            foreach ($result['data'] as $key=>$value){
                $result['data'][$key]['text_status'] = $general_status[$value['status']];
            }
        }
        return $result;
    }
    
    public function getModuleSetting($request){
        $result = ModulesSetting::find($request->input('module_setting_id'));
        if(!empty($result)){
            $config_driver = $this->getConfigDriver();
            $driver = $config_driver[$request->input('config_id')];
            $setting = ModuleLayout::with($driver)->doWithSetting(unserialize($result['setting']) ?? []);
            $result['setting'] = $setting;
        }
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
        $module_id = $request->input('module_id');
        $count_module_setting = ModulesSetting::where('module_id', $module_id)->count();
        
        if($count_module_setting > 0){
            $data['error'] = '此模块下还有'.$count_module_setting.'个设置，不能删除';
            return $data;
        }
        
        DB::beginTransaction();
        try{
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
        //$data['setting'] = serialize($request->input('setting'));
        $data['status'] = $request->input('status');
        $data['tag'] = $request->input('tag');
        $data['limit'] = $request->input('limit', 8);
        $data['show_tag'] = $request->input('show_tag', 0);
        $data['code'] = $request->input('code', 0);
        
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
        $result = ModulesRoute::with(['modulesSettingToRoute'])->find($request->input('id'));
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
            $modules_setting_ids = array_column($modules_setting_to_route, 'modules_setting_id');
            $module_setting = ModulesSetting::whereIn('id', $modules_setting_ids)->select(['id', 'module_id'])->get()->toArray();
            $module_setting = array_under_reset($module_setting, 'id');
            
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
                $modules_setting_to_route[$key]['module_id'] = $module_setting[$value['modules_setting_id']]['module_id'];
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
    private $config_driver = [];
    private function getConfig(){
        $config = config('all_status.modules');
        foreach ($config as $key=>$value){
            $this->config_ids[] = $value['id'];
            $this->config_driver[$value['id']] = $key;
        }
    }
    
    public function getConfigDriver(){
        $this->getConfig();
        return $this->config_driver;
    }
    
    public function getModuleSettingToView($request){
        $html = '';
        $drivers = $this->getConfigDriver();
        $module_setting = ModulesSetting::join('modules as m', function($join){
            $join->on('m.id', '=', 'modules_setting.module_id');
        })->join('modules_setting_to_route as mstr', function($join){
            $join->on('mstr.modules_setting_id', '=', 'modules_setting.id');
        })->select(['m.config_id', 'modules_setting.id', 'modules_setting.tag', 'modules_setting.setting', 'modules_setting.limit', 'modules_setting.show_tag', 'modules_setting.code', 'mstr.layout'])->where('modules_setting.status', 1)->where('modules_setting.id', $request->input('id', 0))->where('mstr.id', $request->input('modules_setting_to_route_id', 0))->first();
        $config_id = $module_setting['config_id'] ?? 0;
        $driver = $drivers[$config_id] ?? 0;
        if(empty($driver)){
            return $html;
        }
        $html = ModuleLayout::with($driver)->viewHtml($module_setting);
        return $html;
    }
    
    public function getRouteToView($request){
        $modules_settings = false;
        $route = $request->input('route', '');
        if(empty($route)){
            return $result;
        }
        
        $result = $this->getModulesRoutes($request);
        
        if(!empty($result)){
            $modules_settings = [];
            $i = 0;
            foreach ($result as $value){
                $layout = '';
                if(!empty($value['modules_setting_to_route'])){
                    foreach ($value['modules_setting_to_route'] as $v){
                        $modules_settings[$v['layout']][$i]['url'] = url('api/modules/get_module_setting_view?api_token='.$request->input('api_token').'&id='.$v['id'].'&modules_setting_to_route_id='.$v['modules_setting_to_route_id']);
                        $modules_settings[$v['layout']][$i]['sort_order'] = $v['sort_order'];
                        $i++;
                    }
                }
                $i++;
            }
            foreach ($modules_settings as $key=>$value){
                $layout_modules = twoDimensionalArraySort($value, 'sort_order');
                $modules_settings[$key] = array_column($layout_modules, 'url');
            }
        }
        
        return $modules_settings;
    }
    
    private function getModulesRoutes($request){
        $route = $request->input('route', '');
        $routes = explode('/', $route);
        $routes = array_filter($routes);
        
        $modules_routes = ModulesRoute::with(['modulesSettingToRoute'=>function($query){
            $query->join('modules_setting as ms', function($join){
                $join->on('modules_setting_to_route.modules_setting_id', '=', 'ms.id');
            });
            $query->join('modules as m', function($join){
                $join->on('m.id', '=', 'ms.module_id');
            });
            $query->where('ms.status', 1);
            $query->select(['m.config_id', 'ms.id', 'modules_setting_to_route.layout', 'modules_setting_to_route.route_id', 'modules_setting_to_route.id as modules_setting_to_route_id', 'modules_setting_to_route.sort_order'])->orderBy('modules_setting_to_route.sort_order', 'desc');
        }]);
        $i = 0;
        $count_routes = count($routes);
        $route_ = '/';
        foreach ($routes as $key=>$route){
            $like = $route_.$route;
            if($i < $count_routes - 1){
                $like .= '/*';
            }
            $like .= '%';
            
            if($i == 0){
                $modules_routes = $modules_routes->where('route', 'like', $like);
            }else{
                $modules_routes = $modules_routes->orWhere('route', 'like', $like);
            }
            $route_ = $route_.$route.'/';
            $i++;
        }
        $modules_routes = $modules_routes->orWhere('route', 'like', '/*%');
        
        return $modules_routes = $modules_routes->select('id')->get()->toArray();
    }
    
    /**
     * 处理商品到html
     * @param unknown $products
     */
    public function setProductHtml($products, $layout = 1){
        $html = '';
        $col_num = 3;
        if(in_array($layout, [3, 4])){
            $col_num = 12;
        }
        if(!empty($products['data'])){
            foreach ($products['data'] as $product){
                $thumb_img = $product['thumb_img'];
                $title = $product['title'];
                $description = $product['description'];
                $min_price = $product['min_price'];
                $price = $product['price'];
                $sales_volume = $product['sales_volume'];
                $unit_code = $product['unit_code'];
                $count_commont = $product['count_commont'];
                $url = url('product/product_info?product_id='.$product['id']);
                $type = '';
                if($product['type'] == 1){
                    $type = '<span class="tag-ico bg-blue mr-1">套餐</span>';
                }
                $activitys = '';
                if(!empty($product['activitys'])){
                    foreach ($product['activitys'] as $key=>$value){
                        $activitys .= '<span class="tag-ico mr-1" style="background-color:'.$value['tag_type'].'">'.$value['tag'].'</span>';
                    }
                }
                $html .= <<<ETO
                    <div class="col-md-$col_num p-0">
                        <div class="thumbnail mb-0 border-0">
                            <a target="_blank" href="$url">
                                <img src="$thumb_img" alt="$title">
                            </a>
                            <div class="caption h-158">
                                <p class="m-0 one-row">
                                    <a target="_blank" href="$url">$title</a>
                                </p>
                                <p class="text-muted m-0 two-row">$description</p>
                                <p><span class="price">￥$min_price</span><del class="ml-3 text-muted">￥$price</del></p>
                                <p><span>销量:$sales_volume$unit_code</span><span class="pull-right">评论:$count_commont</span></p>
                                <p class="mb-0 inline-block"><a target="_blank" href="$url" class="activity-span" style="width: 120px">$type$activitys</a><span class="a pull-right"><i class="glyphicon glyphicon-comment"></i></span></p>
                    </div>
                </div>
            </div>
ETO;
            }
        }
        return $html;
    }
}
