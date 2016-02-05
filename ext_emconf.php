<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "h5upldr"
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'HTML5 Upload Extension',
	'description' => 'HTML5 File API upload based on Helmut Hummels upload_exampe extension.',
	'category' => 'Frontend Extension',
	'author' => 'Philipp Bauer',
	'author_email' => 'pb@bureauoberhoff.de',
	'author_company' => 'Bureau Oberhoff',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.4-7.6.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>
