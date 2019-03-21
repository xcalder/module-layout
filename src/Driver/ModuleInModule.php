<?php

namespace ModuleLayout;

use ModuleLayout\Models\ModulesSetting;

class ModuleInModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $setting = unserialize($setting);
        
        //$html = $setting;
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
        $moduleInModule_checkbox_html_left = '';
        $moduleInModule_checkbox_html_middle = '';
        $moduleInModule_checkbox_html_right = '';
        
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting = unserialize($module_setting['setting']);
        $setting_moduleInModules = $setting['moduleInModules'] ?? [];
        
        if(!empty($moduleInModules)){
            foreach ($moduleInModules as $key=>$value){
                if($value['id'] != $request->input('module_setting_id')){
                    if(in_array($value['id'], $setting_moduleInModules['left'] ?? [])){
                        $moduleInModule_checkbox_html_left .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][left]['.$key.']" value="'.$value['id'].'" checked> '.$value['title'].'</label>';
                    }else{
                        $moduleInModule_checkbox_html_left .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][left]['.$key.']" value="'.$value['id'].'"> '.$value['title'].'</label>';
                    }
                    if(in_array($value['id'], $setting_moduleInModules['middle'] ?? [])){
                        $moduleInModule_checkbox_html_middle .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][middle]['.$key.']" value="'.$value['id'].'" checked> '.$value['title'].'</label>';
                    }else{
                        $moduleInModule_checkbox_html_middle .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][middle]['.$key.']" value="'.$value['id'].'"> '.$value['title'].'</label>';
                    }
                    if(in_array($value['id'], $setting_moduleInModules['right'] ?? [])){
                        $moduleInModule_checkbox_html_right .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][right]['.$key.']" value="'.$value['id'].'" checked> '.$value['title'].'</label>';
                    }else{
                        $moduleInModule_checkbox_html_right .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[moduleInModules][right]['.$key.']" value="'.$value['id'].'"> '.$value['title'].'</label>';
                    }
                }
            }
        }
        echo <<<ETO
            <div class="modal fade moduleInModule-module-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="row">
                        <form id="select-moduleInModule-form" method="post" enctype="multipart/form-data" action="$update_setting">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="moduleInModule" class="btn-block">左侧</label>
                                    $moduleInModule_checkbox_html_left
                                  </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="moduleInModule" class="btn-block">中间</label>
                                    $moduleInModule_checkbox_html_middle
                                  </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="moduleInModule" class="btn-block">左侧</label>
                                    $moduleInModule_checkbox_html_right
                                  </div>
                            </div>
                          <input type="hidden" name="config_id" value="$config_id">
                          <input type="hidden" name="module_setting_id" value="$module_setting_id">
                          <button type="submit" class="btn btn-default btn-block">提交</button>
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
}
