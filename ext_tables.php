<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE') {

	t3lib_extMgm::addModulePath('xMOD_txsafeeditM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	//t3lib_extMgm::addModule('tools', 'txsafeeditM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');

}


/*
$tempColumns = array (
	'tx_safeedit_orig' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:safeedit/locallang_db.xml:tt_content.tx_safeedit_orig',		
		'config' => array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'tt_content',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_content','tx_safeedit_orig;;;;1-1-1');

$tempColumns = array (
	'tx_safeedit_orig' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:safeedit/locallang_db.xml:pages.tx_safeedit_orig',		
		'config' => array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'pages',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_safeedit_orig;;;;1-1-1');

$tempColumns = array (
	'tx_safeedit_orig' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:safeedit/locallang_db.xml:tt_news.tx_safeedit_orig',		
		'config' => array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'tt_news',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
);


t3lib_div::loadTCA('tt_news');
t3lib_extMgm::addTCAcolumns('tt_news',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_news','tx_safeedit_orig;;;;1-1-1');
*/
?>