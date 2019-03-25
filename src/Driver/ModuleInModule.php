<?php

namespace ModuleLayout;

use ModuleLayout\Facades\ModuleLayout;
use ModuleLayout\Models\ModulesSetting;

class ModuleInModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $setting = unserialize($setting['setting']);
        $moduleInModules = $setting['moduleInModules'];
        $left = $moduleInModules['left'] ?? [];
        $middle = $moduleInModules['middle'] ?? [];
        $right = $moduleInModules['right'] ?? [];
        $html .= '<div class="row">';
        if(!empty($left)){
            $html .= '<div class="col-md-3">';
            $left_html = self::doWithModule($left, 'left');
            $html .= $left_html;
            $html .= '</div>';
        }
        if(!empty($right)){
            $right_html = self::doWithModule($right, 'right');
        }
        if(!empty($middle)){
            $col_num = 12;
            if(!empty($left_html ?? []) || !empty($right_html ?? [])){
                $col_num = 9;
            }
            if(!empty($left_html ?? []) && !empty($right_html ?? [])){
                $col_num = 6;
            }
            $html .= '<div class="col-md-'.$col_num.'">';
            $html .= self::doWithModule($middle, 'middle');
            $html .= '</div>';
        }
        if(!empty($right_html ?? '')){
            $html .= '<div class="col-md-3">';
            $html .= $right_html;
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * 处理setting
     * @param unknown $setting
     */
    public static function doWithSetting($setting){
        return $setting;
    }
    
    /**
     * 更新设置
     * @param unknown $request
     */
    public static function updateSetting($request){
        $return = false;
        if($request->has('setting')){
            $setting = $request->input('setting');
            foreach ($setting['moduleInModules'] as $key=>$value){
                foreach ($value as $k=>$v){
                    $id = $v['id'] ?? 0;
                    if(empty($id)){
                        unset($setting['moduleInModules'][$key][$k]);
                    }
                }
            }
            
            if(ModulesSetting::where('id', $request->input('module_setting_id'))->update(['setting' => serialize($setting)])){
                $return = true;
            }
        }
        return $return;
    }
    
    /**
     * 模块设置表单
     * @param unknown $request
     */
    public static function getSettingForm($request){
        $update_setting = url('/api/modules/upate_setting?api_token='.$request->input('api_token'));
        $config_id = $request->input('config_id');
        $module_setting_id = $request->input('module_setting_id');
        $moduleInModules = ModulesSetting::select(['modules_setting.id', 'modules_setting.title'])->get()->toArray();
        $moduleInModules = array_under_reset($moduleInModules, 'id');
        unset($moduleInModules[$module_setting_id]);
        
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting = unserialize($module_setting['setting']);
        $setting_moduleInModules = $setting['moduleInModules'] ?? [];
        
        $left_module = $setting_moduleInModules['left'] ?? [];
        $middle_module = $setting_moduleInModules['middle'] ?? [];
        $right_module = $setting_moduleInModules['right'] ?? [];
        if(!empty($left_module)){
            foreach ($left_module as $key=>$value){
                $id = $value['id'] ?? 0;
                $module = $moduleInModules[$id] ?? [];
                if(!empty($module)){
                    $moduleInModules[$id]['left_checked'] = true;
                    $moduleInModules[$id]['left_sort_order'] = $value['sort_order'] ?? 0;
                }
            }
        }
        if(!empty($middle_module)){
            foreach ($middle_module as $key=>$value){
                $id = $value['id'] ?? 0;
                $module = $moduleInModules[$id] ?? [];
                if(!empty($module)){
                    $moduleInModules[$id]['middle_checked'] = true;
                    $moduleInModules[$id]['middle_sort_order'] = $value['sort_order'] ?? 0;
                }
            }
        }
        if(!empty($right_module)){
            foreach ($right_module as $key=>$value){
                $id = $value['id'] ?? 0;
                $module = $moduleInModules[$id] ?? [];
                if(!empty($module)){
                    $moduleInModules[$id]['right_checked'] = true;
                    $moduleInModules[$id]['right_sort_order'] = $value['sort_order'] ?? 0;
                }
            }
        }
        
        $html_left = self::moduleInModuleCheckboxHtml('left', $moduleInModules);
        $html_middle = self::moduleInModuleCheckboxHtml('middle', $moduleInModules);
        $html_right = self::moduleInModuleCheckboxHtml('right', $moduleInModules);
        
        echo <<<ETO
            <div class="modal fade moduleInModule-module-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="row">
                        <form id="select-moduleInModule-form" method="post" enctype="multipart/form-data" action="$update_setting">
                            <div class="col-md-4">
                                <p>左侧</p>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>模块</td>
                                            <td>排序</td>
                                        </tr>
                                    </thead>
                                    <tbody>$html_left</tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <p>中间</p>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>模块</td>
                                            <td>排序</td>
                                        </tr>
                                    </thead>
                                    <tbody>$html_middle</tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <p>右侧</p>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>模块</td>
                                            <td>排序</td>
                                        </tr>
                                    </thead>
                                    <tbody>$html_right</tbody>
                                </table>
                            </div>
                          <input type="hidden" name="config_id" value="$config_id">
                          <input type="hidden" name="module_setting_id" value="$module_setting_id">
                          <button type="submit" class="btn btn-default pull-right mr-3">提交</button>
                        </form>
                     </div>
                </div>
              </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.moduleInModule-module-modal').modal();
                })

                //提交表单
                var options = {
                		   beforeSubmit: showRequest,
                		   success: showResponse,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#select-moduleInModule-form').ajaxForm(options);
                
                function showRequest(formData, jqForm, options){
                	return true;
                };  
                
                function showResponse(responseText, statusText){
                	var data = responseText;
                    if(data.status){
                        toastr.success('修改成功');
                        $('.moduleInModule-module-modal').modal('hide');
                    }else{
                        toastr.warning('修改失败');
                    }
                }
            </script>
ETO;
    }
        
    private static function moduleInModuleCheckboxHtml($layout, $modules){
        $html = '';
        foreach ($modules as $key=>$value){
            $id = $value['id'];
            $checked = ($value["$layout".'_checked'] ?? false) == true ? 'checked' : '';
            $sort_order = $value["$layout".'_sort_order'] ?? 0;
            $title = $value['title'];
            $html .= <<<ETO
                <tr>
                    <td>
                        <input type="checkbox" name="setting[moduleInModules][$layout][$key][id]" value="$id" $checked>$title
                    </td>
                    <td>
                        <input type="text" name="setting[moduleInModules][$layout][$key][sort_order]" class="form-control" value="$sort_order">
                    </td>
                </tr>
ETO;
        }
        return $html;
    }
    
    private static function doWithModule($layout, $layout_){
        $layout_config = [
            'left' => 3,
            'middle' => 1,
            'right' => 4
        ];
        $html = '';
        if(empty($layout)){
            return $html;
        }
        
        $ids = lumen_array_column($layout, 'id');
        
        $modules_settings = ModulesSetting::whereIn('modules_setting.id', $ids)->join('modules as m', function($join){
            $join->on('m.id', '=', 'modules_setting.module_id');
        })->select(['m.config_id', 'modules_setting.id', 'modules_setting.tag', 'modules_setting.setting', 'modules_setting.limit', 'modules_setting.show_tag', 'modules_setting.code'])->get()->toArray();
        if(empty($modules_settings)){
            return $html;
        }
        $server = new Server();
        $drivers = $server->getConfigDriver();
        
        $layout = array_under_reset($layout, 'id');
        foreach ($modules_settings as $key=>$value){
            $modules_settings[$key]['sort_order'] = $layout[$value['id']]['sort_order'];
            $modules_settings[$key]['layout'] = $layout_config[$layout_];
        }
        
        $modules_settings = twoDimensionalArraySort($modules_settings, 'sort_order');
        
        foreach ($modules_settings as $key=>$value){
            $driver = $drivers[$value['config_id']];
            $html .= ModuleLayout::with($driver)->viewHtml($value);
        }
        
        return $html;
    }
}
