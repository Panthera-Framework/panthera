<?php
/**
  * Printing module - allows rendering alternative printable page views
  *
  * @package Panthera\modules\boot\print
  * @author Damian Kęska
  * @license GNU LGPLv3, see license.txt
  */

/**
 * This module allows rendering alternative page views eg. pdfs, printable html pages
 *
 * @package Panthera\modules\boot\print
 * @author Damian Kęska
 */

class printingModule
{
    public static $printPDFName = null;

    public static function render()
    {
        global $panthera;

        if ($_GET['__print'] == 'pdf')
        {
            $panthera -> add_option('template.display', array('printingModule', 'printPDF'));
            return True;
        }

        $panthera -> add_option('template.display', array('printingModule', 'printPlain'));
    }

    /*
     * PDF printer for Panthera Template
     *
     * @param $name Template name
     * @author Damian Kęska
     * @return modified template name to display
     */

    public static function printPlain($name, $details)
    {
        global $panthera;

        $panthera -> remove_option('template.display', array('printingModule', 'printPlain'));

        if (libtemplate::exists($panthera -> template -> name, 'printable/' .$name))
        {
            $panthera -> get_options('printing.plain', $name, $details);
            $panthera -> logging -> output('Preparing print template "printable/' .$name. '" from ' .$panthera -> template -> name, 'printingModule');
            return 'printable/' .$name;
        } else {
            $panthera -> logging -> output('Cannot find plain template "printable/' .$name. '" from ' .$panthera -> template -> name, 'printingModule');
        }

        return $name;
    }

    /*
     * PDF printer for Panthera Template
     *
     * @param $name Template name
     * @author Damian Kęska
     * @return null
     */

    public static function printPDF($name, $details)
    {
        global $panthera;

        // remove self hook
        $panthera -> remove_option('template.display', array('printingModule', 'printPDF'));

        if (libtemplate::exists($panthera -> template -> name, 'printable-pdf/' .$name))
        {
            $panthera -> get_options('printing.pdf', $name, $details);
            $panthera -> logging -> output('Preparing print PDF template "printable-pdf/' .$name. '" from ' .$panthera -> template -> name, 'printingModule');

            // check if template is embedded (only rendered, not displayed) if yes redirect to printable version
            if ($details['renderOnly'])
            {
                return 'printable-pdf/' .$name;
            }


            include_once PANTHERA_DIR. '/share/mpdf/mpdf.php';

            $content = $panthera -> template -> compile('printable-pdf/' .$name);
            $mpdf = new mPDF();
            $mpdf -> WriteHTML($content);
            $pdfContent = $mpdf -> Output('','S');
            header('Content-type: application/x-pdf');

            if (!self::$printPDFName)
            {
                self::$printPDFName = 'print.pdf';
            }

            header('Content-disposition: attachment; filename="' .self::$printPDFName. '"');

            print($pdfContent);

            pa_exit();
        }

        $panthera -> logging -> output('Cannot find PDF template "printable-pdf/' .$name. '" from ' .$panthera -> template -> name, 'printingModule');
    }
}