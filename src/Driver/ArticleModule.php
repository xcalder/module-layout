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
                        <div class="col-md-6">
                            <form>
                              <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
                              </div>
                              <div class="form-group">
                                <label for="exampleInputPassword1">Password</label>
                                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                              </div>
                              <button type="submit" class="btn btn-default">Submit</button>
                            </form>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                </div>
              </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('.modal-article').modal();
                })
            </script>
ETO;
    }
}
