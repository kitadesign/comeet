(function(){
	function loaded () {
		new LocationController();
	}
	document.addEventListener( 'DOMContentLoaded', loaded, false );
})();

var LocationController = defineClass({
	initialize: function () {
		this.$locationId = $('select#locationId');
		this.$setupLocalButton = $('#setupLocalButton');
		this.$setupLocalButton.bind( 'click',
			_.bind( this.onButtonClick, this )
		);
		this.$locationId.bind( 'change',
			_.bind( this.onChangeLocationId, this )
		);
		this.$loading = $('#loadingImage');
	},
	onButtonClick: function ( event ) {
		var locationId = this.$locationId.val();
		if ( locationId == '' ) {
			console.log('Error');
			return;
		}
		var signature = getInternalParams('signature');
		this.$loading.show();
		callJsonRpc( 'ajax/set_local.php', {
			auth_type: 'signature',
			signature: signature,
			location_id: locationId
		}, _.bind( function ( isSuccess, data ) {
			this.$loading.hide();
			if ( isSuccess ) {
				redirect( '/' );
			} else {
				console.log('TODO: Error Action');
			}
		}, this ) );
	},
	onChangeLocationId: function ( event ) {
		var locationId = this.$locationId.val();
		if ( locationId != '' ) {
			this.$setupLocalButton.removeClass('backgroundGray');
		} else {
			this.$setupLocalButton.addClass('backgroundGray');
		}
	}
});
