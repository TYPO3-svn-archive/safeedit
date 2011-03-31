<?php

########################################################################
# Extension Manager/Repository config file for ext "safeedit".
#
# Auto generated 31-03-2011 12:42
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
	'version' => '0.0.6',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'aware' => '',
			'jslang' => '0.0.4-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:18:{s:9:"ChangeLog";s:4:"66f7";s:10:"README.txt";s:4:"ee2d";s:21:"class.tx_safeedit.php";s:4:"7871";s:33:"class.tx_safeedit_textmessage.php";s:4:"07d3";s:21:"ext_conf_template.txt";s:4:"cf74";s:12:"ext_icon.gif";s:4:"b696";s:17:"ext_localconf.php";s:4:"470d";s:14:"ext_tables.php";s:4:"db33";s:14:"ext_tables.sql";s:4:"dbec";s:13:"locallang.xml";s:4:"cae5";s:16:"locallang_db.xml";s:4:"0e7a";s:14:"tx_safeedit.js";s:4:"30dc";s:26:"tx_safeedit_templavoila.js";s:4:"4e22";s:19:"doc/wizard_form.dat";s:4:"e515";s:20:"doc/wizard_form.html";s:4:"d824";s:13:"mod1/conf.php";s:4:"d546";s:14:"mod1/index.php";s:4:"8065";s:18:"mod1/locallang.xml";s:4:"f56a";}',
	'suggests' => array(
	),
);

?>