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
 * This is the frontend client of 'safeedit' for 'templavoila'.
 *
 * @author Morten Tranberg Hansen <mth at cs dot au dot dk>
 * @date   Januar 2 2010
 */


Ext.namespace('Ext.ux.TYPO3');  

Ext.ux.TYPO3.tx_safeedit_templavoila = Ext.extend(Ext.util.Observable, {
		
		is_on: false,

		constructor: function(config) {
				config = config || {};
				Ext.apply(this, config);

				this.aware = top.TYPO3.tx_aware_client;
				this.uid = top.fsMod.recentIds["web"];

				if(!this.aware || !this.uid) {
						return;
				}
				this.is_on = true;

				this.updatechannel = 'update#pages:'+this.uid;
				
				Ext.ux.TYPO3.tx_safeedit_templavoila.superclass.constructor.call(this, config);
		},

		load: function() {
				if(this.is_on) {
						this.aware.on(this.updatechannel, this.event_handler, this);
				}
		},

		unload: function() {
				if(this.is_on) {
						this.aware.un(this.updatechannel, this.event_handler, this);
				}
		},

		event_handler: function(channel, timestamp, data){
				var elapsed = parseInt(timestamp) - TYPO3.tx_safeedit_templavoila_loadtime;
				
				if(channel==this.updatechannel && data.username!=TYPO3.tx_safeedit_templavoila_username && elapsed>0) {

						var result = '<div class="typo3-message message-warning"><div class="message-body"><div id="safeedit-update">'+TYPO3.jslang.getLL('the_beuser') + ' \'' + data.username + '\' ' + TYPO3.jslang.getLL('templavoila_update') + ' <a href="" onclick="javascript:window.location.href=window.location.href; return false;">' + TYPO3.jslang.getLL('update_newversion') + '</a>.</div></div></div>';

						var safeeditMessage = Ext.get('safeedit-message');
						safeeditMessage.update(result);

				}

		}


});




Ext.onReady(function() {
    TYPO3.tx_safeedit_templavoila = new Ext.ux.TYPO3.tx_safeedit_templavoila();

		TYPO3.tx_safeedit_templavoila.load();

});

Ext.EventManager.on(window, 'unload', function() {
		TYPO3.tx_safeedit_templavoila.unload();
});