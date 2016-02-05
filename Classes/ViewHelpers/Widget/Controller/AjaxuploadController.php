<?php
namespace Bureauoberhoff\H5upldr\ViewHelpers\Widget\Controller;

/***************************************************************
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
 ***************************************************************/

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

use Bureauoberhoff\H5upldr\Property\TypeConverter\UploadedFileReferenceConverter;

/**
* Ajax Controller
*/
class AjaxuploadController extends AbstractWidgetController
{
	/**
	 * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
	 * @inject
	 */
	protected $hashService;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 * @inject
	 */
	protected $resourceFactory;

	/**
	 * FileReference Repository
	 * @var \Bureauoberhoff\H5upldr\Domain\Repository\FileReferenceRepository
	 * @inject
	 */
	protected $fileReferenceRepository;

	/**
	 * Index Action
	 * @return void
	 */
	public function indexAction()
	{
		// Too long variable names are tedious ...
		$conf =& $this->widgetConfiguration;
		
		$this->view->assignMultiple([
			'maxFileSize' => $this->maxFileUploadInBytes(),
			'placeholder' => $conf['placeholder'] !== null ? $conf['placeholder'] : LocalizationUtility::translate('placeholder', 'h5upldr'),
			'buttonLabel' => $conf['buttonLabel'] !== null ? $conf['buttonLabel'] : LocalizationUtility::translate('buttonLabel', 'h5upldr'),
			'uploadTypes' => $conf['uploadTypes'] !== null ? $conf['uploadTypes'] : $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext']
		]);
	}

	/**
	 * Initialize Put Action
	 * @return void
	 */
	public function initializePutAction()
	{
		// See above ...
		$conf =& $this->widgetConfiguration;

		$storageUid  = $conf['storageUid']  !== null ? $conf['storageUid']  : 1;
		$storagePath = $conf['storagePath'] !== null ? $conf['storagePath'] : 'user_upload';
		$uploadTypes = $conf['uploadTypes'] !== null ? $conf['uploadTypes'] : $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'];

		/** @var PropertyMappingConfiguration $ajaxConfiguration */
		$ajaxConfiguration = $this->arguments->getArgument('file')->getPropertyMappingConfiguration();
		$ajaxConfiguration->setTypeConverterOptions(
			'Bureauoberhoff\\H5upldr\\Property\\TypeConverter\\UploadedFileReferenceConverter',
			[
				UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS => $uploadTypes,
				UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => $storageUid . ':' . $this->normalizePath($storagePath)
			]
		);
	}

	/**
	 * Receive new file via XHR request and put it in storage.
	 * Errors from validation are handled in the errorAction.
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $file
	 * @param string $hmac
	 * @return string
	 */
	public function putAction(\TYPO3\CMS\Extbase\Domain\Model\FileReference $file, $hmac = null)
	{
		$fileObject = $file->getOriginalResource()->getOriginalFile();

		if ($hmac !== null) {
			$fileReferenceUid = $this->retrieveFileReferenceUid($hmac);
			$fileReference = $this->updateFileReference($fileReferenceUid, $file->getOriginalResource());
		}

		// Genereate resourcePointerValue from updated fileReference or fallback from file
		if ($fileReference !== null) {
			$resourcePointerValue = $fileReference->getUid();
		}

		if ($resourcePointerValue === null) {
			$resourcePointerValue = 'file:' . $fileObject->getUid();
		}

		// Return success payload
		return $this->returnStatus(200, 'Upload successful', [
			'status' => 200,
			'message' => 'Upload successful',
			'file' => [
				'uid' => $fileObject->getUid(),
				'name' => $fileObject->getName(),
				'identifier' => $fileObject->getIdentifier(),
				'storage' => $fileObject->getStorage()->getUid(),
				'resourcePointerValue' => htmlspecialchars($this->hashService->appendHmac((string)$resourcePointerValue))
			]
		]);
	}

	/**
	 * Return JSON capable error message instead of just a string
	 * @return string
	 */
	public function errorAction()
	{
        $this->clearCacheOnError();
		

		if ($this->arguments->getValidationResults()->hasErrors()) {
			foreach ($this->arguments->getValidationResults()->getFlattenedErrors() as $key => $error) {
				$error = $error[0]->getMessage();
			}
		}

		return $this->returnStatus(500, null, [
			'status' => 500,
			'message' => $error !== '' ? $error : $this->getFlattenedValidationErrorMessage()
		]);
	}

	/**
	 * Retrieve fileReference UID from hmac
	 * @param  string $hmac
	 * @return integer
	 */
	protected function retrieveFileReferenceUid($hmac)
	{
		try {
			return (int) $this->hashService->validateAndStripHmac($hmac);
		} catch (Exception $e) {
			return $this->returnStatus(500, $e->getMessage());
		}
	}

	/**
	 * Create new fileReference for later use
	 * @param  integer $fileReferenceUid
	 * @param  \TYPO3\CMS\Core\Resource\FileReference $fileUid
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	protected function updateFileReference($fileReferenceUid, \TYPO3\CMS\Core\Resource\FileReference $falFileRefence)
	{
		$fileReference = $this->persistenceManager->getObjectByIdentifier($fileReferenceUid, 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference', FALSE);

		// Generate Core FileReference
		$fileReference->setOriginalResource($falFileRefence);

		// Persist
		$this->fileReferenceRepository->update($fileReference);

		return $fileReference;
	}

	/**
	 * Return status for unit test or throwStatus for HTTP requests
	 * @param  integer $status
	 * @param  string $message
	 * @param  array $payload
	 * @return array|void
	 */
	protected function returnStatus($status, $message, $payload = null)
	{
		// If payload is not given
		if ($payload === null) {
			$payload = json_encode([
				'status' => $status,
				'message' => $message
			]);
		}

		// Turn array into json
		if (is_array($payload) === true) {
			$payload = json_encode($payload);
		}

		if ($this->isHttpRequest() === true) {
			$this->throwStatus($status, $message, $payload);
		} else {
			return [
				'status' => $status,
				'message' => $message,
				'payload' => $payload
			];
		}
	}

	/**
	 * Normalize path string
	 * @param  string $path
	 * @return string
	 */
	protected function normalizePath($path)
	{
		// Remove double slashes '//' with single
		$path = str_replace('//', '/', $path);
		// Add beginning slash if not present
		$path = substr($path, 0, 1) !== '/' ? '/' . $path : $path;
		// Add trailing slash if not present
		$path = substr($path, -1) !== '/' ? $path . '/' : $path;

		return $path;
	}

	/**
	 * Convert between bytesizes
	 * @param  string $val
	 * @return string|int
	 */
	protected function returnBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) 
		{
			case 'g':
			$val *= 1024;
			case 'm':
			$val *= 1024;
			case 'k':
			$val *= 1024;
		}
		return $val;
	}

	/**
	 * Return max file upload size
	 * @return integer
	 */
	protected function maxFileUploadInBytes() {
		//select maximum upload size
		$max_upload = $this->returnBytes(ini_get('upload_max_filesize'));
		//select post limit
		$max_post = $this->returnBytes(ini_get('post_max_size'));
		//select memory limit
		$memory_limit = $this->returnBytes(ini_get('memory_limit'));
		// return the smallest of them, this defines the real limit
		return (int) min($max_upload, $max_post, $memory_limit);
	}

	/**
	 * Determines if the request comes from HTTP protocol
	 * @return boolean
	 */
	public function isHttpRequest()
	{
		return in_array($this->request->getMethod(), ['POST', 'GET']);
	}

}
?>