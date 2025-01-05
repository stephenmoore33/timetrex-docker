export class UserDateTotalParentViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_date_total_parent_view_container',
			sub_user_date_total_view_controller: null,


		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserDateTotalParentEditView.html';
		this.permission_id = 'punch';
		this.script_name = 'UserDateTotalParentView';
		this.viewId = 'UserDateTotalParent';
		this.table_name_key = 'user_date_total_parent';
		this.context_menu_name = $.i18n._( 'Accumulated Time' );
		this.navigation_label = $.i18n._( 'Accumulated Time' );
		this.api = TTAPI.APIUserDateTotal;

		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
	}

	removeEditView() {
		super.removeEditView();

		if ( this.parent_view_controller && this.parent_view_controller.viewId === 'TimeSheet' ) {
			this.parent_view_controller.onSubViewRemoved();
		}
	}

	getCustomContextMenuModel() {
		return {
			include:
				['cancel'],
			exclude:
				['default']
		};
	}

	openEditView( date_str ) {

		var $this = this;

		if ( $this.edit_only_mode ) {

			TTPromise.add( 'UserDateTotalParent', 'init' );
			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

			var date_stamp = Global.strToDate( date_str, 'YYYY-MM-DD' ).format();

			$this.current_edit_record = {
				date: date_str,
				user_id: LocalCacheData.getAllURLArgs().user_id,
				date_stamp: date_stamp
			};
			TTPromise.wait( 'UserDateTotalParent', 'init', function() {
				$this.initEditView();
			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

		}
	}

	buildSearchFields() {
		super.buildSearchFields();
		this.search_fields = [];
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_user_date_total_parent': {
				'label': $.i18n._( 'Accumulated Time' ),
				'html_template': this.getUserDateTotalParentTabHTML(),
			},
		};
		this.setTabModel( tab_model );

		TTPromise.resolve( 'UserDateTotalParent', 'init' );
	}

	setCurrentEditRecordData() {

		this.edit_view_tab.find( '#tab_user_date_total_parent' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
		this.initSubUserDateTotalView( 'tab_user_date_total_parent' );
		this.setEditViewDataDone();
	}

	initSubUserDateTotalView( tab_id ) {
		var $this = this;

		if ( this.sub_user_date_total_view_controller ) {
			this.sub_user_date_total_view_controller.buildContextMenu( true );
			this.sub_user_date_total_view_controller.setDefaultMenu();
			$this.sub_user_date_total_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_date_total_view_controller.getSubViewFilter = function( filter ) {

				return getFilter( filter, this );
			};

			$this.sub_user_date_total_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/attendance/timesheet/UserDateTotalViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'UserDateTotal' + 'View' );
			UserDateTotalViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_user_date_total_view_controller = subViewController;
			$this.sub_user_date_total_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_user_date_total_view_controller.getSubViewFilter = function( filter ) {

				return getFilter( filter, this );
			};
			$this.sub_user_date_total_view_controller.parent_view_controller = $this;
			$this.sub_user_date_total_view_controller.initData();

		}

		function getFilter( filter, target ) {
			var date = Global.strToDate( target.parent_edit_record.date, 'YYYY-MM-DD' ).format();

			filter.date_stamp = date;
			filter.user_id = target.parent_edit_record.user_id; //Should be selected user_id
			filter.object_type_id = [20, 25, 30, 40, 100, 110];

			return filter;
		}
	}

	getUserDateTotalParentTabHTML() {
		return `
		<div id="tab_user_date_total_parent" class="edit-view-tab-outside-sub-view">
			<div class="edit-view-tab" id="tab_user_date_total_parent_content_div">
				<div class="first-column-sub-view"></div>
			</div>
		</div>`;
	}

}

UserDateTotalParentViewController.loadView = function( container ) {
	Global.loadViewSource( 'UserDateTotalParent', 'UserDateTotalParentView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
		} else {
			Global.contentContainer().html( template( args ) );
		}

	} );
};

UserDateTotalParentViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'UserDateTotalParent', 'SubUserDateTotalParentView.html', function( result ) {
		var args = {};
		var template = _.template( result );
		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_user_date_total_parent_view_controller );
				} );
			}
		}

	} );

};