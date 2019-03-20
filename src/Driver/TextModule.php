<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;
use ModuleLayout\Models\ModulesSetting;

class TextModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $setting = unserialize($setting);
        
        $html = $setting['content'];
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
        
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting_content = '';
        if(!empty($module_setting)){
            $setting = unserialize($module_setting['setting'] ?? []);
            if($setting){
                $setting_content = $setting['content'] ?? '';
            }
        }
        
        echo <<<ETO
            <div class="modal fade banner-module-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <form id="banner-module-modal" method="post" enctype="multipart/form-data" action="$update_setting">
                        <div class="form-group">
                            <label for="content" class="text-block">文本内容<span class="error"></span></label>
                            <textarea class="summernote" name="setting[content]">$setting_content</textarea>
                        </div>
                        <input type="hidden" name="config_id" value="$config_id">
                        <input type="hidden" name="module_setting_id" value="$module_setting_id">
                        <button type="submit" class="btn btn-default btn-block">提交</button>
                    </form>
                </div>
              </div>
            </div>
            
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.banner-module-modal').modal();
                    defaultSummernote('.summernote');
                    rewriteSummernote();
                })
                
                //提交表单
                var options = {
                		   beforeSubmit: showRequest,
                		   success: showResponse,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#banner-module-modal').ajaxForm(options);
                
                function showRequest(formData, jqForm, options){
                	return true;
                };
                
                function showResponse(responseText, statusText){
                	var data = responseText;
                    if(data.status){
                        toastr.success('修改成功');
                        $('.banner-module-modal').modal('hide');
                    }else{
                        toastr.warning('修改失败');
                    }
                }
            </script>
ETO;
    }
}
