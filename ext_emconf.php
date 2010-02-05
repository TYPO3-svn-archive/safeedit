<?php

########################################################################
# Extension Manager/Repository config file for ext "safeedit".
#
# Auto generated 03-01-2010 12:18
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Safe Record Editing',
	'description' => 'Makes editing of records safe by auto-saving drafts and informing users of concurrent editing and record updates.',
	'category' => 'be',
	'author' => 'Morten Tranberg Hansen',
	'author_email' => 'mth@cs.au.dk',
	'shy' => '',
	'dependencies' => 'cms,aware,jslang',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.5',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'aware' => '',
			'jslang' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"d100";s:10:"README.txt";s:4:"ee2d";s:21:"class.tx_safeedit.php";s:4:"461e";s:33:"class.tx_safeedit_textmessage.php";s:4:"e6d0";s:21:"ext_conf_template.txt";s:4:"82e2";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"bd72";s:14:"ext_tables.php";s:4:"b81a";s:14:"ext_tables.sql";s:4:"dbec";s:13:"locallang.xml";s:4:"418d";s:16:"locallang_db.xml";s:4:"0e7a";s:14:"tx_safeedit.js";s:4:"0538";s:26:"tx_safeedit_templavoila.js";s:4:"0065";s:19:"doc/wizard_form.dat";s:4:"e515";s:20:"doc/wizard_form.html";s:4:"d824";}',
	'suggests' => array(
	),
);

?>