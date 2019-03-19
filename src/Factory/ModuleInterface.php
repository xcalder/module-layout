<?php

namespace ModuleLayout;

/**
 * 订单活动接口
 * 所有的订单活动实现此接口，并在业务逻辑中实例化
 * @author xcalder
 *
 */
interface ModuleInterface
{
    /**
     * 取模块设置表单
     * @param unknown $request
     */
    public static function getSettingForm($request);
    
    /**
     * 更新设置
     * @param unknown $request
     */
    public static function updateSetting($request);
    
    /**
     * 处理setting
     * @param unknown $setting
     */
    public static function doWithSetting($setting);
}
