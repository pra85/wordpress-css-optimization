<?php
namespace O10n;

/**
 * Global CSS Optimization Admin Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     o10n-x <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminGlobalcss extends ModuleAdminController implements Module_Admin_Controller_Interface
{

    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'client'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {

        // add admin bar menu
        add_action('admin_bar_menu', array( $this, 'admin_bar'), 100);

        $this->client->after('client', '<script>o10n.constructor.prototype.extract=function(t){(function(d,c,s){s=d.createElement(\'script\');s.async=true;s.onload=c;s.src=' . json_encode($this->core->modules('css')->dir_url() . 'public/js/critical-css-widget.min.js').';d.head.appendChild(s);})(document,function(){o10n.extract(t);});}</script>'); // critical-css-widget.min.js
    }
     
    /**
     * Admin bar option
     *
     * @param  object       Admin bar object
     */
    final public function admin_bar($admin_bar)
    {
        // current url
        if (is_admin()
            || (defined('DOING_AJAX') && DOING_AJAX)
            || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
        ) {
            $currenturl = home_url();
        } else {
            $currenturl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        // WPO plugin or more than 1 optimization module, add to optimization menu
        if (defined('O10N_WPO_VERSION') || count($this->core->modules()) > 1) {
            $admin_bar->add_menu(array(
                'id' => 'o10n',
                'title' => '<span class="ab-label">' . __('o10n', 'o10n') . '</span>',
                'href' => add_query_arg(array( 'page' => 'o10n' ), admin_url('admin.php')),
                'meta' => array( 'title' => __('Performance Optimization', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_bar->add_node(array(
                'parent' => 'o10n',
                'id' => 'o10n-css',
                'title' => __('CSS Optimization', 'o10n'),
                'href' => add_query_arg(array( 'page' => 'o10n-css' ), admin_url('admin.php'))
            ));

            $admin_base = 'admin.php';
        } else {
            $admin_bar->add_menu(array(
                'id' => 'o10n-css',
                'title' => '<span class="ab-label">' . __('CSS', 'o10n') . '</span>',
                'href' => add_query_arg(array( 'page' => 'o10n-css' ), admin_url('themes.php')),
                'meta' => array( 'title' => __('CSS Optimization', 'o10n'), 'class' => 'ab-sub-secondary' )
            ));

            $admin_base = 'themes.php';
        }

        // critical CSS quality test
        $admin_bar->add_node(array(
            'parent' => 'o10n-css',
            'id' => 'o10n-css-editor',
            'title' => '<span class="dashicons dashicons-editor-code" style="font-family:dashicons;margin-top:-3px;margin-right:4px;"></span> ' . __('CSS Editor', 'o10n'),
            'href' => add_query_arg(array( 'page' => 'o10n-css-editor' ), admin_url('themes.php')),
            'meta' => array( 'title' => __('CSS Editor', 'o10n') )
        ));

        // critical CSS quality test
        $critical_css_editor_url = preg_replace('|\#.*$|Ui', '', $currenturl) . ((strpos($currenturl, '?') !== false) ? '&' : '?') . 'o10n-css=1';
        $admin_bar->add_node(array(
            'parent' => 'o10n-css',
            'id' => 'o10n-critical-css-editor',
            'title' => '<span class="dashicons dashicons-image-flip-horizontal" style="font-family:dashicons;margin-top:-3px;margin-right:4px;"></span> ' . __('Critical CSS Editor', 'o10n'),
            'href' => $critical_css_editor_url,
            'meta' => array( 'title' => __('Critical CSS Editor (split view)', 'o10n'), 'target' => '_blank', )
        ));

        // extract Critical CSS
        $admin_bar->add_node(array(
            'parent' => 'o10n-css',
            'id' => 'o10n-extract-critical-css-widget',
            'title' => '<span class="dashicons dashicons-download" style="font-family:dashicons;margin-top:-3px;margin-right:4px;"></span> ' . __('Extract Critical CSS (widget)', 'o10n'),
            'href' => $critical_css_editor_url . '#editor',
            'meta' => array(
                'title' => ((is_admin()) ? __('Use on the frontend to start download directly. On the admin panel, this link opens the editor.', 'o10n') : __('Extract Critical CSS via Javascript widget', 'o10n')),
                'target' => '_blank',
                'onclick' => ((is_admin()) ? '' : 'o10n.extract();return false;')
            )
        ));
   
        $admin_bar->add_node(array(
            'parent' => 'o10n-css',
            'id' => 'o10n-extract-full-css-widget',
            'title' => '<span class="dashicons dashicons-download" style="font-family:dashicons;margin-top:-3px;margin-right:4px;"></span> ' . __('Extract Full CSS (widget)', 'o10n'),
            'href' => $critical_css_editor_url,
            'meta' => array(
                'title' => ((is_admin()) ? __('Use on the frontend to start download directly. On the admin panel, this link opens the editor.', 'o10n') : __('Extract Full CSS via Javascript widget', 'o10n')),
                'target' => '_blank',
                'onclick' => ((is_admin()) ? '' : "o10n.extract(\"full\");return false;")
            )
        ));
    }
}
