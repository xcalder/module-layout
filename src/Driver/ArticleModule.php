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
            <style type="text/css">#categorys.form-group{height: 450px;overflow-y: auto;}</style>
            <div class="row">
                <form>
                    <div class="col-md-4">
                        <h4>文章模块设置</h4>
                        <div class="form-group">
                            <label for="title">标题</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="标题">
                          </div>
                          <div class="form-group">
                            <label for="description">描述</label>
                            <input type="text" class="form-control" id="description" name="description" placeholder="描述">
                          </div>
                          <div class="form-group">
                            <label for="sort-order">文章排序</label>
                            <select class="form-control" id="sort-order" name="sort-order">
                                <option value="0">--请选择--</option>
                                <option value="1">最新发布在前</option>
                                <option value="1">最新发布在后</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="view-type">显示方式</label>
                            <select class="form-control" id="view-type" name="view-type">
                                <option value="0">--请选择--</option>
                                <option value="1">图文横排</option>
                                <option value="2">图文坚排</option>
                                <option value="3">只显示图片</option>
                                <option value="4">只显示文字</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="status" class="btn-block">状态</label>
                            <label class="radio-inline">
                              <input type="radio" name="status" value="1"> 启用
                            </label>
                            <label class="radio-inline">
                              <input type="radio" name="status" value="0"> 禁用
                            </label>
                          </div>
                          <button type="submit" class="btn btn-default btn-block">提交</button>
                          <p>说明:自动设置/手动指定文章不能同时生效，当设置了手动指定文章，自动设置将不生效</p>
                    </div>
                    <div class="col-md-4">
                        <h4>分类</h4>
                        <div id="categorys" class="form-group mb-0"></div>
                    </div>
                    <div class="col-md-4">
                        <h4>指定文章</h4>
                        <!-- Nav tabs -->
                          <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab" class="p-1">已添加</a></li>
                            <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab" class="p-1">未添加</a></li>
                          </ul>
                        
                          <!-- Tab panes -->
                          <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="home">...</div>
                            <div role="tabpanel" class="tab-pane" id="profile">...</div>
                          </div>
                    </div>
                </form>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                	getCategory();
                })
                function getCategory(){
                    $.ajax({
                	    type: 'GET',
                	    url: url+'/api/category/get_list',
                	    data: {api_token: api_token},
                	    dataType: 'json',
                	    success: function(data){
                	    	if(data.status){
                                html = '';
                                var categorys = data.data.data;
                                for(var i in categorys){
                                    var category = categorys[i];
                                    if(!isEmpty(category.child)){
                                        html += '<label class="btn-block">'+category.title+'</label>';
                                        for(var c in category.child){
                                            var child = category.child[c];
                                            html += '<label class="checkbox-inline ml-0 mr-3">';
                                            html += '<input type="checkbox" value="'+child.id+'"> '+child.title;
                                            html += '</label>';
                                        }
                                    }
                                }
                                $('#categorys.form-group').html(html);
                            }
                	    }
                	})
                }
            </script>
ETO;
    }
}
