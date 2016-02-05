'use strict';

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

/**
 * AJAX Upload Widget
 *
 * TODO: Make messages translatable
 */
$.fn.ajaxUploadWidget = function (callback) {
	var isCompliant = checkForHtml5Apis(),
		uploadsInProgress = 0;

	// Run plugin
	if (isCompliant === true) {
		$(this).each(run);
	} else {
		$(this).each(showOriginalInputs);
	}

	////////////////////

	/**
	 * Run plugin on each element
	 */
	function run () {
		$(this)
			.on('click', '.ajaxupload-open-filemodal', openFileModal)
			.on('change', 'input[type="file"]', submitFile);

		replacePlaceholderWithFilename($(this));
	};

	/**
	 * Check for HTML5 APIs
	 * @return {boolean}
	 */
	function checkForHtml5Apis() {
		if (window.File && window.FileReader && window.FileList && window.Blob) {
			return true;
		} else {
			// TODO: Create Fallback for File API
			console.log('The File APIs are not fully supported by this browser');
			return false;
		}
	};

	/**
	 * Checks the current files size
	 * @param  {integer} maxfilesize
	 * @param  {object} file
	 * @return {boolean}
	 */
	function checkFilesize (maxfilesize, file) {
		if (parseInt(maxfilesize) < parseInt(file.size)) {
			alert('File too big!\n\nThe file "' + file.name + '" (' + Math.round(file.size / (1024*1024)) + 'MB) is too big.\n\nMax file size is: ' + Math.round(parseInt(maxfilesize) / (1024*1024)) + 'MB');
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Checks the current files size
	 * @param  {string} uploadtypes
	 * @param  {object} file
	 * @return {boolean}
	 */
	function checkFiletypes (uploadtypes, file) {
		var allowedtypes = uploadtypes.split(','),
			filetype = file.type.split('/')[1];

		if (allowedtypes.indexOf(filetype) === -1) {
			alert('Wrong file type!\n\nThe file "' + file.name + '" is of the wrong filetype.\n\nAllowed filetypes are: ' + uploadtypes + '');
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Modify original input fields
	 * @param  {object} $self
	 * @return {void}
	 */
	function showOriginalInputs () {
		$(this).find('.ajaxupload-original').show();
		$(this).find('.ajaxupload-virtual').hide();
	}

	/**
	 * Update original fields after successful upload
	 * @param  {object} $self
	 * @return {void}
	 */
	function updateOriginalInputs ($self, response) {
		var $original = $self.find('.ajaxupload-original'),
			$fileInput = $original.find('input[type="file"]'),
			fileInputHtml = $fileInput.prop('outerHTML');
			$hiddenInput = $original.find('input[type="hidden"]');

		// Add response data to parent and fire event
		$self.data('response', response).trigger('ajaxupload-change');

		// If hiddenInput already exists modify value,
		// otherwise create a hiddenInput field
		if ($hiddenInput.length > 0) {
			$hiddenInput.val(response.file.resourcePointerValue);
		} else {
			$original.append('<input type="hidden" name="' + $fileInput.attr('name') + '[submittedFile][resourcePointer]" value="' + response.file.resourcePointerValue + '" data-transient="true">');
		}

		// Reintegrate fileInput
		$fileInput.replaceWith(fileInputHtml);
		$fileInput = $original.find('input[type="file"]');
	}

	/**
	 * Update text input
	 * @param  {object} $self
	 * @param  {string} filename
	 * @return {void}
	 */
	function updateTextInput ($self, filename) {
		$self.find('.ajaxupload-text-input').val(filename);
	}

	/**
	 * Replace placeholder text with filename of already uploaded file
	 * @param  {object} $self
	 * @return {void}
	 */
	function replacePlaceholderWithFilename ($self) {
		var filename = $self.find('.ajaxupload-resourcedata').data('filename');

		if (filename != undefined) {
			updateTextInput($self, filename);
		}
	}

	/**
	 * Open browser file modal
	 * @param  {object} e
	 * @return {void}
	 */
	function openFileModal (e) {
		e.preventDefault();

		$(this).closest('.ajaxupload').find('input[type="file"]').trigger('click');
	}

	/**
	 * Submit selected file
	 * @param  {object} e
	 * @return {void}
	 */
	function submitFile (e) {
		e.preventDefault();

		var $self = $(this).closest('.ajaxupload'),
			action = $self.data('action'),
			files = e.target.files,
			file = null;

		for (var i = 0; i < files.length; i++) {
			file = files[i];

			if (checkFilesize($self.data('maxfilesize'), file) === true && checkFiletypes($self.data('filetypes'), file)) {
				new FileUpload($self, action, file);
			}
		}
	}

	/**
	 * Update progress bar of current file upload
	 * @param  {object} $self
	 * @param  {integer} percentage
	 * @return {void}
	 */
	function progressUpdate ($self, percentage) {
		var $progress = $self.find('.progress'),
			$progressBar = $progress.find('.progress-bar'),
			isHidden = $progress.is(':hidden');

		if (isHidden === true && percentage > 0 && percentage < 100) {
			isHidden = false;
			$progress.fadeIn();
		}

		if (isHidden === false && percentage === 100) {
			isHidden = true;
			window.setTimeout(function(){
				$progress.fadeOut();
			}, 1000);
		}

		$progressBar.attr('aria-valuenow', percentage).css({width: percentage + '%'});
		$progressBar.children('span').html(percentage + '%');
	}

	/**
	 * FileUpload Method
	 * @param {object} $self
	 * @param {string} action
	 * @param {object} file
	 */
	function FileUpload ($self, action, file) {
		var xhr = new XMLHttpRequest(),
			formdata = new FormData(),
			hmac = $self.find('input[type="hidden"]:not([data-transient="true"])').val();

		// Prepare formdata
		formdata.append('file', file);

		if (hmac != undefined) {
			formdata.append('hmac', hmac);
		}

		// Prepare XHR
		xhr.upload.onprogress = xhrUploadProgress;
		xhr.upload.onload = xhrUploadLoad;
		xhr.onreadystatechange = xhrStateChange;
		xhr.onload = xhrLoad;
		xhr.open('POST', action, true);

		// Send data
		xhr.send(formdata);

		////////////////////

		/**
		 * Update progress
		 * @param  {object} e
		 * @return {void}
		 */
		function xhrUploadProgress (e) {
			if (e.lengthComputable) {
				var percentage = parseInt(Math.round((e.loaded / e.total) * 100));

				progressUpdate($self, percentage);
				updateTextInput($self, file.name + ' (' + percentage + '%)');
			}
		}

		/**
		 * Finalize progress on fileupload end
		 * @param  {object} e The event object
		 * @return {void}
		 */
		function xhrUploadLoad (e) {
			progressUpdate($self, 100);
			updateTextInput($self, file.name + ' (processing...)');
		}

		/**
		 * React to state changes
		 * @param  {object} e The event object
		 * @return {void}
		 */
		function xhrStateChange (e) {
			var uploads = $self.closest('form').data('uploads') === undefined ? 0 : $self.closest('form').data('uploads');
			
			if (xhr.readyState === 1) {
				uploads = uploads + 1;
				$self.closest('form').find('input[type="submit"]').prop('disabled', true);
			}

			if (xhr.readyState === 4) {
				uploads = uploads > 0 ? uploads - 1 : 0;
				if (uploads === 0) {
					$self.closest('form').find('input[type="submit"]').prop('disabled', false);
				}
			}
			
			$self.closest('form').data('uploads', uploads);
		}

		/**
		 * Finalize progress on fileupload end
		 * @param  {object} e The event object
		 * @return {void}
		 */
		function xhrLoad (e) {
			var response = JSON.parse(this.response);

			if (response.status === 200) {
				updateTextInput($self, file.name);
				updateOriginalInputs($self, response);
			} else {
				alert('Error ' + response.status + '\n' + response.message);
			}
		}
	}

};

$(function() {
	$('.ajaxupload').ajaxUploadWidget();
});