<?php
/**
 * PDF Helper.
 * used to generate image links we can use for PDF generation. Needs full path, or relative path
 *
 * @package TAP
 * @author Mitchell Amihod
 **/
class PdfHelper extends HtmlHelper {

    public $helpers = array('Html');

/**
 * Wrapper to make Image
 *
 * DOMPDF images arent seeming to work with the image tag paths that cakephp generates. 
 * Either needs a simple relative path it seems, or use a full url and make sure to enable remote for dompdf.
 * So, here, we create the relative path it can work from.
 * This is so we can NOT enable remote, which doesn't work behind htaccess protected sites, yet still allow the images to be found.
 *
 * @param string $path Path to the image file, relative to the app/webroot/img/ directory.
 * @param array $options Array of HTML attributes.
 * @param bool  $pdf Whether we need the img tag for a PDF generation vs regular browser display
 *                  When making a PDF, the path needs to be relative for the pdf class to make it
 * @return string
 */
    function image($path, $options = array(), $pdf = false) {

        if(!$pdf) {
            return $this->Html->image($path, $options);
        }

        $path = IMAGES_URL.$path;

        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }

        $url = false;
        if (!empty($options['url'])) {
            $url = $options['url'];
            unset($options['url']);
        }

        $image = sprintf($this->tags['image'], $path, $this->_parseAttributes($options, null, '', ' '));

        if ($url) {
            return $this->output(sprintf($this->tags['link'], $this->url($url), null, $image));
        }

        return $this->output($image);
    }

}