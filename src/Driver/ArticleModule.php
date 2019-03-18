<?php

namespace ModuleLayout;

use Illuminate\Support\Facades\DB;
use ModuleLayout\ModuleInterface;

class ArticleModule implements ModuleInterface
{
    /**
     * 模块设置表单
     * @param unknown $request
     */
    public static function getSettingForm($request){
        echo <<<ETO
            <div class="modal fade bs-example-modal-lg modal-article" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content p-3">
                    <div class="row">
                        <form class="col-md-7">
                            <div class="col-md-6">
                                  <div class="form-group">
                                    <label for="sort_order">排序</label>
                                    <select class="form-control" id="sort_order">
                                        <option value="1">最近发布</option>
                                        <option value="2">最早发布</option>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label for="view_type">前台样式</label>
                                    <select class="form-control" id="view_type">
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
                            <p>指定文章</p>
                            
                        </div>
                    </div>
                </div>
              </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.modal-article').modal();
                    getCategory();
                })
                function getCategory(){
                    $.ajax({
                	    type: 'GET',
                	    url: url+'/api/category/get_list',
                	    data: {api_token: api_token, offset: 1000},
                	    dataType: 'json',
                	    success: function(data){
                	    	var html = '';
                            for(var i in data.data.data){
                                var category = data.data.data[i];
                                if(!isEmpty(category.child)){
                                    html += '<label class="btn-block">'+category.title+'</label>';
                                    for(var c in category.child){
                                        var child = category.child[c];
                                        html += '<label class="checkbox-inline ml-0 mr-3">';
                                        html += '<input type="checkbox" id="inlineCheckbox1" value="'+child.id+'"> '+child.title;
                                        html += '</label>';
                                    }
                                }
                            }
                            $('#category').html(html);
                	    }
                	})
                }
            </script>
ETO;
    }
}
