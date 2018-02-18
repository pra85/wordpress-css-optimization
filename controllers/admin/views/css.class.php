<?php
namespace O10n;

/**
 * CSS Optimization Admin View Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     o10n-x <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewCss extends AdminViewBase
{
    protected static $view_key = 'css'; // reference key for view
    protected $module_key = 'css';

    // default tab view
    private $default_tab_view = 'intro';
    
    /**
     * Load controller
     *
     * @param  Core       $Core Core controller instance.
     * @param  string     $View View key.
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminForm',
            'AdminClient',
            'json'
        ));
    }
    
    /**
     * Setup controller
     */
    protected function setup()
    {
        // set view etc
        parent::setup();
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
        // process form submissions
        add_action('o10n_save_settings_verify_input', array( $this, 'verify_input' ), 10, 1);

        // enqueue scripts
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), $this->first_priority);
    }

    /**
     * Return help tab data
     */
    final public function help_tab()
    {
        $data = array(
            'name' => __('CSS Optimization', 'o10n'),
            'github' => 'https://github.com/o10n-x/wordpress-css-optimization',
            'wordpress' => 'https://wordpress.org/support/plugin/css-optimization',
            'docs' => 'https://github.com/o10n-x/wordpress-css-optimization/tree/master/docs'
        );

        return $data;
    }

    /**
     * Enqueue scripts and styles
     */
    final public function enqueue_scripts()
    {
        // skip if user is not logged in
        if (!is_admin() || !is_user_logged_in()) {
            return;
        }

        // set module path
        $this->AdminClient->set_config('module_url', $this->module->dir_url());

        // global admin script
        wp_enqueue_script('o10n_view_css', $this->module->dir_url() . 'admin/js/view-css.js', array( 'jquery', 'o10n_cp' ), $this->module->version());
    }

    /**
     * Return view template
     */
    public function template($view_key = false)
    {

        // template view key
        $view_key = false;

        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : $this->default_tab_view;
        if ($tab) {
            switch ($tab) {
                case "optimization":
                    $view_key = 'css';
                break;
                case "delivery":
                case "critical":
                case "editor":
                case "intro":
                    $view_key = 'css-' . $tab;
                break;
                default:
                    throw new Exception('Invalid view ' . esc_html($view_key), 'core');
                break;
            }
        }

        return parent::template($view_key);
    }
    
    /**
     * Verify settings input
     *
     * @param  object   Form input controller object
     */
    final public function verify_input($forminput)
    {
        $tab = (isset($_REQUEST['tab'])) ? trim($_REQUEST['tab']) : 'optimization';
        switch ($tab) {
            case "optimization":

                // CSS code optimization

                $forminput->type_verify(array(
                    'css.minify.enabled' => 'bool',

                    'css.minify.filter.enabled' => 'bool',
                    'css.minify.filter.type' => 'string',
                    'css.minify.filter.include' => 'newline_array',
                    'css.minify.filter.exclude' => 'newline_array',

                    'css.minify.concat.enabled' => 'bool',
                    'css.minify.concat.minify' => 'bool',

                    'css.minify.concat.filter.enabled' => 'bool',
                    'css.minify.concat.filter.type' => 'string',
                    'css.minify.concat.filter.config' => 'json-array',

                    'css.minify.concat.mediaqueries.enabled' => 'bool',
                    'css.minify.concat.mediaqueries.filter.enabled' => 'bool',
                    'css.minify.concat.mediaqueries.filter.type' => 'string',
                    'css.minify.concat.mediaqueries.filter.include' => 'newline_array',
                    'css.minify.concat.mediaqueries.filter.exclude' => 'newline_array',

                    'css.minify.concat.inline.enabled' => 'bool',
                    'css.minify.concat.inline.filter.enabled' => 'bool',
                    'css.minify.concat.inline.filter.type' => 'string',
                    'css.minify.concat.inline.filter.include' => 'newline_array',
                    'css.minify.concat.inline.filter.exclude' => 'newline_array',

                    'css.replace' => 'json-array',

                    'css.url_filter.enabled' => 'bool',
                    'css.url_filter.config' => 'json-array'
                ));

                $filters_options = array_keys((array)$this->AdminForm->schema_option('css.cssmin.filters')->properties);
                array_walk($filters_options, function (&$value, $key) {
                    $value = 'css.cssmin.filters.' . $value;
                });
                $plugins_options = array_keys((array)$this->AdminForm->schema_option('css.cssmin.plugins')->properties);
                array_walk($plugins_options, function (&$value, $key) {
                    $value = 'css.cssmin.plugins.' . $value;
                });
                $cssmin_options = array_flip(array_merge($filters_options, $plugins_options));
                array_walk($cssmin_options, function (&$value, $key) {
                    $value = 'bool';
                });
                $forminput->type_verify($cssmin_options);

                // verify search & replace
                $cssreplace = $forminput->get('css.replace', 'json-array', array());
                if (!empty($cssreplace)) {
                    $searchreplace = array();
                    $position = 0;
                    foreach ($cssreplace as $cnf) {
                        if (!is_array($cnf) || !isset($cnf['search']) || !isset($cnf['replace'])) {
                            continue;
                        }
                        $position++;
                        if (isset($cnf['regex'])) {
                            // exec preg_match on null
                            $valid = @preg_match($cnf['search'], null);
                            $error = $this->is_preg_error();
                            if ($valid === false || $error) {
                                throw new Exception('<code>'.esc_html($cnf['search']).'</code> is an invalid regular expression and has been removed.' . (($error) ? '<br /><p>Error: '.$error.'</p>' : ''), 'settings');
                            }
                        }
                        $searchreplace[] = $cnf;
                    }
                    $cssreplace = $searchreplace;
                }

                // set search & replace
                $forminput->set('css.replace', $cssreplace);
            break;
            case "delivery":

                // CSS delivery optimization

                $forminput->type_verify(array(
                    'css.async.enabled' => 'bool',
                    'css.async.rel_preload' => 'bool',
                    'css.async.noscript' => 'bool',

                    'css.async.filter.enabled' => 'bool',
                    'css.async.filter.type' => 'string',
                    'css.async.filter.config' => 'json-array',

                    'css.async.load_position' => 'string',
                    'css.async.render_timing.enabled' => 'bool',

                    'css.http2_push.enabled' => 'bool',
                    'css.http2_push.filter.enabled' => 'bool',
                    'css.http2_push.filter.type' => 'string',
                    'css.http2_push.filter.include' => 'newline_array',
                    'css.http2_push.filter.exclude' => 'newline_array',

                    'css.async.localStorage.enabled' => 'bool',
                    'css.async.localStorage.max_size' => 'int',
                    'css.async.localStorage.expire' => 'int',
                    'css.async.localStorage.update_interval' => 'int',
                    'css.async.localStorage.head_update' => 'bool',

                    'css.proxy.enabled' => 'bool',
                    'css.proxy.include' => 'newline_array',
                    'css.proxy.capture.enabled' => 'bool',
                    'css.proxy.capture.list' => 'json-array',

                    'css.cdn.enabled' => 'bool'
                ));

                // load timing
                if ($forminput->get('css.async.load_position') === 'timing') {
                    $forminput->type_verify(array(
                        'css.async.load_timing.type' => 'string'
                    ));

                    if ($forminput->get('css.async.load_timing.type') === 'requestAnimationFrame') {
                        $forminput->type_verify(array(
                            'css.async.load_timing.frame' => 'int-empty'
                        ));
                    }

                    if ($forminput->get('css.async.load_timing.type') === 'requestIdleCallback') {
                        $forminput->type_verify(array(
                            'css.async.load_timing.timeout' => 'int-empty',
                            'css.async.load_timing.setTimeout' => 'int-empty'
                        ));
                    }
            
                    if ($forminput->get('css.async.load_timing.type') === 'inview') {
                        $forminput->type_verify(array(
                            'css.async.load_timing.selector' => 'string',
                            'css.async.load_timing.offset' => 'int-empty'
                        ));
                    }

                    if ($forminput->get('css.async.load_timing.type') === 'media') {
                        $forminput->type_verify(array(
                            'css.async.load_timing.media' => 'string'
                        ));
                    }
                }

                // render timing
                if ($forminput->bool('css.async.render_timing.enabled')) {
                    $forminput->type_verify(array(
                        'css.async.render_timing.type' => 'string'
                    ));

                    if ($forminput->get('css.async.render_timing.type') === 'requestAnimationFrame') {
                        $forminput->type_verify(array(
                            'css.async.render_timing.frame' => 'int-empty'
                        ));
                    }

                    if ($forminput->get('css.async.render_timing.type') === 'requestIdleCallback') {
                        $forminput->type_verify(array(
                            'css.async.render_timing.timeout' => 'int-empty',
                            'css.async.render_timing.setTimeout' => 'int-empty'
                        ));
                    }
        
                    if ($forminput->get('css.async.render_timing.type') === 'inview') {
                        $forminput->type_verify(array(
                        'css.async.render_timing.selector' => 'string',
                        'css.async.render_timing.offset' => 'int-empty'
                    ));
                    }

                    if ($forminput->get('css.async.render_timing.type') === 'media') {
                        $forminput->type_verify(array(
                        'css.async.render_timing.media' => 'string'
                    ));
                    }
                }

                if ($forminput->bool('css.cdn.enabled')) {
                    $forminput->type_verify(array(
                        'css.cdn.http2_push' => 'bool',
                        'css.cdn.url' => 'string',
                        'css.cdn.mask' => 'string',
                    ));
                }
            break;
            default:
                throw new Exception('Invalid view ' . esc_html($tab), 'core');
            break;
        }
    }
}
