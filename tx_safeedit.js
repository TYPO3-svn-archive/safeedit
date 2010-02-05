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
 * This is the frontend client of 'safeedit'.
 *
 * @author Morten Tranberg Hansen <mth at cs dot au dot dk>
 * @date   November 10 2009
 */


Ext.namespace('Ext.ux.TYPO3');  

Ext.ux.TYPO3.tx_safeedit = Ext.extend(Ext.util.Observable, {
		
		edit_expiration: 15000,		

		is_on: false,
		saved_draft: false,
		edit_interval: 5000,
		draft_interval: 5000,
		messages: {},

		constructor: function(config) {
				config = config || {};
				Ext.apply(this, config);

				var confDiv = Ext.get('safeedit-conf');
				if(confDiv==null) {
						return;
				}

				this.aware = top.TYPO3.tx_aware_client;
				this.conf = Ext.util.JSON.decode(confDiv.dom.innerHTML);
				
				if(!this.aware || this.conf.is_new) {
						return;
				}
				this.is_on = true;

				this.updatechannel = 'update#'+this.conf.table+':'+this.conf.uid;
				this.editchannel = 'edit#'+this.conf.table+':'+this.conf.uid; 
				this.draftchannel = 'draft#'+this.conf.table+':'+this.conf.uid+':'+this.conf.username; 
				this.latest_data = this.get_data();

				
        this.editTask = {
            run: function(){
								this.aware.addEvent(this.editchannel, {'username':this.conf.username, 'email':this.conf.email});
								this.update_messages();
						},
            interval: this.edit_interval,
            scope: this
        };

        this.draftTask = {
            run: function(){
								var is_changed = false;
								var data = this.get_data();
								for(var i=0; i<this.conf.fields.size(); i++) {
										if(data[this.conf.fields[i]]!=this.latest_data[this.conf.fields[i]]) {
												is_changed = true;
										}
								}
								
								this.latest_data = data;
								
								if(is_changed) {
										this.messages['draft'] = undefined;
										this.update_messages();
										this.aware.addEvent('draft#safeedit', {'table':this.conf.table, 'uid':this.conf.uid, 'data':Ext.util.JSON.encode(data)});
										
										this.saved_draft = true;

								}
						},
            interval: this.draft_interval,
            scope: this
        };			
				
				if(!this.conf.is_draft && this.conf.has_draft) {
						this.messages['draft'] = TYPO3.getLL('you_have_draft') + ' <a href="'+this.get_draft_link()+'">' + TYPO3.getLL('load') + '</a> ' + TYPO3.getLL('or_edit');
				}  

				if(this.conf.old_draft) {
						this.messages['update'] = TYPO3.getLL('draft_outdated') + ' ' + TYPO3.getLL('update_start') + ' ' + TYPO3.getLL('update_change')+ ' <a href="'+this.get_newversion_link()+'">' + TYPO3.getLL('update_newversion') + '</a> ' + TYPO3.getLL('update_or') + ' <a href="" onclick="javascript:TYPO3.tx_safeedit.launch_diff(); return false;">' + TYPO3.getLL('update_newwindow') + '</a> ' + TYPO3.getLL('update_end');
				}
				
				Ext.ux.TYPO3.tx_safeedit.superclass.constructor.call(this, config);
		},

		load: function() {
				if(this.is_on) {
						this.aware.on(this.updatechannel, this.event_handler, this);
						this.aware.on(this.editchannel, this.event_handler, this);
						this.aware.on(this.draftchannel, this.event_handler, this);
						Ext.TaskMgr.start(this.editTask);
						Ext.TaskMgr.start(this.draftTask);
				}
		},

		unload: function() {
				if(this.is_on) {
						this.aware.un(this.updatechannel, this.event_handler, this);
						this.aware.un(this.editchannel, this.event_handler, this);
						this.aware.un(this.draftchannel, this.event_handler, this);
						Ext.TaskMgr.stop(this.editTask);
						Ext.TaskMgr.stop(this.draftTask);
				}
		},

		launch_diff: function() {
				this.diff_window = window.open('mod.php?M=xMOD_txsafeeditM1&table='+encodeURIComponent(this.conf.table)+'&uid='+encodeURIComponent(this.conf.uid),'Show Diff',"height=400,width=550,status=0,menubar=0,resizable=0,location=0,directories=0,scrollbars=1,toolbar=0");
				if (this.diff_window && this.diff_window.focus)	{
						this.diff_window.focus();
				}
		},

		is_changes_made: function() {
				return this.conf.is_draft || this.saved_draft;
		},

		get_data: function() {
				var data = new Object();
				for(var i=0; i<this.conf.fields.size(); i++) {
						var name = TBE_EDITOR.prependFormFieldNames+'['+this.conf.table+']['+this.conf.uid+']['+this.conf.fields[i]+']';

						if(document[TBE_EDITOR.formname][name]) { 

								if(window.RTEarea && RTEarea[name] && RTEarea[name].editor && RTEarea[name].is_loaded) {

										data[this.conf.fields[i]] = RTEarea[name].editor.getHTML();
										data['_TRANSFORM_'+this.conf.fields[i]] = 'RTE';

								} else {

										data[this.conf.fields[i]] = document[TBE_EDITOR.formname][name].value;
										
										// we need to wait ontil getHTML returns a non empty string to make sure its proper loaded.
										// else we risk saving draft without any edit 
										if(window.RTEarea && RTEarea[name] && RTEarea[name].editor && RTEarea[name].editor.getHTML()!='') {

												RTEarea[name].is_loaded = true;
												data[this.conf.fields[i]] = RTEarea[name].editor.getHTML();
												data['_TRANSFORM_'+this.conf.fields[i]] = 'RTE';

												// avoid saving draft just because editor is loaded, except for the case where
												// the RTE started empty. In that case the RTE only loads after edit.
												if(this.latest_data && document[TBE_EDITOR.formname][name].value!='') {
														this.latest_data[this.conf.fields[i]] = data[this.conf.fields[i]];
												}
										}

								}		

						}
				}
				return data;
		},

		get_url_vars: function () {
				var map = {};
				var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
						map[key] = value;
				});
				return map;
		},

		create_link: function(vars) {
				var tmp = [];
				for(var v in vars) {
						tmp.push(v+'='+vars[v]);
				}
				return window.location.protocol + '//' + window.location.host + '' + window.location.pathname + '?' + tmp.join('&');
		}, 

		get_draft_link: function() {
				var vars = this.get_url_vars();
				vars['view_draft'] = 1;
				delete vars['hide_draft'];
				return this.create_link(vars);
		},
		
		get_newversion_link: function() {
				var vars = this.get_url_vars();
				vars['hide_draft'] = 1;
				delete vars['view_draft'];
				return this.create_link(vars);
		},

		event_handler: function(channel, timestamp, data){
				//var dt = new Date(parseInt(timestamp));
				//var elapsed = dt.getElapsed(new Date(this.conf.loadtime));
				var elapsed = parseInt(timestamp) - this.conf.loadtime;

				if(channel==this.updatechannel) {
						
						if(data.username!=this.conf.username && elapsed>0) {
								if(this.is_changes_made()) {
										this.messages['update'] = TYPO3.getLL('the_beuser') + ' \'' + data.username + '\' ' + TYPO3.getLL('update_start') + ' ' + TYPO3.getLL('update_change') + ' <a href="'+this.get_newversion_link()+'">' + TYPO3.getLL('update_newversion') + '</a> ' + TYPO3.getLL('update_or') + ' <a href="" onclick="javascript:TYPO3.tx_safeedit.launch_diff(); return false;">' + TYPO3.getLL('update_newwindow') + '</a> ' + TYPO3.getLL('update_end');
								} else {
										this.messages['update'] = TYPO3.getLL('the_beuser') + ' \'' + data.username + '\' ' + TYPO3.getLL('update_start') + ' ' + TYPO3.getLL('update_nochange') + ' <a href="'+this.get_newversion_link()+'">' + TYPO3.getLL('update_newversion') + '</a>.'
								}
								this.update_messages();
						}
						
				} else if(channel==this.editchannel) {
		
						if(data.username!=this.conf.username && elapsed>0) {

								if(!this.messages['edit']) {
										this.messages['edit'] = {};
								}
								if(!this.messages['edit'][data.username]) {
										this.messages['edit'][data.username] = {};
								}
								
								this.messages['edit'][data.username]['timestamp'] = timestamp;
								this.messages['edit'][data.username]['text'] = TYPO3.getLL('the_beuser') + ' \'' + data.username + '\' ' + (data.email?'(<a href="mailto:'+data.email+'">'+data.email+'</a>) ':'') + TYPO3.getLL('edit');
								this.update_messages();
						}

				} else if(channel==this.draftchannel && elapsed>0) {
						
						var date = new Date(parseInt(timestamp*1000));
						this.messages['draft_saved'] = TYPO3.getLL('draft_saved') + date.format(TYPO3.getLL('draft_date_format'));

						// update diff window
						if(this.diff_window) {
								this.launch_diff();
						}
						
				}

				/*var min = parseInt(elapsed/(1000*60));
				var sec = parseInt(elapsed/1000)-min*60;
				this.set_user_message('User '+data.username+' updated this record ' + (min>0? min + ' minutes and ':'')  + sec + ' seconds ago');
				*/
		},

		get_warning_message: function(action, message) {
				return '<div class="typo3-message message-warning"><div class="message-body"><div id="safeedit-'+action+'">'+message+'</div></div></div>';
		},

		get_info_message: function(action, message) {
				return '<div class="typo3-message message-information"><div class="message-body"><div id="safeedit-'+action+'">'+message+'</div></div></div>';
		},

		update_messages: function() {
				var result = '';

				if(this.messages['update']) {
						result += this.get_warning_message('update', this.messages['update']);
				}
				
				if(this.messages['draft']) {
						result += this.get_info_message('draft', this.messages['draft']);
				}

				for(var username in this.messages['edit']) {						
						if(this.messages['edit'][username]) {
								var elapsed = (new Date()).getTime()-this.messages['edit'][username]['timestamp']*1000;
								if(elapsed<this.edit_expiration) {
										result += this.get_warning_message('edit', this.messages['edit'][username]['text']);
										this.remove_static_message();
								}
						}
				}

				if(this.messages['draft_saved']) {
						result += this.get_info_message('draft-saved', this.messages['draft_saved']);
				}

				var safeeditMessage = Ext.get('safeedit-message');
				safeeditMessage.update(result);

		},

		remove_static_message: function() {
				if(this.conf.remove_message) {
						var messages = Ext.query(".typo3-message");
						for(var i=0; i<messages.size(); i++) {
								var body = Ext.query('.message-body',messages[i]);
								if(body[0] && body[0].innerHTML==this.conf.remove_message) {
										var el = Ext.get(messages[i]);
										el.setDisplayed(false);
								}
						}
				}
		},

});


Ext.onReady(function() {
    TYPO3.tx_safeedit = new Ext.ux.TYPO3.tx_safeedit();

		TYPO3.tx_safeedit.load();

});

Ext.EventManager.on(window, 'unload', function() {
		TYPO3.tx_safeedit.unload();
});