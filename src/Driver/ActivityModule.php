<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;
use ModuleLayout\Models\ModulesSetting;
use Activity\Models\ProductActivity;

class ActivityModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $setting = unserialize($setting);
        
        $html = $setting;
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
        $activitys = self::getActivity();
        $activity_checkbox_html = '';
        
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting = unserialize($module_setting['setting']);
        $setting_activitys = $setting['activitys'] ?? [];
        
        if(!empty($activitys)){
            foreach ($activitys as $key=>$value){
                if(in_array($value['id'], $setting_activitys)){
                    $activity_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[activitys]['.$key.']" value="'.$value['id'].'" checked> '.$value['title'].'</label>';
                }else{
                    $activity_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[activitys]['.$key.']" value="'.$value['id'].'"> '.$value['title'].'</label>';
                }
            }
        }
        echo <<<ETO
            <div class="modal fade activity-module-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content p-3">
                    <form id="select-activity-form" method="post" enctype="multipart/form-data" action="$update_setting">
                      <div class="form-group">
                        <label for="activity" class="btn-block">选择活动</label>
                        $activity_checkbox_html
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
                    $('.activity-module-modal').modal();
                })

                //提交表单
                var options = {
                		   beforeSubmit: showRequest,
                		   success: showResponse,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#select-activity-form').ajaxForm(options);
                
                function showRequest(formData, jqForm, options){
                	return true;
                };  
                
                function showResponse(responseText, statusText){
                	var data = responseText;
                    if(data.status){
                        toastr.success('修改成功');
                        $('.activity-module-modal').modal('hide');
                    }else{
                        toastr.warning('修改失败');
                    }
                }
            </script>
ETO;
    }
        
    /**
     * 取活动列表
     */
    private static function getActivity(){
        $date = date("Y-m-d H:i:s");
        $result = ProductActivity::where('ended_at', '>', $date)->select(['id', 'title'])->get()->toArray();
        return $result;
    }
}
