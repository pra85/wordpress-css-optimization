<?php
namespace O10n;

/**
 * Critical CSS Optimization Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers
 * @author     o10n-x <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class Criticalcss extends Controller implements Controller_Interface
{
    private $debug_view = false;

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
            'env',
            'client',
            'file',
            'cache',
            'options',
            'url'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        // disabled
        if (!$this->env->is_optimization()) {
            return;
        }

        /**
         * Load critical css in template_redirect hook, apply conditions etc.
         */
        add_action('template_redirect', array($this,'load_critical_css'), $this->first_priority);
    }

    /**
     * Load critical CSS
     */
    final public function load_critical_css()
    {
        if (!is_admin() && (isset($_GET['o10n-css']) || isset($_GET['o10n-no-css']))) {
            if (!$this->options->bool('css.critical.editor_public.enabled') && (!is_user_logged_in() || !current_user_can('manage_options'))) {
                wp_die('No permission');
            }

            // critical css editor
            if (isset($_GET['o10n-css'])) {
                add_filter('o10n_html_final', array($this, 'editor_view'), PHP_INT_MAX);
            }

            // no CSS display, filter out CSS
            if (isset($_GET['o10n-no-css'])) {
                add_filter('o10n_stylesheet_pre', function () {
                    return 'delete';
                }, PHP_INT_MAX);

                // mark iframe view
                $this->debug_view = true;

                add_filter('o10n_html_final', array($this, 'editor_iframe_view'), PHP_INT_MAX);
            }
        }

        $files = $this->options->get('css.critical.files');
        if (empty($files)) {
            return;
        }

        // critical css directory
        $critical_css_directory = $this->file->theme_directory(array('critical-css'));

        // method cache
        $method_cache = array();
        $method_param_cache = array();

        $active_files = array();
        $updated = false; // file index updated?

        foreach ($files as $index => $file) {

            // index conditions
            if (isset($file['conditions'])) {

                // verify if database is up to date
                $conditions_file = $critical_css_directory . substr($file['file'], 0, -4) . '.json';
                if (!file_exists($critical_css_directory)) {

                    // conditions file removed
                    unset($files[$index]['conditions']);
                    $updated = true;
                } else {

                    // file modified, update conditions
                    $modified_time = filemtime($conditions_file);
                    if ($file['conditions'][0] !== $modified_time) {
                        try {
                            $conditions = $this->json->parse(file_get_contents($conditions_file), true);
                        } catch (\Exception $err) {
                            $conditions = false;
                        }
                        $files[$index]['conditions'][0] = $modified_time;
                        $files[$index]['conditions'][1] = $conditions;
                    } else {
                        $conditions = $file['conditions'][1];
                    }

                    if ($conditions) {

                        // process conditions
                        $match = false;
                        foreach ($conditions as $match_any) {
                            if (!is_array($match_any)) {
                                continue;
                            }

                            // single condition
                            if (!isset($match_any[0]) || !is_array($match_any[0])) {
                                $match_all = array($match_any);
                            } else {
                                $match_all = $match_any;
                            }

                            $group_match = true;
                            foreach ($match_all as $condition) {

                                // method to call
                                $method = (isset($condition['method'])) ? $condition['method'] : false;
                    
                                // verify method
                                if (!$method || !function_exists($method)) {
                                    throw new Exception('Invalid condition method specified in '.$this->file->safe_path(O10N_CONFIG_DIR . $filename).' ('.$method.').', 'config');
                                }

                                // parameters to apply to method
                                $params = (isset($condition['params'])) ? $condition['params'] : null;

                                // result to expect from method
                                $expected_result = (isset($condition['result'])) ? $condition['result'] : true;

                                // call method
                                if ($params === null) {
                                    if (isset($method_cache[$method])) {
                                        $result = $method_cache[$method];
                                    } else {
                                        $result = $method_cache[$method] = call_user_func($method);
                                    }
                                } else {
                                    $params_key = json_encode($params);

                                    if (isset($method_cache[$method]) && isset($method_cache[$method][$params_key])) {
                                        $result = $method_cache[$method][$params_key];
                                    } else {
                                        if (!isset($method_param_cache[$method])) {
                                            $method_param_cache[$method] = array();
                                        }
                                        $result = $method_param_cache[$method][$params_key] = call_user_func_array($method, $params);
                                    }
                                }

                                // expected result is array of options
                                if (is_array($expected_result)) {
                                    if (!in_array($result, $expected_result, true)) {
                                        $group_match = false; // group doesn't match
                            
                                        break 1; // stop processing condition (group)
                                    }
                                } else {
                                    if ($result !== $expected_result) {
                                        $group_match = false; // group doesn't match
                            
                                        break 1; // stop processing condition (group)
                                    }
                                }
                            }

                            if ($group_match) {
                                $match = true; // match found
                    
                                break 1; // stop processing conditions
                            }
                        }

                        if ($match) {
                            $active_files[] = $file;
                        }
                        continue 1;
                    }
                }
            }
            $active_files[] = $file;
        }

        if ($updated) {
            $this->options->save(array('css.critical.files' => $files));
        }

        // no active critical CSS files
        if (empty($active_files)) {
            return;
        }

        // sort by priority
        usort($active_files, function ($a, $b) {
            if (intval($a['priority']) === intval($b['priority'])) {
                return 0;
            }

            return (intval($a['priority']) < intval($b['priority'])) ? -1 : 1;
        });

        $criticalcss = array();
        foreach ($active_files as $file) {
            if (isset($file['file'])) {
                $filepath = $this->file->theme_directory(array('critical-css')) . $file['file'];

                // check if file exists
                if (file_exists($filepath)) {
                    $source = trim(file_get_contents($filepath));

                    if ($this->debug_view) {
                        $cssdata = "\n\n/*\n * @file " . $file['file'];

                        if (isset($file['title'])) {
                            $cssdata .= "\n * @title " . $file['title'];
                        }

                        if (isset($file['priority'])) {
                            $cssdata .= "\n * @priority " . $file['priority'];
                        }

                        if (isset($file['conditions']) && isset($file['conditions'][1])) {
                            $cssdata .= "\n * @conditions " . json_encode($file['conditions'][1]);
                        }

                        $cssdata .= "\n */\n" . $source;
                    } else {
                        $cssdata = $source;
                    }

                    $criticalcss[] = $cssdata;
                }
            }
        }

        // concat CSS
        $criticalcss = implode(' ', $criticalcss);

        if ($this->debug_view) {
            $criticalcss = "/**\n * Critical CSS Editor\n *\n * The extracted Critical CSS has been annotated with file references. \n * The Critical CSS source files are located in the theme directory ".$this->file->safe_path($this->file->theme_directory(array('critical-css')))."\n */\n" . $criticalcss;
        }

        // HTTP/2 Server Push or minify, store in file
        $http2 = ($this->options->bool('css.critical.http2') && $this->core->module_loaded('http2'));
        $concat_url = false;

        if ($http2 && $this->env->is_ssl()) {

            // concat hash
            $concat_hash = md5($criticalcss);
 
            // load from cache
            if ($this->cache->exists('css', 'concat', $concat_hash)) {

                // preserve cache file based on access
                $this->cache->preserve('css', 'concat', $concat_hash, (time() - 3600));

                // get CSS from cache
                $cachecss = $this->cache->get('css', 'concat', $concat_hash);
                if ($cachecss) {
                    $criticalcss = $cachecss;
                }
            } else {

                // store cache file
                $this->cache->put('css', 'concat', $concat_hash, $criticalcss, 'critical');
            }

            // concat URL
            $concat_url = $this->url->remove_host($this->cache->url('css', 'concat', $concat_hash));
        }

        // HTTP/2 Server Push, add direct link to stylesheet
        if ($http2 && $concat_url) {

            // add to header
            $this->client->at('critical-css', '<link '.(($this->debug_view) ? 'id="o10n-critical-css" ' : '').'data-o10n rel="stylesheet" href="'.$concat_url.'"/>');
        } else {

            // add to header
            $this->client->at('critical-css', '<style '.(($this->debug_view) ? 'id="o10n-critical-css" ' : '').'data-o10n="critical">'.$criticalcss.'</style>');
        }
    }

    /**
     * Critical CSS editor view
     */
    final public function editor_view($HTML)
    {
        if (stripos($HTML, "<html") === false || stripos($HTML, "<xsl:stylesheet") !== false) {
            // not valid HTML
            return $HTML;
        }

        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $parsed = array();
        parse_str(substr($url, strpos($url, '?') + 1), $parsed);
        $extractkey = $parsed['extract-css'];
        unset($parsed['o10n-css']);
        unset($parsed['output']);
        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . preg_replace('|\?.*$|Ui', '', $_SERVER['REQUEST_URI']);
        if (!empty($parsed)) {
            $url .= '?' . http_build_query($parsed);
        }

        /**
         * Print compare critical CSS page
         */
        $plugin_uri = $this->core->modules('css')->dir_url();
        require_once($this->core->modules('css')->dir_path() . 'includes/critical-css-editor.inc.php');

        return $output;
    }

    /**
     * Critical CSS editor iframe view
     */
    final public function editor_iframe_view($HTML)
    {
        if (stripos($HTML, "<html") === false || stripos($HTML, "<xsl:stylesheet") !== false) {
            // not valid HTML
            return $HTML;
        }

        // iframe
        $iframe_script = '<script src="' . $this->core->modules('css')->dir_url() . 'public/js/view-css-editor-iframe.js"></script>';

        if (preg_match('/(<head[^>]*>)/Ui', $HTML, $out)) {
            $HTML = str_replace($out[0], $out[0] . $iframe_script, $HTML);
        } else {
            $HTML .= $iframe_script;
        }

        return $HTML;
    }
}
