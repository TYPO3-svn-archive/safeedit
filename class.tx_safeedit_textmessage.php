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
 * This is a text flash message used to distribute text
 * to the frontend client.
 *
 * @author Morten Tranberg Hansen <mth at cs dot au dot dk>
 * @date   November 10 2009
 */

class tx_safeedit_textmessage extends t3lib_FlashMessage {

	public function __construct($message) {
		$this->setMessage($message);
	}

	/**
	 * Renders the flash message.
	 *
	 * @return	string	The flash message as HTML.
	 */
	public function render() {

		/*$message = '<div class="typo3-message" style="display:none;">'
			. '<div class="message-body">' . $this->message . '</div>'
			. '</div>';

			return $message;*/
		return $this->message;
	}


	/**
	 * Creates a string representation of the flash message. Useful for command
	 * line use.
	 *
	 * @return	string	A string representation of the flash message.
	 */
	public function __toString() {
		return $this->message;
	}

}

if (defined('TYPO3_MODE') && isset($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/class.tx_safeedit_textmessage.php'])) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/safeedit/class.tx_safeedit_textmessage.php']);
}

?>