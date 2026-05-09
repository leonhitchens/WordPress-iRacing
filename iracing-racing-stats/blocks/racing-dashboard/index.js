( function ( blocks, element ) {
	var el = element.createElement;

	blocks.registerBlockType( 'iracing-racing-stats/racing-dashboard', {
		edit: function () {
			return el(
				'div',
				{ className: 'iracing-editor-placeholder' },
				el( 'div', { className: 'iracing-editor-placeholder__icon' }, '🏁' ),
				el( 'div', { className: 'iracing-editor-placeholder__label' }, 'iRacing Racing Dashboard' ),
				el(
					'div',
					{ className: 'iracing-editor-placeholder__hint' },
					'Your racing stats will appear here on the front end. Configure your API key in Settings → iRacing Stats.'
				)
			);
		},
		save: function () {
			// Server-side rendered — return null so WordPress handles output via render_callback.
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element );
