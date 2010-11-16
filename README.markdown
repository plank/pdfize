About
-----

A simple component to generate PDF output from any controller/action.

Uses dompdf (v0.6.0beta1) for PDF generation. http://code.google.com/p/dompdf/

You could probably plug it in to a different engine - since this plugin was started, there have been a few others that look promising. But at the time, dompdf was the simplest I could find that did what we needed it to do.

Should work with CakePHP 1.2 & 1.3. I've used it with both. You can see an example of its usage in the [expozine](https://github.com/meeech/expozine/commit/dc5e2d775c39a39a615887735a5a0413ae76143b) project.

Things you should know
----------------------

* [dompdf](http://code.google.com/p/dompdf/) in the pdfize/vendors folder. As a convenience, I have included that library with this plugin. You should go there to make sure you have the laest copy of the lib. 
* PDFize will look for **views/layouts/pdf.ctp**. If that doesn't exist, will fallback to **plugins/pdfize/views/layouts/pdf.ctp**
    * re: **views/layouts/pdf.ctp** Treat it just like any other layout. 
* You will need a view for the action, as usual. 
* For images, you will probably need to output the full http:// path. I didn't have much luck with relative. 
* The PDF file outputted is named based on Inflector::slug($controller->pageTitle), otherwise it falls back on Inflector::slug($controller->name.' '.$controller->action)
* Depending on the amount of images and whatnot, you might have to up your memory limit.
In PdfComponent::shutdown() we use **ini\_set('memory_limit', '96M')**; 
* Make sure your webserver can write to **pdfize/vendors/dompdf/lib/fonts/** (usually this involves just chmod 777 that directory)

Usage
-----

In your controller, add it to $components. Pass in an array with some config info: 

<pre>
public $components = array('Pdfize.Pdf' => array(
        'actions' => array('admin_pdf'),
        'debug' =>false,
        'size' => 'legal',
        'orientation' => 'landscape'
    ));
</pre>

* **actions**: (required) Array of names of the actions in the controller that you want PDFize to hijack and output as a pdf.
* **debug**: Set to TRUE to output to browser instead of generating pdf.
* **size**: default is **legal**. For the full list of valid sizes, see CPDF_Adapter::PAPER_SIZES **dompdf/include/cpdf_adapter.cls.php**
* **orientation**: **landscape** (default) or **portrait**

