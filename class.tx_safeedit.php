<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Morten Tranberg Hansen (mth at cs dot au dot dk)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
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
 * This is the backend hooks used for 'safeedit'.
 *
 * @author Morten Tranberg Hansen <mth at cs dot au dot dk>
 * @date   November 10 2009
 */

class tx_safeedit {

	private $tables;
	private $record_called = false;

	private $list_records = array();

	public function __construct() {
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
	}

	/**
	 * Template hook called on all pages generated with the TYPO3
	 * template object. The methods adds safeedit javascript to the pages.
	 */
	public function preStartPageHook(array &$params, template &$ref) {
		global $BE_USER, $LANG, $TYPO3_CONF_VARS;

		// the blank title is for full-screen RTE
		if ($params['title']==='TYPO3 Edit Document' || $params['title']==='') {
			
			$ref->loadJavascriptLib(t3lib_extMgm::extRelPath('safeedit').'tx_safeedit.js');			
			tx_jslang::addLL($ref, 'EXT:safeedit/locallang.xml');
			
		} elseif ($TYPO3_CONF_VARS['EXTCONF']['safeedit']['enable_templavoila'] && $params['title']===$LANG->sL('LLL:EXT:templavoila/mod1/locallang.xml:title')) {
			
			$ref->loadJavascriptLib(t3lib_extMgm::extRelPath('safeedit').'tx_safeedit_templavoila.js');			
			tx_jslang::addLL($ref, 'EXT:safeedit/locallang.xml');
			$ref->getPageRenderer()->loadExtJs();
		}
		
	}

	/**
	 * TCEForms hook called before the generation of each form field.
	 */
	public function getSingleField_preProcess($table, $field, &$row, $altName, $palette, $extra, $pal, $tce_forms) {
		$this->safeedit_record_hook($table, $row, $tce_forms);
	}

	/**
	 * TCEForms hook called before the generation of an entire form.
	 */
	public function getMainFields_preProcess($table, &$row, $tce_forms) {
		$this->safeedit_record_hook($table, $row, $tce_forms);
	}

	/**
	 * TCEMain hook called after each database operation.  The method deletes
	 * a any related drafts to an updated record.
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$reference) {
		global $BE_USER, $TYPO3_DB;

		if (!t3lib_div::inArray($this->tables, $table)) {
			return;
		}

		if ($status === 'update') {
			$TYPO3_DB->exec_DELETEquery($table, 'tx_safeedit_orig='.$id.' AND cruser_id='.$BE_USER->user['uid']);
		}
	
	}

	/**
	 * templavoila hook to create safeedit-message <div> on top of page
	 * and pass username to tx_safeedit_templavoila client.
	 */	
	public function templavoila_top(&$params, &$ref) {
		global $BE_USER, $TYPO3_CONF_VARS;

		if ($TYPO3_CONF_VARS['EXTCONF']['safeedit']['enable_templavoila']) {
			return '<div id="safeedit-message"></div>'. '
<script type="text/javascript"> 
/*<![CDATA[*/
<!-- 
TYPO3.tx_safeedit_templavoila_username = \''.$BE_USER->user['username'].'\';
TYPO3.tx_safeedit_templavoila_loadtime = \''.$GLOBALS['EXEC_TIME'].'\';
// -->
/*]]>*/
</script> 
';
		}

	}


	/**
	 * Safeedit record hook inserts record tx_safeedit_record configuration
	 * on TCEForms.  The hook also replaces original record with draft record if
	 * the 'draft' GET parameter is present.
	 *
	 * @param	string the table
	 * @param	array the record data
	 * @param	t3lib_TCEforms the calling TCEForms object
	 * @return void
	 * @access private
	 */
	private function safeedit_record_hook($table, &$row, $tce_forms) {
		global $BE_USER, $TCA, $TYPO3_DB;

		if ($this->record_called) {
			return;
		}
		$this->record_called = true;

		if (!t3lib_div::inArray($this->tables, $table)) {
			return;
		}

		// Get table fields
		$fields = array();
		foreach($TCA[$table]['columns'] as $k => $v) {
			$fields[] = $k;
		}

		// Create configuration array
		$conf['loadtime'] = $GLOBALS['EXEC_TIME'];
		$conf['username'] = $BE_USER->user['username']; 
		$conf['email'] = $BE_USER->user['email']; 
		$conf['table'] = $table;
		$conf['uid'] = $row['uid'];
		$conf['fields'] = $fields;//t3lib_div::trimExplore($fields//substr($fields,0,strlen($fields)-1);

		//echo "draft: " . t3lib_div::_GET('view_draft');

		// Load draft fields if exists
		if (intval($row['uid'])) {

			// hide draft if requested
			if (t3lib_div::_GET('hide_draft')) {
				$TYPO3_DB->exec_UPDATEquery($table, 'tx_safeedit_orig='.$row['uid'], array('hidden'=>1));
			}

			// get draft
			$rows = $TYPO3_DB->exec_SELECTgetRows('*', $table, 'tx_safeedit_orig='.$row['uid'].' AND cruser_id='.$BE_USER->user['uid'].' AND hidden=0');
			$rec = $rows['0'];
			
			if (!empty($rec)) {
				$conf['has_draft'] = 1;
				if (t3lib_div::_GET('view_draft')) {
					$conf['is_draft'] = 1;

					if ($rec['tstamp']<$row['tstamp']) {
						$conf['old_draft'] = 1;
					}

					// override fields with draft value
					if (!empty($rec)) {
						foreach($fields as $f) {
							$row[$f] = $rec[$f];
						}
					}
				}
			}
			
		} else {
			$conf['is_new'] = 1;
		}

		if ($lockInfo = t3lib_BEfunc::isRecordLocked($table, $row['uid'])) {
			$conf['remove_message'] = $lockInfo['msg'];
		}

		// Pass configurations to javascript client.
		t3lib_div::requireOnce(t3lib_extMgm::extPath('safeedit').'class.tx_safeedit_textmessage.php');
		$confMessage = t3lib_div::makeInstance('tx_safeedit_textmessage',
																					 '<div id="safeedit-conf" style="display:none;">'.json_encode($conf).'</div>');
		t3lib_FlashMessageQueue::addMessage($confMessage);
		
		$safeeditMessage = t3lib_div::makeInstance('tx_safeedit_textmessage',
																							 '<div id="safeedit-message"></div>');
		t3lib_FlashMessageQueue::addMessage($safeeditMessage);

	}


	/**
	 * Helper function to process RTE fields in the $data array.
	 * 
	 * @param	string the table
	 * @param	array the uid
	 * @param	array the data array
	 * @return array processed data array
	 * @access private
	 */
	private function fillData($table, $uid, $data) {
		global $BE_USER;

		$types_fieldConfig = t3lib_BEfunc::getTCAtypes($table,$data);
		$theTypeString = t3lib_BEfunc::getTCAtypeValue($table,$data);
		if (is_array($types_fieldConfig))	{
			foreach ($types_fieldConfig as $vconf) {
				$eFile = t3lib_parsehtml_proc::evalWriteFile($vconf['spec']['static_write'],$data);
					if (isset($data[$vconf['field']]))	{
						if($data['_TRANSFORM_'.$vconf['field']]==='RTE') {
							list($tscPID) = t3lib_BEfunc::getTSCpid($table,$uid,$data['pid']);
							$RTEsetup = $BE_USER->getTSConfig('RTE',t3lib_BEfunc::getPagesTSconfig($tscPID));
							$thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'],$table,$vconf['field'],$theTypeString);
							
							// Set alternative relative path for RTE images/links:
							$RTErelPath = is_array($eFile) ? dirname($eFile['relEditFile']) : '';
							
							// Get RTE object, draw form and set flag:
							$RTEobj = t3lib_BEfunc::RTEgetObj();
							if (is_object($RTEobj))	{
								$data[$vconf['field']] = $RTEobj->transformContent('db',$data[$vconf['field']],$table,$vconf['field'],$data,$vconf['spec'],$thisConfig,$RTErelPath,$data['pid']);
							}		

							unset($data['_TRANSFORM_'.$vconf['field']]);
						}
					}
			}
		}
		
		return $data;
	}

	/**
	 * Aware listener that gets called everytime a new 'draft' events
	 * is created.  This methods creates or updates an existing draft
	 * from the event.
	 * 
	 * @param	array the new draft event
	 * @return void
	 * @access public
	 */
	public function newDraft(array &$event) {
		global $TYPO3_DB, $BE_USER;

		$table = $event['data']['table'];
		$uid = intval($event['data']['uid']);
		$data = (array) json_decode($event['data']['data']);

		$rows =  $TYPO3_DB->exec_SELECTgetRows('*', $table, 'tx_safeedit_orig='.$uid.' AND cruser_id='.$BE_USER->user['uid']);
		$rec = $rows['0'];

		$data['tstamp'] = $GLOBALS['EXEC_TIME'];
		$data['hidden'] = 0;
		unset($data['tx_safeedit_orig']);

		$data = $this->fillData($table, $uid, $data);
		
		if(empty($rec)) {
			
			// create new draft
			$data['pid'] = -1;
			$data['crdate'] = $GLOBALS['EXEC_TIME'];			
			$data['cruser_id'] = $BE_USER->user['uid'];
			$data['tx_safeedit_orig'] = $uid;

			$TYPO3_DB->exec_INSERTquery($table, $data);

		} else {

			// update draft
			$TYPO3_DB->exec_UPDATEquery($table, 'uid='.$rec['uid'], $data);

			// remove other duplicate drafts
			/*for($i=1; $i<count($rows); $i++) {
				$TYPO3_DB->exec_DELETEquery($table, 'uid='.$rows[$i]['uid']);
				}*/
		}

		tx_aware::addEvent('draft#'.$table.':'.$uid.':'.$BE_USER->user['username'], array(), true);

	}

	/**
	 * Aware new channel hook that gets called whenever a new channel is created.
	 * Is used to set the default lifetime of edit events.
	 * 
	 * @param	string the new channel
	 * @return void
	 * @access public
	 */	

	public function newChannel($channel) {
		
		if(strtok($channel,'#')=='edit') {
			tx_aware::setChannelLifetime($channel, 30);
		}

	}

}

if (defined('TYPO3_MODE') && isset($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/class.tx_safeedit.php'])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/class.tx_safeedit.php']);
}

?>