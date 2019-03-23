<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;
use ModuleLayout\Models\ModulesSetting;
use App\Service\CategoryService;
use App\Models\Category;
use App\Models\CategoryChild;

class CategoryModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        
        $html = self::setHtml($setting);
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
        $show_type = $setting['show_type'] ?? 1;
        
        $show_type_config = [
            '1' => '横展开平铺',
            '2' => '坚平铺',
            '3' => '坚点击展开'
        ];
        $show_type_html = '';
        foreach ($show_type_config as $key=>$value){
            if($key == $show_type){
                $show_type_html .= '<option value="'.$key.'" selected>'.$value.'</option>';
            }else{
                $show_type_html .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }
        
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
                        <label for="show_type" class="btn-block">显示方式</label>
                        <select name="setting[show_type]" class="form-control">$show_type_html</select>
                      </div>
                      <div class="form-group">
                        <label for="category" class="btn-block">选择分类</label>
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
    
    
    private static function setHtml($setting){
        $html = '';
        $tag = $setting['tag'];
        $show_tag = $setting['show_tag'] ?? 1;
        $setting = unserialize($setting['setting']);
        $chid_ids = $setting['child_categorys'];
        $categorys = Category::with(['child' => function($query) use($chid_ids){
            $query->whereIn('id', $chid_ids)->select(['id', 'category_id', 'title', 'url']);
        }])->whereIn('id', $setting['categorys'] ?? [])->select(['id', 'title', 'url'])->orderBy('sort_order', 'desc')->get()->toArray();
        
        switch ($setting['show_type'])
        {
            case '1':
                $li_html = '';
                if(!empty($categorys)){
                    foreach($categorys as $key=>$value){
                        $url = '';
                        if(!empty($value['url'])){
                            $url = url($value['url']);
                        }else{
                            $url = url('product/product_list?&category_id='.$value['id']);
                        }
                        $li_html .= '<li><a target="_blank" href="'.$url.'" class="pt-1 pb-1">'.$value['title'].'</a></li>';
                        
                        if(!empty($value['child'])){
                            foreach($value['child'] as $k=>$v){
                                $url = '';
                                if(!empty($v['url'])){
                                    $url = url($v['url']);
                                }else{
                                    $url = url('product/product_list?child_category_id='.$v['id']);
                                }
                                $li_html .= '<li><a target="_blank" href="'.$url.'" class="pt-1 pb-1">'.$v['title'].'</a></li>';
                            }
                        }
                    }
                }
                $html = <<<ETO
                <nav class="navbar" style="min-height: 30px">
                  <div class="container-fluid p-0">
                    <div class="collapse navbar-collapse p-0">
                      <ul class="nav navbar-nav">$li_html</ul>
                    </div>
                  </div>
                </nav>
ETO;
                break;
            case '2':
                $li_html = '';
                if(!empty($categorys)){
                    foreach($categorys as $key=>$value){
                        $url = '';
                        if(!empty($value['url'])){
                            $url = url($value['url']);
                        }else{
                            $url = url('product/product_list?&category_id='.$value['id']);
                        }
                        $li_html .= '<li class="list-group-item border-0"><a target="_blank" href="'.$url.'" class="pt-1 pb-1">'.$value['title'].'</a></li>';
                        if(!empty($value['child'])){
                            foreach($value['child'] as $k=>$v){
                                $url = '';
                                if(!empty($v['url'])){
                                    $url = url($v['url']);
                                }else{
                                    $url = url('product/product_list?child_category_id='.$v['id']);
                                }
                                $li_html .= '<li class="list-group-item border-0 ml-3"><a target="_blank" href="'.$url.'" class="pt-1 pb-1">'.$v['title'].'</a></li>';
                            }
                        }
                    }
                }
                $html = <<<ETO
                <div class="panel panel-default">
ETO;
                if($show_tag == 1){
                    $html .= <<<ETO
                    <div class="panel-heading">
                        <h4 class="panel-title">$tag</h4>
                    </div>
ETO;
                }
                $html .= <<<ETO
                  <div class="panel-body p-0">
                    <ul class="list-group">$li_html</ul>
                  </div>
                </div>         
ETO;
                break;
            case '3':
                $li_html = '';
                $child_li_html = '';
                if(!empty($categorys)){
                    foreach($categorys as $key=>$value){
                        $url = '';
                        if(!empty($value['url'])){
                            $url = url($value['url']);
                        }else{
                            $url = url('product/product_list?&category_id='.$value['id']);
                        }
                        $li_html .= '<li class="list-group-item border-0 btn-block" data-id="'.$value['id'].'">'.$value['title'].'</li>';
                        if(!empty($value['child'])){
                            foreach($value['child'] as $k=>$v){
                                $url = '';
                                if(!empty($v['url'])){
                                    $url = url($v['url']);
                                }else{
                                    $url = url('product/product_list?child_category_id='.$v['id']);
                                }
                                $child_li_html .= '<li class="list-group-item border-0 ml-3"><a target="_blank" href="'.$url.'" class="pt-1 pb-1">'.$v['title'].'</a></li>';
                            }
                        }
                    }
                }
                
                $html = <<<ETO
                <div class="panel panel-default">
ETO;
                if($show_tag == 1){
                    $html .= <<<ETO
                    <div class="panel-heading">
                        <h4 class="panel-title">$tag</h4>
                    </div>
ETO;
                }
                $html .= <<<ETO
                  <div class="panel-body p-0">
                    <ul class="list-group">$li_html</ul>
                  </div>
                </div>
ETO;
                break;
             default:
                 $html = '';
        }
        return $html;
    }
}
