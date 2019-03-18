<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function($router){
    $router->group(['prefix' => 'modules'], function($router){
        $router->get('module_list', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModulesList',
            'description' => '取模块列表'
        ]);
        
        $router->get('module', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModule',
            'description' => '取模块详情'
        ]);
        
        $router->get('module_setting_form', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModuleSettingForm',
            'description' => '取模块设置表单'
        ]);
        
        $router->get('module_setting_list', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModuleSettingList',
            'description' => '取模块设置列表'
        ]);
        
        $router->get('module_setting', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModuleSetting',
            'description' => '取模块设置详情'
        ]);
        
        $router->post('add_module', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@addModule',
            'description' => '添加模块'
        ]);
        
        $router->delete('del_module', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@delModule',
            'description' => '删除模块'
        ]);
        
        $router->put('update_module', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@updateModule',
            'description' => '更新模块'
        ]);
        
        $router->post('add_module_setting', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@addModuleSetting',
            'description' => '添加模块设置'
        ]);
        
        $router->delete('del_module_setting', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@delModuleSetting',
            'description' => '删除模块设置'
        ]);
        
        $router->put('update_module_setting', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@updateModuleSetting',
            'description' => '修改模块设置'
        ]);
        
        $router->get('module_route_list', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModuleRouteList',
            'description' => '路由列表'
        ]);
        
        $router->get('module_route', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@getModuleRoute',
            'description' => '路由详情'
        ]);
        
        $router->post('add_module_route', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@addModuleRoute',
            'description' => '添加路由'
        ]);
        
        $router->delete('del_module_route', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@delModuleRoute',
            'description' => '删除路由'
        ]);
        
        $router->put('update_module_route', [
            'group' => 'modules',
            'uses' => 'ModuleLayout\IndexController@updateModuleRoute',
            'description' => '修改路由'
        ]);
    });
});