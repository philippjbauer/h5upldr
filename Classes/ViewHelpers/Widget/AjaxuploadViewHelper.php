<?php
/**
 *  Copyright notice
 *
 *  (c) 2016 Philipp Bauer <pb@bureauoberhoff.de>
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Bureauoberhoff\H5upldr\ViewHelpers\Widget;

use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * Class AjaxViewHelper
 *
 * The template is based on Bootstrap. You can modifiy it, but take a look at what the Javascript does in order to keep functionality.
 * 
 * Usage:
 * 
 * {namespace bo=Bureauoberhoff\H5upldr\ViewHelpers}
 * 
 * <bo:widget.ajaxupload placeholder="Please select a JPG file" buttonLabel="Select JPG" storageUid="1" storagePath="content" uploadTypes="jpg,png,gif">
 *     <bo:form.upload property="image">
 *         <!--
 *         This is optional and can be edited according to filetype.
 *         Make sure to edit the CSS file to show the '.ajaxupload-original' container (it's hidden on default).
 *         <f:if condition="{resource}">             
 *             <f:image image="{resource}" alt="" width="50" />
 *         </f:if>
 *         -->
 *     </bo:form.upload>
 * </bo:widget.ajaxupload>
 *
 * @category ViewHelpers
 * @package  Bureauoberhoff\H5upldr
 * @author   Philipp Bauer <pb@bureauoberhoff.de>
 * @license  http://www.gnu.org/copyleft/gpl.html. GNU GPL
 * @link     http://www.bureauoberhoff.de/ Bureau Oberhoff
 */
class AjaxuploadViewHelper extends AbstractWidgetViewHelper
{
    /**
     * AJAX Widget
     * 
     * @var bool
     */
    protected $ajaxWidget = true;

    /**
     * AJAX Upload Controller
     * 
     * @var    \Bureauoberhoff\H5upldr\ViewHelpers\Widget\Controller\AjaxuploadController
     * @inject
     */
    protected $controller;

    /**
     * Init sub request
     * 
     * @param string  $placeholder (default: 'Please select a file')
     * @param string  $buttonLabel (default: 'Select')
     * @param integer $storageUid  (default: 1)
     * @param string  $storagePath (default: 'user_upload')
     * @param string  $uploadTypes (default: [SYS][mediafile_ext])
     * 
     * @return string
     */
    public function render($placeholder = null, $buttonLabel = null, $storageUid = null, $storagePath = null, $uploadTypes = null)
    {
        return $this->initiateSubRequest();
    }

}