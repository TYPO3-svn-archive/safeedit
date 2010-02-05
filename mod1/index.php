<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Morten Tranberg Hansen <mth@cs.au.dk>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


$LANG->includeLLFile('EXT:safeedit/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
//$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Show Diff' for the 'safeedit' extension.
 *
 * @author	Morten Tranberg Hansen <mth@cs.au.dk>
 * @package	TYPO3
 * @subpackage	tx_safeedit
 */
class  tx_safeedit_module1 {

	/** Valid tables **/
	var $tables;

		// GET vars:
	var $table;			// Record table
	var $uid;			// Record uid

		// Internal, static:
	var $perms_clause;	// Page select clause
	var $access;		// If true, access to element is granted
	var $doc;			// Document Template Object

		// Internal, dynamic:
	var $content;		// Content Accumulation
	var $pageinfo;		//  Set to page record of the parent page of the item set
	var $row;			// The database record row.

	
	/**
	 * Initializes the Module
	 * @return	void
	 */
	public function init()	{
		global $BE_USER,$BACK_PATH,$TCA,$TYPO3_CONF_VARS,$TYPO3_DB,$LANG;

		// Setting valid tables
		$this->tables = array();
		if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['safeedit']['enable_tt_content']) {
			$this->tables[] = 'tt_content';
		}
		if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['safeedit']['enable_pages']) {
			$this->tables[] = 'pages';
		}
		if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['safeedit']['enable_tt_news']) {
			$this->tables[] = 'tt_news';
		}

			// Setting input variables.
		$this->table = t3lib_div::_GET('table');
		$this->uid = intval(t3lib_div::_GET('uid'));

			// Initialize:
		$this->perms_clause = $BE_USER->getPagePermsClause(1);
		$this->access = 0;	// Set to true if there is access to the record / file.

			// Checking if the $table value is really a table and if the user has access to it.
		if (isset($TCA[$this->table]))	{
			t3lib_div::loadTCA($this->table);
			
				// Check permissions and uid value:
			if ($this->uid && $BE_USER->check('tables_select',$this->table))	{

				if ((string)$this->table=='pages')	{
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->uid,$this->perms_clause);
					$this->access = is_array($this->pageinfo) ? 1 : 0;
					$this->row = $this->pageinfo;
				} else {
					$this->row = t3lib_BEfunc::getRecord($this->table,$this->uid);
					if ($this->row)	{
						$this->pageinfo = t3lib_BEfunc::readPageAccess($this->row['pid'],$this->perms_clause);
						$this->access = is_array($this->pageinfo) ? 1 : 0;
					}
				}

				$rows = $TYPO3_DB->exec_SELECTgetRows('*', $this->table, 'tx_safeedit_orig='.$this->uid.' AND cruser_id='.$BE_USER->user['uid'].' AND hidden=0');
				$this->draft = $rows['0'];

				/*$treatData = t3lib_div::makeInstance('t3lib_transferData');
				$treatData->renderRecord($this->table, $this->uid, 0, $this->row);
				$cRow = $treatData->theRecord;*/
			}
		}

			// Initialize document template object:
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;

			// Starting the page by creating page header stuff:
		$title = $LANG->getLL('title');
		$this->content .= $this->doc->startPage($title);
		$this->content .= $this->doc->header($title);
		$this->content .= $this->doc->spacer(5);	
		$this->content .= $this->doc->section('',sprintf($LANG->getLL('help'),'<span class="diff-g">','</span>','<span class="diff-r">','</span>'));
		$this->content .= $this->doc->spacer(5);	

	}


	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	public function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		if (!$this->access)	{
			
			$this->content .= $this->doc->section('',$LANG->getLL('access_denied'));

		} else {

			if (!t3lib_div::inArray($this->tables, $this->table)) {

				$this->content .= $this->doc->section('',$LANG->getLL('invalid_table'));

			} else {

				if (empty($this->draft)) {
					
					$this->content .= $this->doc->section('',$LANG->getLL('no_draft'));
					
				} else {
					
					$returnLinkTag = t3lib_div::_GP('returnUrl') ? '<a href="'.t3lib_div::_GP('returnUrl').'" class="typo3-goBack">' : '<a href="#" onclick="window.close();">';
					
					$this->render();
					
					// If return Url is set, output link to go back:
					if (t3lib_div::_GP('returnUrl'))	{
						$this->content = $this->doc->section('',$returnLinkTag.'<strong>'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.goBack',1).'</strong></a><br /><br />').$this->content;
						
						$this->content .= $this->doc->section('','<br />'.$returnLinkTag.'<strong>'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.goBack',1).'</strong></a>');
					}
				}
			}
		}
	}
	
	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	public function printContent()	{
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}
	
	protected function render() {
		global $TCA, $BE_USER, $LANG;


			// Print header, path etc:
		$code = $this->doc->getHeader($this->table,$this->row,$this->pageinfo['_thePath'],1).'</br>';
		$this->content.= $this->doc->section('',$code);

			// Initialize variables:
		$tableRows = Array();

			// Traverse the list of fields to display for the record:
		$fieldList = array();
		$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
		$typeNum = $tceforms->getRTypeNum($this->table,$this->row);
		if ($TCA[$this->table]['types'][$typeNum])	{
			$itemList = $TCA[$this->table]['types'][$typeNum]['showitem'];
			
			if ($itemList)	{
				$fields = t3lib_div::trimExplode(',',$itemList,1);
				$excludeElements = $tceforms->getExcludeElements($this->table,$this->row,$typeNum);

				foreach($fields as $fieldInfo) {
					$parts = explode(';',$fieldInfo);
					
					$theField = trim($parts[0]);
					if (!in_array($theField,$excludeElements) && $TCA[$this->table]['columns'][$theField])	{
							$fieldList[] = $theField;
					}
				}
			}
		}

		foreach($fieldList as $name)	{
			$name = trim($name);
			if ($TCA[$this->table]['columns'][$name])	{
				if (!$TCA[$this->table]['columns'][$name]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$this->table.':'.$name))	{
					
					if (false && $TCA[$this->table]['columns'][$name]['config']['type']=='text') {
						$data = $this->row[$name];
						$draft = $this->draft[$name];
					} else {
						$data = t3lib_BEfunc::getProcessedValue($this->table,$name,$this->row[$name], 0, 1);
						$draft = t3lib_BEfunc::getProcessedValue($this->table,$name,$this->draft[$name], 0, 1);
					}
					
					if ($draft)	{	// There must be diff-data:
						if (strcmp($draft,$data))	{
							
							// Create diff-result:
							$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');
							$data = $t3lib_diff_Obj->makeDiffDisplay($data, $draft);
																											 /*t3lib_BEfunc::getProcessedValue($this->table,$field,$dLVal['old'][$field],0,1),
																												 t3lib_BEfunc::getProcessedValue($this->table,$field,$dLVal['new'][$field],0,1)*/
							
							/*$item.='<div class="typo3-TCEforms-diffBox">'.
								'<div class="typo3-TCEforms-diffBox-header">'.htmlspecialchars($this->getLL('l_changeInOrig')).':</div>'.
								$diffres.
								'</div>';*/
						}
					}
					
					$tableRows[] = '
						<tr>
							<td class="bgColor5">'.$LANG->sL(t3lib_BEfunc::getItemLabel($this->table,$name),1).'</td>
							<td class="bgColor4">'.$data.'</td>
						</tr>';
					
				}
			}
		}

			// Create table from the information:
		$tableCode = '
					<table border="0" cellpadding="1" cellspacing="1" id="typo3-showitem">
						'.implode('',$tableRows).'
					</table>';
		$this->content.=$this->doc->section('',$tableCode);
		$this->content.=$this->doc->divider(2);

			// Add path and table information in the bottom:
		$code = '';
		$code.= $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],-48).'<br />';
		$code.= $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.table').': '.$LANG->sL($TCA[$this->table]['ctrl']['title']).' ('.$this->table.') - UID: '.$this->uid.'<br />';
		$this->content.= $this->doc->section('', $code);
					
	}
	
}

if (defined('TYPO3_MODE') && isset($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/mod1/index.php']))	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_safeedit_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>