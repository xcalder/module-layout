<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;
use ModuleLayout\Models\ModulesSetting;
use App\Service\ToolsService;

class BannerModule implements ModuleInterface
{
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
        $default_img = url('storage/images/default.jpg');
        $i = 0;
        
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $url_type = [
            '1' => '商品页',
            '2' => '商品列表页',
            '3' => '文章列表',
            '4' => '文章详情',
            '5' => '单页列表',
            '6' => '单页详情',
            '7' => '活动详情',
            '8' => '活动列表',
        ];
        $url_type_option = '';
        foreach ($url_type as $key=>$value){
            $url_type_option .= '<option value="'.$key.'">'.$value.'</option>';
        }
        $setting_html = '';
        if(!empty($module_setting)){
            $setting = unserialize($module_setting['setting'] ?? []);
            if($setting){
                $tools_service = new ToolsService();
                foreach ($setting['item'] as $key=>$value){
                    $url_type_option_ = '';
                    foreach ($url_type as $k=>$v){
                        if($k == ($value['url_type'] ?? 0)){
                            $url_type_option_ .= '<option value="'.$k.'" selected>'.$v.'</option>';
                        }else{
                            $url_type_option_ .= '<option value="'.$k.'">'.$v.'</option>';
                        }
                    }
                    $value['thumb_img'] = $tools_service->serviceResize($value['img'], 80, 80);
                    $setting_html .= '<tr class="item-'.$i.'">';
                    $setting_html .= '<td><a data-toggle="image" class="img-thumbnail"><img width="24px" height="24px" src="'.$value['thumb_img'].'" data-placeholder="'.$default_img.'"><input type="hidden" name="setting[item]['.$i.'][img]" value="'.$value['img'].'"></a></td>';
                    $setting_html .= '<td><input type="text" class="form-control" name="setting[item]['.$i.'][title]" placeholder="标题" value="'.$value['title'].'"></td>';
                    $setting_html .= '<td><select class="form-control" name="setting[item]['.$i.'][url_type]">'.$url_type_option_.'</select></td>';
                    $setting_html .= '<td><input type="text" class="form-control" name="setting[item]['.$i.'][parameter]" placeholder="链接参数" value="'.$value['parameter'].'"></td>';
                    $setting_html .= '<td><input type="text" class="form-control" name="setting[item]['.$i.'][sort_order]" placeholder="链接参数" value="'.$value['sort_order'].'"></td>';
                    $setting_html .= '<td><button type="button" class="btn btn-danger" onclick="$(\'tr.item-'.$i.'\').remove();">删除</button></td>';
                    $setting_html .= '</tr>';
                    $i++;
                }
            }
        }
        
        echo <<<ETO
            <div class="modal fade banner-module-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <form id="banner-module-modal" method="post" enctype="multipart/form-data" action="$update_setting">
                        <input type="hidden" name="config_id" value="$config_id"><input type="hidden" name="module_setting_id" value="$module_setting_id">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <td>图片</td>
                                    <td>标题</td>
                                    <td>链接类型</td>
                                    <td>链接参数</td>
                                    <td>排序</td>
                                    <td>操作</td>
                                </tr>
                            </thead>
                            <tbody>$setting_html</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right"><button type="button" class="btn btn-info" onclick="add();">添加</button></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-center"><button type="submit" class="btn btn-success">提交</button></td>
                                </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
              </div>
            </div>

            <script type="text/javascript">
                $(document).ready(function () {
                    $('.banner-module-modal').modal();
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
            
                var i = $i;
                function add(){
                    html = '';
                    html += '<tr class="item-'+i+'">';
                    html += '<td>';
                    html += '<a data-toggle="image" class="img-thumbnail">';
                    html += '<img width="24px" height="24px" src="$default_img">';
                    html += '<input type="hidden" name="setting[item]['+i+'][img]" value="">';
                    html += '</a>';
                    html += '</td>';
                    html += '<td><input type="text" class="form-control" name="setting[item]['+i+'][title]" placeholder="标题"></td>';
                    html += '<td><select class="form-control" name="setting[item]['+i+'][url_type]">$url_type_option</select></td>';
                    html += '<td><input type="text" class="form-control" name="setting[item]['+i+'][parameter]" placeholder="链接参数"></td>';
                    html += '<td><input type="text" class="form-control" name="setting[item]['+i+'][sort_order]" placeholder="排序"></td>';
                    html += '<td><button type="button" class="btn btn-danger" onclick="$(\'tr.item-'+i+'\').remove();">删除</button></td>';
                    html += '</tr>';
                    $('#banner-module-modal tbody').append(html);
                    i++;
                }
            </script>
ETO;
    }
}
