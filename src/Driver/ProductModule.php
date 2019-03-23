<?php

namespace ModuleLayout;

use App\Models\Product;
use ModuleLayout\Models\ModulesSetting;
use App\Service\ProductService;

class ProductModule implements ModuleInterface
{
    /**
     * 处理setting到html
     * @param unknown $setting
     */
    public static function viewHtml($setting){
        $html = '';
        $limit = $setting['limit'] ?? 8;
        $show_tag = $setting['show_tag'] ?? 1;
        $tag = $setting['tag'];
        $layout = $setting['layout'] ?? 3;
        $setting = unserialize($setting['setting']);
        
        $product_ids = $setting['products'] ?? [];
        $category = $setting['category'] ?? [];
        $products = new Product();
        if(!empty($product_ids)){
            $products = $products->whereIn('id', $product_ids);
        }
        if(!empty($category)){
            $products = $products->whereIn('child_category_id', $category);
        }
        
        $products = $products->select(['id'])->groupBy('id')->limit($limit)->get()->toArray();
        $product_ids = lumen_array_column($products, 'id');
        
        if(empty($product_ids)){
            return $html;
        }
        
        $product_service = new ProductService();
        $products = $product_service->getProductList(['product_ids' => $product_ids]);
        
        $server = new Server();
        $product_html = $server->setProductHtml($products, $layout);
        
        $html = <<<ETO
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">$tag</h4>
              </div>
              <div class="panel-body p-0">$product_html</div>
            </div>
ETO;
        
        return $html;
    }
    
    /**
     * 处理setting
     * @param unknown $setting
     */
    public static function doWithSetting($setting){
        $setting['products'] = $setting['products'] ?? [];
        if(!empty($setting['products'])){
            $result = Product::whereIn('id', $setting['products'])->select(['id', 'title'])->get();
            $setting['products'] = $result;
        }
        return $setting;
    }
    
    /**
     * 更新设置
     * @param unknown $request
     */
    public static function updateSetting($request){
        $return = false;
        $input_setting = $request->input('setting', []);
        $input_product_id = $request->input('product_id', 0);
        $module_setting = ModulesSetting::find($request->input('module_setting_id'));
        $setting = unserialize($module_setting['setting']);
        $setting_products = $setting['products'] ?? [];
        
        if(!empty($input_setting)){
            $setting['sort_order'] = $input_setting['sort_order'] ?? 1;
            $setting['view_type'] = $input_setting['view_type'] ?? 1;
            $setting['category'] = array_values($input_setting['category'] ?? []) ?? [];
            $setting['products'] = $setting_products;
        }
        if(!empty($input_product_id)){
            if($request->input('action') == 'add'){
                $setting_products[] = $input_product_id;
            }
            if($request->input('action') == 'del'){
                foreach ($setting_products as $key=>$value){
                    if($value == $input_product_id){
                        unset($setting_products[$key]);
                    }
                }
            }
            $setting['products'] = $setting_products;
        }
        if(ModulesSetting::where('id', $request->input('module_setting_id'))->update(['setting' => serialize($setting)])){
            $return = true;
        }
        return $return;
    }
    
    /**
     * 模块设置表单
     * @param unknown $request
     */
    public static function getSettingForm($request){
        $config_id = $request->input('config_id');
        $module_setting_id = $request->input('module_setting_id');
        $get_module_setting_api = url('api/modules/module_setting?module_setting_id='.$module_setting_id);
        $product_search_api = url('/api/product/search?api_token='.$request->input('api_token', ''));
        $update_setting = url('/api/modules/upate_setting?api_token='.$request->input('api_token'));
        echo <<<ETO
            <div class="modal fade bs-example-modal-lg modal-product" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="row">
                        <form id="atricle-base-setting" class="col-md-7" method="post" enctype="multipart/form-data" action="$update_setting">
                            <input type="hidden" name="config_id" value="$config_id">
                            <input type="hidden" name="module_setting_id" value="$module_setting_id">
                            <input type="hidden" name="base_setting" value="1">
                            <div class="col-md-6">
                                  <div class="form-group">
                                    <label for="sort_order">排序</label>
                                    <select class="form-control" id="sort_order" name="setting[sort_order]">
                                        <option value="1">最近发布</option>
                                        <option value="2">最早发布</option>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="view_type">前台样式</label>
                                    <select class="form-control" id="view_type" name="setting[view_type]">
                                        <option value="1">图文横排</option>
                                        <option value="2">图文坚排</option>
                                        <option value="3">只显示图片</option>
                                        <option value="4">只显示文字</option>
                                    </select>
                                  </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="btn-block">分类</label>
                                    <div id="category" style="height: 500px;overflow-y: auto;"></div>
                                  </div>
                            </div>
                            <button type="submit" class="btn btn-default btn-block">提交</button>
                        </form>
                        <div class="col-md-5">
                            <p>指定商品</p>
                            <!-- Nav tabs -->
                              <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#joined" aria-controls="joined" role="tab" data-toggle="tab" class="p-1">已加入</a></li>
                                <li role="presentation"><a href="#not-joined" aria-controls="not-joined" role="tab" data-toggle="tab" class="p-1">未加入</a></li>
                              </ul>
                            
                              <!-- Tab panes -->
                              <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="joined">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <td>标题</td>
                                                <td>操作</td>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="not-joined" style="height: 490px;overflow-y: auto;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <td colspan="2">
                                                    <form id="product-search" method="get" enctype="multipart/form-data" action="$product_search_api">
                                                        <div class="input-group">
                                                          <input type="text" class="form-control" name="keyword" placeholder="关键词">
                                                          <span class="input-group-btn">
                                                            <input type="hidden" name="product" value="1">
                                                            <button class="btn btn-default" type="submit">搜索</button>
                                                          </span>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>标题</td>
                                                <td>操作</td>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                              </div>
                        </div>
                    </div>
                </div>
              </div>
            </div>
            <script type="text/javascript">
                var setting_category = [];
                $(document).ready(function () {
                    $('.modal-product').modal();
                    getModuleSetting();
                })

                function changeproduct(product_id, action='add'){
                    $.ajax({
                	    type: 'POST',
                	    url: '$update_setting',
                	    data: {product_id: product_id, action: action, config_id: $config_id, module_setting_id: $module_setting_id},
                	    dataType: 'json',
                	    success: function(data){
                            if(data.status){
                                getModuleSetting();
                                toastr.success('修改成功');
                            }else{
                                toastr.warning('修改失败');
                            }
                        }
                    })
                }

                function getModuleSetting(){
                    $.ajax({
                	    type: 'GET',
                	    url: '$get_module_setting_api',
                	    data: {api_token: api_token, config_id: $config_id},
                	    dataType: 'json',
                	    success: function(data){
                            var joined_html = '';
                            if(data.status){
                                var setting = data.data.setting;
                                setting_category = setting.category;
                                getCategory();
                                if(setting){
                                    if(!isEmpty(setting.sort_order)){
                                        $('#atricle-base-setting #sort_order').val(setting.sort_order);
                                    }
                                    if(!isEmpty(setting.view_type)){
                                        $('#atricle-base-setting #view_type').val(setting.view_type);
                                    }
                                    if(!isEmpty(setting.products)){
                                        for(var i in setting.products){
                                            var product = setting.products[i];
                                            joined_html += '<tr><td>'+product.title+'</td><td><button type="button" class="btn btn-default btn-xs" onclick="changeproduct('+product.id+',\'del\');">移出</button></td></tr>';
                                        }
                                        $('#joined tbody').html(joined_html);
                                    }
                                }else{
                                    joined_html += '<tr><td colspan="2">还没有商品</td></tr>';
                                }
                            }
                            $('#joined tbody').html(joined_html);
                        }
                    })
                }

                function getCategory(){
                    $.ajax({
                	    type: 'GET',
                	    url: url+'/api/category/get_list',
                	    data: {api_token: api_token, offset: 1000},
                	    dataType: 'json',
                	    success: function(data){
                	    	var html = '';
                            var e = 0;
                            for(var i in data.data.data){
                                var category = data.data.data[i];
                                if(!isEmpty(category.child)){
                                    html += '<label class="btn-block">'+category.title+'</label>';
                                    for(var c in category.child){
                                        var child = category.child[c];
                                        html += '<label class="checkbox-inline ml-0 mr-3">';
                                        if(in_array(child.id, setting_category)){
                                            html += '<input type="checkbox" class="module-setting-category" name="setting[category]['+e+']" value="'+child.id+'" checked> '+child.title;
                                        }else{
                                            html += '<input type="checkbox" class="module-setting-category" name="setting[category]['+e+']" value="'+child.id+'"> '+child.title;
                                        }
                                        html += '</label>';
                                        e++;
                                    }
                                }
                                e++;
                            }
                            $('#category').html(html);
                	    }
                	})
                }
                //提交表单
                var options = {
                		   beforeSubmit: showRequestproductSearch,
                		   success: showResponseproductSearch,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#product-search').ajaxForm(options);
                
                function showRequestproductSearch(formData, jqForm, options){
                	return true;
                };  
                
                function showResponseproductSearch(responseText, statusText){
                	var data = responseText;
                    var html = '';
                	if(data.status){
                		var products = data.data.data;
                        if(!isEmpty(products)){
                            for(var i in products){
                                var product = products[i];
                                html += '<tr><td>'+product.title+'</td><td><button type="button" class="btn btn-default btn-xs" onclick="changeproduct('+product.id+',\'add\');">加入</button></td></tr>';
                            }
                        }else{
                            html += '<tr><td colspan="2">未找到搜索的商品</td></tr>';
                        }
                        pagination(data, '#not-joined tfoot', 2);
                	}else{
                		html += '<tr><td colspan="2">未找到搜索的商品</td></tr>';
                	}
                    $('#not-joined tbody').html(html);
                }

                //提交表单
                var options = {
                		   beforeSubmit: showRequestproductBaseSetting,
                		   success: showResponseproductBaseSetting,
                		   dataType: 'json',
                		   timeout: 3000
                		}
                $('#atricle-base-setting').ajaxForm(options);
                
                function showRequestproductBaseSetting(formData, jqForm, options){
                	return true;
                };  
                
                function showResponseproductBaseSetting(responseText, statusText){
                	var data = responseText;
                }
            </script>
ETO;
    }
}
