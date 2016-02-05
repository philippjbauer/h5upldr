
====================================================
Async File Upload using Extbase and FAL in TYPO3 7.6
====================================================

.. post::
   :tags: TYPO3, Extbase, HTML5, File API




.. highlight:: php
.. default-role:: code


:Project:
      TYPO3 HTML5 File API Asynchronous Upload based on helhum/upload_example

:Author:
      `Philipp Bauer <philipp.j.bauer@gmail.com>`__

:Repository:
      At Github `philippjbauer/h5upldr <https://github.com/philippjbauer/h5upldr>`__

:Credit:
      - `Helmut Hummel <helmut.hummel@typo3.org>`__ - for original code base


**Overview:**

.. contents::
   :local:
   :depth: 3
   :backlinks: none



What does it do?
================

Can be wrapped around a modified version of the original UploadViewHelper and provides asynchronous upload functionality.

How does it work?
=================

Just define the namespace in your template and use the ViewHelper as follows:

.. code-block:: html

   {namespace bo=Bureauoberhoff\H5upldr\ViewHelpers}

   <bo:widget.ajaxupload placeholder="Please select a JPG file" buttonLabel="Select JPG" storageUid="1" storagePath="content" uploadTypes="jpg,png,gif">
       <bo:form.upload property="image">
           <!--
           This is optional and can be edited according to filetype.
           Make sure to edit the CSS file to show the '.ajaxupload-original' container (it's hidden on default).
           <f:if condition="{resource}">             
               <f:image image="{resource}" alt="" width="50" />
           </f:if>
           -->
       </bo:form.upload>
   </bo:widget.ajaxupload>

