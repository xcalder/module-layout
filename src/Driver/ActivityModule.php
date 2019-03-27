<?php

namespace ModuleLayout;

use ModuleLayout\Models\ModulesSetting;
use Activity\Models\ProductActivity;
use Activity\Models\ProductActivityRuleProducts;
use App\Service\ProductService;

class ActivityModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        return self::setHtml($setting);
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
                        <label for="view_type">前台样式</label>
                        <select class="form-control" id="view_type" name="setting[view_type]">
                            <option value="1">图文横排</option>
                            <option value="2">图文坚排</option>
                            <option value="3">只显示图片</option>
                            <option value="4">只显示文字</option>
                        </select>
                      </div>
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
    
    private static function setHtml($setting){
        $html = '';
        $limit = $setting['limit'] ?? 8;
        $show_tag = $setting['show_tag'] ?? 1;
        $tag = $setting['tag'];
        $layout = $setting['layout'] ?? 3;
        $setting = unserialize($setting['setting']);
        $view_type = $setting['view_type'] ?? 1;
        
        $activity_ids = $setting['activitys'] ?? [];
        if(empty($activity_ids)){
            return $html;
        }
        $products = ProductActivityRuleProducts::whereIn('activity_id', $activity_ids)->select(['product_id'])->groupBy('product_id')->limit($limit)->get()->toArray();
        $product_ids = lumen_array_column($products, 'product_id');
        
        if(empty($product_ids)){
            return $html;
        }
        
        $product_service = new ProductService();
        $products = $product_service->getProductList(['product_ids' => $product_ids]);
        
        $server = new Server();
        $product_html = $server->setProductHtml($products, $layout, $view_type);
        
        $more_url = url('activity/activity_list');
        $html = <<<ETO
            <div class="panel panel-default">
ETO;
        if($show_tag == 1){
            $html .= <<<ETO
            <div class="panel-heading">
                <h4 class="panel-title">$tag<a href="$more_url" class="pull-right f-14">更多>></a></h4>
            </div>
ETO;
        }
        
        $html .= <<<ETO
              <div class="panel-body p-0">$product_html</div>
            </div>
ETO;
        return $html;
    }
}
