<?php
/**
 * PdfComponent
 *
 * Tested: Cake 1.2
 *
 * Simple glue between dompdf (http://www.digitaljunkies.ca/dompdf/) and cakephp
 * Allows setting any controller action as PDF output.
 *
 * debugkit toolbar aware.
 *
 * @author Mitchell Amihod
 */
class PdfComponent extends Object {

    /**
     * Array of controller actions to convert the output to pdf
     *
     * @var array
     **/
    public $actionsToPdf = array();

    /**
     * If set to true, we don't try to pdf the output - just display the html.
     *
     * @var string
     **/
    public $debug = false;

    /**
     * What paper size to generate the PDF for. 
     *
     * @var string 'letter', 'legal', 'A4', etc. {@link CPDF_Adapter::$PAPER_SIZES}
     **/
    public $size = 'legal';

    /**
     * Orientation of the PDF
     *
     * @var string 'portrait' or 'landscape'
     **/
    public $orientation = 'landscape';

    /**
     * Path to the plugin. Generated in initialize
     *
     * @var string
     **/
    private $pluginPath;


    /**
     * A nice place to set up some dompdf constants
     * Read more about them in
     *
     * dompdf_config.inc.php
     *
     * Really. Browse through that file, all the constants below have inline docs explaining them.
     * There's a lot of things you can control/setup.
     * For our case, we needed just some basic settings, and left the rest of the defaults.
     *
     * For the constants being set here, the dompdf_config.inc.php has !defined() checks before trying to set the defaults.
     *
     * @todo look at integrating into passed in $settings['constants']
     * @return void
     **/
    private function _dompdfDefines() {
        // Allow remote fetching(needed for images)
        define("DOMPDF_ENABLE_REMOTE", true);

        define("DOMPDF_UNICODE_ENABLED", true);

        define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");

        define("DOMPDF_DEFAULT_FONT", "serif");

        define("DOMPDF_ENABLE_PHP", false);

    }


    /**
     * Initialization of the Pdf component
     *
     * @param object Controller reference object
     * @param array Optional settings that this component can parse.
     */
    public function initialize(&$controller, $settings = array()) {

        $this->actionsToPdf = $settings['actions'];
        $this->debug = (isset($settings['debug'])) ? $settings['debug'] : $this->debug ;
        $this->size = (isset($settings['size'])) ? $settings['size'] : $this->size ;
        $this->orientation = (isset($settings['orientation'])) ? $settings['orientation'] : $this->orientation ;

        $this->pluginPath = APP.'plugins/pdfize/';

        //Turn off debugkit toolbar output, else PDFs dont generate.
        //Turn out is a load order issue. DebugKit only checks configure::debug when it initializes,
        //But, since it inits before the PDF component sets debug, it ignores it.
        if($this->debug == false) {
            //We use array_key_exists instead of isset, cuz isset thinks its checking an obj
            if(array_key_exists('DebugKit.Toolbar', $controller->components)) {
                $controller->Component->_loaded['Toolbar']->enabled = false;
            }
        }
    }

    public function beforeRender(&$controller) {
        if(!in_array($controller->action, $this->actionsToPdf)) { return true; }

        //Default to the plugins pdf layout
        $controller->layout = '../../plugins/pdfize/views/layouts/pdf';
        //Check for App Level pdf layout.
        if(file_exists(LAYOUTS.'pdf.ctp')) {
            $controller->layout = 'pdf';
        }

    }

    /**
     * Figure out a name for the PDF file
     *
     * @return string
     **/
    function _fileName(&$controller) {
        $filename = false;

        if(property_exists($controller, 'pageTitle')) {
            $filename = Inflector::slug($controller->pageTitle);
        } else {
            $filename = Inflector::slug($controller->name.' '.$controller->action);
        }

        return $filename.'.pdf';
    }


    public function shutdown(&$controller) {
        if(!in_array($controller->action, $this->actionsToPdf)) { return true; }

        if($this->debug) {
            return true;
        }
        //Turn off any debug output
        Configure::write('debug', 0);
        ini_set('memory_limit', '96M');
        //grab the output
        $html = $controller->output;
        //Wipe out the output - we dont want anything sent to the browser.
        $controller->output = false;

        //Some DOMPDF configs
        $this->_dompdfDefines();

        if(!class_exists('DOMPDF')){
            //App::import isn't doing the trick, I suspect since dompdf_config.inc.php leans on autoloading?
            require_once($this->pluginPath."vendors/dompdf/dompdf_config.inc.php");
        }
        $filename = $this->_fileName($controller);

        // Uncomment for debugging
        // global $_dompdf_show_warnings;
        // global $_dompdf_debug;
        // global $_DOMPDF_DEBUG_TYPES;

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);

        $dompdf->set_paper($this->size, $this->orientation);
        $dompdf->render();

        //We might need basepath for images, css.
        //base path? debug(WWW_ROOT);

        // uncomment for debugging
        // if ( $_dompdf_show_warnings ) {
        //   global $_dompdf_warnings;
        //   foreach ($_dompdf_warnings as $msg){
        //     $this->log($msg);
        //   }
        //   $this->log($dompdf->get_canvas()->get_cpdf()->messages);
        // }

        //Dom PDF takes care of all the headers and whatnot.
        $dompdf->stream($filename);

    }

}