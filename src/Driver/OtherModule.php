<?php

namespace ModuleLayout;

use ModuleLayout\Models\ModulesSetting;

class OtherModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $code = $setting['code'] ?? '';
        
        $setting = unserialize($setting['setting']);
        
        switch ($code)
        {
            case 'productSearch':
                $default_keyword = $setting['default_keyword'] ?? '';
                $hot_keywords = $setting['hot_keyword'] ?? '';
                $hot_keywords = explode(',', $hot_keywords);
                
                $hot_keyword_html = '';
                if(!empty($hot_keywords)){
                    foreach ($hot_keywords as $keyword){
                        $hot_keyword_html .= '<span class="pr-3"><a href="'.url('product/product_list?keyword='.$keyword).'">'.$keyword.'</a></span>';
                    }
                }
                
                $url = url('product/product_list');
                $html = <<<ETO
                    <div class="row">
                        <div class="col-md-8">
                            <form id="top-search-form" action="$url" method="GET">
                    			<div class="input-group input-group-lg mb-2">
                                  <input type="text" class="form-control" placeholder="搜索" value="$default_keyword" name="keyword">
                                  <span class="input-group-addon a" onclick="$('#top-search-form').submit();"><i class="glyphicon glyphicon-search"></i></span>
                                </div>
                                <p>$hot_keyword_html</p>
                            </form>
                        </div>
                    </div>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            var keyword = $.getUrlParam('keyword');
                            if(!isEmpty(keyword)){
                                $('#top-search-form input[name="keyword"]').val(keyword);
                            }
                        })
                    </script>
ETO;
                break;
            default:
                $html = '';
        }
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
        $setting = unserialize($module_setting['setting']);
        $default_keyword = $setting['default_keyword'] ?? '';
        $hot_keyword = $setting['hot_keyword'] ?? '';
        
        echo <<<ETO
            <div class="modal fade other-modal-edit" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content p-3">
                    <form id="set-other-module" method="post" enctype="multipart/form-data" action="$update_setting">
                      <div class="form-group">
                        <label for="default_keyword">默认搜索词</label>
                        <input type="text" class="form-control" id="default_keyword" name="setting[default_keyword]" placeholder="默认搜索词" value="$default_keyword">
                      </div>
                      <div class="form-group">
                        <label for="hot_keyword">热门搜索词</label>
                        <input type="text" class="form-control" id="hot_keyword" name="setting[hot_keyword]" placeholder="热门搜索词" value="$hot_keyword">
                        <p>多个词使用","号分隔</p>
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
                    $('.other-modal-edit').modal();
                })
                //提交表单
                var options = {
                		   beforeSubmit: showRequest,
                		   success: showResponse,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#set-other-module').ajaxForm(options);
                
                function showRequest(formData, jqForm, options){
                	return true;
                };  
                
                function showResponse(responseText, statusText){
                	var data = responseText;
                    if(data.status){
                        toastr.success('修改成功');
                        $('.other-modal-edit').modal('hide');
                    }else{
                        toastr.warning('修改失败');
                    }
                }
            </script>
ETO;
    }
}
