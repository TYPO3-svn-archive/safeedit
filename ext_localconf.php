<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

/** Initialize vars from extension conf */
$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
$initVars = array('enable_tt_content','enable_pages','enable_tt_news','enable_templavoila');
foreach($initVars as $var) {
  $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY][$var] = $_EXTCONF[$var] ? trim($_EXTCONF[$var]) : "";
}

$TYPO3_CONF_VARS['EXTCONF']['aware']['listeners']['draft#safeedit'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit->newDraft';

$TYPO3_CONF_VARS['EXTCONF']['aware']['newchannel'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit->newChannel';

$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit->preStartPageHook';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit';

// templavoila hook
$TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'][] = 'EXT:safeedit/class.tx_safeedit.php:tx_safeedit->templavoila_top';

?>
