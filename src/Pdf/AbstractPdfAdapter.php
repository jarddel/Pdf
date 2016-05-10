<?php

/*
 * This file is part of the UCSDMath package.
 *
 * Copyright 2016 UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace UCSDMath\Pdf;

use Carbon\Carbon;
use UCSDMath\Functions\ServiceFunctions;
use UCSDMath\Functions\ServiceFunctionsInterface;

/**
 * AbstractPdfAdapter provides an abstract base class implementation of {@link PdfInterface}.
 * Primarily, this services the fundamental implementations for all Pdf classes.
 *
 * This component library is an adapter to the mPDF library.
 *
 * Method list: (+) @api, (-) protected or private visibility. (+) @api, (-) protected or private.
 *
 * (+) PdfInterface __construct();
 * (+) void __destruct();
 * (+) setPageSizeLegal();
 * (+) setPageAsPortrait();
 * (+) setPageSizeLetter();
 * (+) appendPageCSS($str);
 * (+) setPageAsLandscape();
 * (+) appendPageContent($str);
 * (+) setMetaTitle($str = null);
 * (+) setMetaAuthor($str = null);
 * (+) setMetaCreator($str = null);
 * (+) setMetaSubject($str = null);
 * (+) setHeader(array $data = null);
 * (+) setFooter(array $data = null);
 * (+) setFontType($fontname = null);
 * (+) setPageSize($pageSize = null);
 * (+) getFontFamily($fontname = null);
 * (+) setMarginTop($marginTop = null);
 * (+) setMargins(array $setting = null);
 * (+) setMarginLeft($marginLeft = null);
 * (+) setMarginRight($marginRight = null);
 * (+) setMetaKeywords(array $words = null);
 * (+) setMarginBottom($marginBottom = null);
 * (+) setMarginHeader($marginHeader = null);
 * (+) setMarginFooter($marginFooter = null);
 * (+) registerPageFormat($pageSize = null, $orientation = null);
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 */
abstract class AbstractPdfAdapter implements PdfInterface, ServiceFunctionsInterface
{
    /**
     * Constants.
     *
     * @var string VERSION  A version number
     *
     * @api
     */
    const VERSION = '1.7.0';

    // --------------------------------------------------------------------------

    /**
     * Properties.
     *
     * @var    mPDF         $mpdf               A mPDF Interface
     * @var    string       $pageHeader         A page header content to render
     * @var    array        $pageFooter         A page footer content to render
     * @var    string       $characterEncoding  A default character encoding
     * @var    int          $fontSize           A default font size specified in points (pt. [12], 14, 18, etc.)
     * @var    string       $fontType           A default font typeface ([Times], '','','')
     * @var    string       $filename           A default document or filename
     * @var    string       $outputDestination  A default destination where to send the document ([I], D, F, S)
     * @var    string       $pageCSS            A page style setting
     * @var    string       $pageContent        A page content to render
     * @var    string       $pageSize           A page size (['Letter'],'Legal','A4','Tabloid', etc.)
     * @var    string       $pageFormat         A page size and orientation scheme (['Letter'],'Legal-L') based in millimetres (mm)
     * @var    string       $pageOrientation    A setup orientation (['Portrait'],'Landscape')
     * @var    int          $marginTop          A top margin size specified as length in millimetres (mm)
     * @var    int          $marginRight        A right margin size specified as length in millimetres (mm)
     * @var    int          $marginBottom       A bottom margin size specified as length in millimetres (mm)
     * @var    int          $marginLeft         A left margin size specified as length in millimetres (mm)
     * @var    int          $marginHeader       A header margin size specified as length in millimetres (mm)
     * @var    int          $marginFooter       A footer margin size specified as length in millimetres (mm)
     * @var    string       $metaTitle          A document title (e.g., metadata)
     * @var    string       $metaAuthor         A document author (e.g., metadata)
     * @var    string       $metaSubject        A document subject (e.g., metadata)
     * @var    string       $metaCreator        A document creator (e.g., metadata)
     * @var    string       $outputTypes        A output type (e.g., 'I': inline, 'D': download, 'F': file, 'S': string)
     * @var    array        $metaKeywords       A document list of descriptive keywords (e.g., metadata)
     * @var    array        $storageRegister    A set of validation stored data elements
     * @static PdfInterface $instance           A PdfInterface
     * @static int          $objectCount        A PdfInterface count
     */
    protected $mpdf               = null;
    protected $pageHeader         = null;
    protected $pageFooter         = array();
    protected $characterEncoding  = 'UTF-8';
    protected $fontSize           = 12;
    protected $fontType           = 'Times';
    protected $filename           = 'document.pdf';
    protected $outputDestination  = 'I';
    protected $pageCSS            = null;
    protected $pageContent        = null;
    protected $pageSize           = 'Letter';
    protected $pageOrientation    = 'Portrait';
    protected $pageFormat         = 'Letter';
    protected $marginTop          = 11;
    protected $marginRight        = 15;
    protected $marginBottom       = 14;
    protected $marginLeft         = 11;
    protected $marginHeader       = 5;
    protected $marginFooter       = 9;
    protected $metaTitle          = null;
    protected $metaAuthor         = null;
    protected $metaSubject        = null;
    protected $metaCreator        = null;
    protected $metaKeywords       = array();
    protected $storageRegister    = array();
    protected $pageTypes          = ['Letter', 'Legal', 'A4', 'Tabloid'];
    protected $outputTypes        = ['I', 'D', 'F', 'S'];

    protected $orientationTypes   = ['Portrait', 'Landscape'];
    protected $fontFamily         = [
            'arial' => "Arial, 'Helvetica Neue', Helvetica, sans-serif",
            'times' => "TimesNewRoman, 'Times New Roman', Times, Baskerville, Georgia, serif",
            'tahoma' => "Tahoma, Verdana, Segoe, Geneva, sans-serif",
            'georgia' => "Georgia, Times, 'Times New Roman', serif",
            'trebuchet' => "'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Helvetica, Tahoma, sans-serif",
            'courier' => "'Courier New', Courier, 'Lucida Sans Typewriter', 'Lucida Typewriter', monospace",
            'lucida' => "'Lucida Sans Typewriter', 'Lucida Console', monaco, 'Bitstream Vera Sans Mono', monospace",
            'lucida-bright' => "'Lucida Bright', Georgia, serif",
            'palatino' => "'Palatino Linotype', 'Palatino LT STD', 'Book Antiqua', Palatino, Georgia, serif",
            'garamond' => "Garamond, Baskerville, 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', serif",
            'verdana' => "Verdana, Geneva, sans-serif",
            'console' => "'Lucida Console', 'Lucida Sans Typewriter', Monaco, 'Bitstream Vera Sans Mono', monospace",
            'monaco' => "'Lucida Console', 'Lucida Sans Typewriter', Monaco, 'Bitstream Vera Sans Mono', monospace",
            'helvetica' => "'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif",
            'calibri' => "Calibri, Candara, Segoe, 'Segoe UI', Optima, Arial, sans-serif",
            'avant-garde' => "'Avant Garde', Avantgarde, 'Century Gothic', CenturyGothic, AppleGothic, sans-serif",
            'cambria' => "Cambria, Georgia, serif",
            'default' => "Arial, 'Helvetica Neue', Helvetica, sans-serif"
        ];
    protected static $instance    = null;
    protected static $objectCount = 0;

    // --------------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @api
     */
    public function __construct()
    {
        static::$instance = $this;
        static::$objectCount++;
    }

    // --------------------------------------------------------------------------

    /**
     * Destructor.
     *
     * @api
     */
    public function __destruct()
    {
        static::$objectCount--;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the page header.
     *
     * @param array $data  A list of header items ('left','right')
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setHeader(array $data): PdfInterface
    {
        $string_right = str_replace("{{date(\"n/d/Y g:i A\")}}", Carbon::now()->format('n/d/Y g:i A'), $data['right']);
        $string_left  = str_replace("|", '<br>', $data['left']);

        $html = "<table border='0' cellspacing='0' cellpadding='0' width='100%'><tr>
                     <td style='font-family:arial;font-size:14px;font-weight:bold;'>$string_left</td>
                     <td style='font-size:13px;font-family:arial;text-align:right;font-style:italic;'>$string_right</td>
                 </tr></table><br>";

        $this->setProperty('pageHeader', $html);
        $this->appendPageContent($this->pageHeader);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the page footer.
     *
     * @param array $data  A list of footer items ('left','center','right')
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setFooter(array $data): PdfInterface
    {
        $footer = [
            'odd' => [
                $this->setFooterContent('Left', str_replace("|", '<br>', $data['left'])),
                $this->setFooterContent('Center', str_replace("|", '<br>', $data['center'])),
                $this->setFooterContent('Right', str_replace("{{page(\"# of #\")}}", '{PAGENO} of {nb}', $data['right'])),
                'line' => true,
            ],
            'even' => []
        ];

        $this->setProperty('pageFooter', $footer);
        $this->mpdf->SetFooter($this->pageFooter);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the content for the page footer.
     *
     * @param string $str  A footer content item
     * @param string $column  A footer placement [Right, Center, Left]
     *
     * @return array
     *
     * @api
     */
    public function setFooterContent(string $column, string $str): array
    {
        return [
            mb_substr($column, 0, 1, 'utf-8') => [
                'content'     => "<strong>$str</strong>",
                'font-size'   => 9,
                'font-style'  => '',
                'font-family' => 'Arial',
                'color'       => '#000000'
            ]
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Set the default document font.
     *
     * @param string $fontname  A font name ('Times','Helvetica','Courier')
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setFontType(string $fontname = null): PdfInterface
    {
        /**
         * Font sets to be used for PDF documents:
         *
         *   - Arial           - Times             - Tahoma
         *   - Georgia         - Trebuchet         - Courier
         *   - Lucida          - Lucida-Bright     - Palatino
         *   - Garamond        - Verdana           - Console
         *   - Monaco          - Helvetica         - Calibri
         *   - Avant-Garde     - Cambria
         */
        $this->setProperty('fontType', $this->getFontFamily(strtolower($fontname)));
        $this->mpdf->SetDefaultBodyCSS('font-family', $this->getProperty('fontType'));

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Return a specific font-family.
     *
     * @param string $fontname  A font name type
     *
     * @return string
     *
     * @api
     */
    protected function getFontFamily(string $fontname = null): string
    {
        /**
         * Font sets to be used for PDF documents:
         *
         *   - Arial           - Times             - Tahoma
         *   - Georgia         - Trebuchet         - Courier
         *   - Lucida          - Lucida-Bright     - Palatino
         *   - Garamond        - Verdana           - Console
         *   - Monaco          - Helvetica         - Calibri
         *   - Avant-Garde     - Cambria
         */
        return array_key_exists(strtolower($fontname), $this->fontFamily)
            ? $this->fontFamily[strtolower($fontname)]
            : $this->fontFamily['default'];
    }

    // --------------------------------------------------------------------------

    /**
     * Append the HTML content.
     *
     * @param string $str  A string data used for render
     *
     * @return PdfInterface
     *
     * @api
     */
    public function appendPageContent(string $str): PdfInterface
    {
        $this->setProperty('pageContent', $str);
        $this->mpdf->WriteHTML($this->pageContent);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the top page margin.
     *
     * @param int $marginTop  A top page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginTop(int $marginTop): PdfInterface
    {
        $this->setProperty('marginTop', $marginTop);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the right page margin.
     *
     * @param int $marginRight  A right page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginRight(int $marginRight): PdfInterface
    {
        $this->setProperty('marginRight', $marginRight);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the bottom page margin.
     *
     * @param int $marginBottom  A bottom page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginBottom(int $marginBottom): PdfInterface
    {
        $this->setProperty('marginBottom', $marginBottom);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the left page margin.
     *
     * @param int $marginLeft  A left page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginLeft(int $marginLeft): PdfInterface
    {
        $this->setProperty('marginLeft', $marginLeft);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the header page margin.
     *
     * @param int $marginHeader  A header page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginHeader(int $marginHeader): PdfInterface
    {
        $this->setProperty('marginHeader', $marginHeader);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the footer page margin.
     *
     * @param int $marginFooter  A footer page margin
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMarginFooter(int $marginFooter): PdfInterface
    {
        $this->setProperty('marginFooter', $marginFooter);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the page margins.
     *
     * @param array $setting  A margin configiration setting
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setMargins(array $setting): PdfInterface
    {
        $this->setProperty('marginTop', (int) $setting['marginTop']);
        $this->setProperty('marginRight', (int) $setting['marginRight']);
        $this->setProperty('marginBottom', (int) $setting['marginBottom']);
        $this->setProperty('marginLeft', (int) $setting['marginLeft']);
        $this->setProperty('marginHeader', (int) $setting['marginHeader']);
        $this->setProperty('marginFooter', (int) $setting['marginFooter']);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the page size.
     *
     * @param string $pageSize  A page format/size type ['Letter','Legal', etc.]
     *
     * @return PdfInterface
     *
     * @api
     */
    public function setPageSize(string $pageSize): PdfInterface
    {
        $this->setProperty('pageSize', $pageSize);
        $this->registerPageFormat();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Append a CSS style.
     *
     * @param string $str  A string data used for render
     *
     * @return PdfInterface
     *
     * @api
     */
    public function appendPageCSS(string $str): PdfInterface
    {
        $this->setProperty('pageCSS', $str);
        $this->mpdf->WriteHTML($this->pageCSS, 1);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Generate and store a defined PDF page format.
     *
     * @param string $pageSize     A page format type ['Letter','Legal', etc.]
     * @param string $orientation  A page orientation ['Portrait','Landscape']
     *
     * @return PdfInterface
     */
    protected function registerPageFormat(string $pageSize = null, string $orientation = null): PdfInterface
    {
        in_array($pageSize, $this->pageTypes)
            ? $this->setProperty('pageSize', $pageSize)
            : $this->setProperty('pageSize', static::DEFAULT_PAGE_SIZE);

        $this->setPageOrientation($orientation);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the page orientation.
     *
     * @param string $orientation  A page orientation ['Portrait','Landscape']
     *
     * @return PdfInterface
     */
    public function setPageOrientation(string $orientation): PdfInterface
    {
        $this->setProperty('pageOrientation', strtoupper($orientation[0]));

        $this->pageOrientation === 'L'
            ? $this->setProperty('pageFormat', $this->pageSize . '-' . $this->pageOrientation)
            : $this->setProperty('pageFormat', $this->pageSize);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF Meta Title.
     *
     * @param string $str  The page title
     *
     * @return PdfInterface
     */
    public function setMetaTitle(string $str): PdfInterface
    {
        $this->setProperty('metaTitle', $str);
        $this->mpdf->SetTitle($this->metaTitle);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF Meta Author.
     *
     * @param string $str  The page author
     *
     * @return PdfInterface
     */
    public function setMetaAuthor(string $str): PdfInterface
    {
        $this->setProperty('metaAuthor', $str);
        $this->mpdf->SetAuthor($this->metaAuthor);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF Meta Creator.
     *
     * @param string $str  The page creator
     *
     * @return PdfInterface
     */
    public function setMetaCreator(string $str): PdfInterface
    {
        $this->setProperty('metaCreator', $str);
        $this->mpdf->SetCreator($this->metaCreator);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF Meta Subject.
     *
     * @param string $str  The page subject
     *
     * @return PdfInterface
     */
    public function setMetaSubject(string $str): PdfInterface
    {
        $this->setProperty('metaSubject', $str);
        $this->mpdf->SetSubject($this->metaSubject);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF Meta Key Words.
     *
     * @param array $words  The page key words
     *
     * @return PdfInterface
     */
    public function setMetaKeywords(array $words): PdfInterface
    {
        $this->setProperty('metaKeywords', array_merge($this->metaKeywords, $words));
        $this->mpdf->SetKeywords(implode(', ', $this->metaKeywords));

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF to Letter Size.
     *
     * @return PdfInterface
     */
    public function setPageSizeLetter(): PdfInterface
    {
        $this->setProperty('pageSize', 'Letter');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF to Legal Size.
     *
     * @return PdfInterface
     */
    public function setPageSizeLegal(): PdfInterface
    {
        $this->setProperty('pageSize', 'Legal');

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF to Landscape.
     *
     * @return PdfInterface
     */
    public function setPageAsLandscape(): PdfInterface
    {
        $this->setProperty('pageOrientation', 'Landscape');
        $this->registerPageFormat();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set PDF to Portrait.
     *
     * @return PdfInterface
     */
    public function setPageAsPortrait(): PdfInterface
    {
        $this->setProperty('pageOrientation', 'Portrait');
        $this->registerPageFormat();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Method implementations inserted:
     *
     * Method noted as: (+) @api, (-) protected or private visibility.
     *
     * (+) array all();
     * (+) object init();
     * (+) string version();
     * (+) bool isString($str);
     * (+) bool has(string $key);
     * (+) string getClassName();
     * (+) int getInstanceCount();
     * (+) bool isValidEmail($email);
     * (+) array getClassInterfaces();
     * (+) mixed getConst(string $key);
     * (+) bool isValidUuid(string $uuid);
     * (+) bool isValidSHA512(string $hash);
     * (+) mixed __call($callback, $parameters);
     * (+) bool doesFunctionExist($functionName);
     * (+) bool isStringKey(string $str, array $keys);
     * (+) mixed get(string $key, string $subkey = null);
     * (+) mixed getProperty(string $name, string $key = null);
     * (+) object set(string $key, $value, string $subkey = null);
     * (+) object setProperty(string $name, $value, string $key = null);
     * (-) \Exception throwExceptionError(array $error);
     * (-) \InvalidArgumentException throwInvalidArgumentExceptionError(array $error);
     */
    use ServiceFunctions;

    // --------------------------------------------------------------------------
}
