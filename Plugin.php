<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 多模板切换
 *
 * @package MultiTheme
 * @author HackRose
 * @version 1.0.0
 * @link http://hackrose.com
 */

class MultiTheme_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('index.php')->begin = array('MultiTheme_Plugin', 'load');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     * 设置切换主题的cookie字段
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        Typecho_Widget::widget('Widget_Options')->to($options);
        Typecho_Widget::widget('Widget_Themes_List')->to($themes);
        $availableThemes = array();
        while($themes->next()){
            $availableThemes[$themes->name] = $themes->title;
        }

        $field = new Typecho_Widget_Helper_Form_Element_Text('field', NULL, 'theme', _t('url中带有此参数时切换主题'));
        $form->addInput($field);

        $theme = new Typecho_Widget_Helper_Form_Element_Select(
            'default', $availableThemes, $options->theme,
            '默认主题', '设置你前台默认的主题');
        $form->addInput($theme);

        $allowExchange = new Typecho_Widget_Helper_Form_Element_Checkbox(
        'allow', $availableThemes, array_keys($availableThemes),
            '可选主题', '设置你经常使用的默认允许在前台切换的主题');
        $form->addInput($allowExchange);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 支持切换的主题
     * @return mixed
     * @throws Typecho_Widget_Exception
     */
    public static function themes(){
        Typecho_Widget::widget('Widget_Options')->to($options);
        $options->theme = $options->plugin('MultiTheme')->default;
        return $options->plugin('MultiTheme')->allow;
    }

    /**
     * 切换主题
     *
     * @access public
     * @return void
     */
    public static function load()
    {
        Typecho_Widget::widget('Widget_Options')->to($options);
        $options->theme = $options->plugin('MultiTheme')->default;
        $themeByKey = $options->plugin('MultiTheme')->field;
        $allowThemes = $options->plugin('MultiTheme')->allow;

        $request = Typecho_Request::getInstance();
        $themeFromRequest = $request->get($themeByKey);

        if($themeFromRequest){
            if(in_array($themeFromRequest, $allowThemes)) {
                $theme = $themeFromRequest;
            }else{
                $theme = null;
            }
        }else{
            $theme = Typecho_Cookie::get('__typecho_multi_theme');
        }
        if($theme && in_array($theme, $allowThemes)){
            Typecho_Cookie::set('__typecho_multi_theme', $theme);
            $options->theme = $theme;
        }
        return;
    }


}
