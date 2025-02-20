<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilFormFieldParser
{
    private ilCertificateXlstProcess $xlstProcess;

    public function __construct(?ilCertificateXlstProcess $xlstProcess = null)
    {
        if (null === $xlstProcess) {
            $xlstProcess = new ilCertificateXlstProcess();
        }
        $this->xlstProcess = $xlstProcess;
    }

    public function fetchDefaultFormFields(string $content): array
    {
        $pagewidth = "21cm";
        if (preg_match("/page-width\=\"([^\"]+)\"/", $content, $matches)) {
            $pagewidth = $matches[1];
        }
        $pageheight = "29.7cm";
        if (preg_match("/page-height\=\"([^\"]+)\"/", $content, $matches)) {
            $pageheight = $matches[1];
        }

        $pagesize = 'custom';
        if (((strcmp($pageheight, "29.7cm") === 0) || (strcmp($pageheight, "297mm") === 0))
            && ((strcmp($pagewidth, "21cm") === 0) || (strcmp($pagewidth, "210mm") === 0))) {
            $pagesize = "a4";
        } elseif (((strcmp($pagewidth, "29.7cm") === 0) || (strcmp($pagewidth, "297mm") === 0))
            && ((strcmp($pageheight, "21cm") === 0) || (strcmp($pageheight, "210mm") === 0))) {
            $pagesize = "a4landscape";
        } elseif (((strcmp($pageheight, "21cm") === 0) || (strcmp($pageheight, "210mm") === 0))
            && ((strcmp($pagewidth, "14.8cm") === 0) || (strcmp($pagewidth, "148mm") === 0))) {
            $pagesize = "a5";
        } elseif (((strcmp($pagewidth, "21cm") === 0) || (strcmp($pagewidth, "210mm") === 0))
            && ((strcmp($pageheight, "14.8cm") === 0) || (strcmp($pageheight, "148mm") === 0))) {
            $pagesize = "a5landscape";
        } elseif (((strcmp($pageheight, "11in") === 0))
            && ((strcmp($pagewidth, "8.5in") === 0))) {
            $pagesize = "letter";
        } elseif (((strcmp($pagewidth, "11in") === 0))
            && ((strcmp($pageheight, "8.5in") === 0))) {
            $pagesize = "letterlandscape";
        }

        $marginBody_top = ilPageFormats::DEFAULT_MARGIN_BODY_TOP;
        $marginBody_right = ilPageFormats::DEFAULT_MARGIN_BODY_RIGHT;
        $marginBody_bottom = ilPageFormats::DEFAULT_MARGIN_BODY_BOTTOM;
        $marginBody_left = ilPageFormats::DEFAULT_MARGIN_BODY_LEFT;
        if (preg_match("/fo:flow[^>]*margin\=\"([^\"]+)\"/", $content, $matches)) {
            // Backwards compatibility
            $marginbody = $matches[1];
            if (preg_match_all("/([^\s]+)/", $marginbody, $matches)) {
                $marginBody_top = $matches[1][0];
                $marginBody_right = $matches[1][1];
                $marginBody_bottom = $matches[1][2];
                $marginBody_left = $matches[1][3];
            }
        } elseif (preg_match("/fo:region-body[^>]*margin\=\"([^\"]+)\"/", $content, $matches)) {
            $marginbody = $matches[1];
            if (preg_match_all("/([^\s]+)/", $marginbody, $matches)) {
                $marginBody_top = $matches[1][0];
                $marginBody_right = $matches[1][1];
                $marginBody_bottom = $matches[1][2];
                $marginBody_left = $matches[1][3];
            }
        }

        $xsl = file_get_contents("./Services/Certificate/xml/fo2xhtml.xsl");
        if ($content !== '' && (is_string($xsl) && $xsl !== '')) {
            $args = [
                '/_xml' => $content,
                '/_xsl' => $xsl
            ];

            $content = $this->xlstProcess->process($args, []);
        }

        $content = preg_replace("/<\?xml[^>]+?>/", "", $content);
        // dirty hack: the php xslt processing seems not to recognize the following
        // replacements, so we do it in the code as well
        $content = str_replace(["&#xA0;", "&#160;"], "<br />", $content);

        return [
            'pageformat' => $pagesize,
            'pagewidth' => $pagewidth,
            'pageheight' => $pageheight,
            'margin_body_top' => $marginBody_top,
            'margin_body_right' => $marginBody_right,
            'margin_body_bottom' => $marginBody_bottom,
            'margin_body_left' => $marginBody_left,
            'certificate_text' => $content
        ];
    }
}
