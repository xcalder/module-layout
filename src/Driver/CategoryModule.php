<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;
use ModuleLayout\Models\ModulesSetting;
use App\Service\CategoryService;

class CategoryModule implements ModuleInterface
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
        
        $categorys_service = new CategoryService();
        $categorys = $categorys_service->getCategorys($request);
        $category_checkbox_html = '';
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting = unserialize($module_setting['setting']);
        $setting_categorys = $setting['categorys'] ?? [];
        $setting_child_categorys = $setting['child_categorys'] ?? [];
        
        if(!empty($categorys['data'])){
            $i = 0;
            foreach ($categorys['data'] as $key=>$value){
                if(in_array($value['id'], $setting_categorys)){
                    $category_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2 btn-block" style="font-weight: bolder;"><input type="checkbox" name="setting[categorys]['.$key.']" value="'.$value['id'].'" checked> '.$value['title'].'</label>';
                }else{
                    $category_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2 btn-block" style="font-weight: bolder;"><input type="checkbox" name="setting[categorys]['.$key.']" value="'.$value['id'].'"> '.$value['title'].'</label>';
                }
                if(!empty($value['child'])){
                    foreach ($value['child'] as $k=>$v){
                        if(in_array($v['id'], $setting_child_categorys)){
                            $category_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[child_categorys]['.$i.']" value="'.$v['id'].'" checked> '.$v['title'].'</label>';
                        }else{
                            $category_checkbox_html .= '<label class="checkbox-inline ml-0 mr-2"><input type="checkbox" name="setting[child_categorys]['.$i.']" value="'.$v['id'].'"> '.$v['title'].'</label>';
                        }
                        $i++;
                    }
                }
                $i++;
            }
        }
        echo <<<ETO
            <div class="modal fade category-module-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content p-3">
                    <form id="select-category-form" method="post" enctype="multipart/form-data" action="$update_setting">
                      <div class="form-group">
                        <label for="category" class="btn-block">选择活动</label>
                        $category_checkbox_html
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
                    $('.category-module-modal').modal();
                })
                
                //提交表单
                var options = {
                		   beforeSubmit: showRequest,
                		   success: showResponse,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#select-category-form').ajaxForm(options);
                
                function showRequest(formData, jqForm, options){
                	return true;
                };
                
                function showResponse(responseText, statusText){
                	var data = responseText;
                    if(data.status){
                        toastr.success('修改成功');
                        $('.category-module-modal').modal('hide');
                    }else{
                        toastr.warning('修改失败');
                    }
                }
            </script>
ETO;
    }
}
