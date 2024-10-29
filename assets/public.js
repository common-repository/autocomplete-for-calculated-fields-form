( function(){
	let $ = fbuilderjQuery || jQuery,
		words_count = 3,
		request_id,
		timeout_id;

	function _error_log( str ) {
		if ( 'console' in window ) {
			console.log( str );
		}
		return null;
	} // End _error_log

	function _get_last_x_words( v ) {
		return (new String( v )).split(/\s+/).splice(-1 * words_count)
	} // End _get_last_x_words

	$( document ).on( 'input', '#fbuilder .cff-text-field :input', function(){
		let me = this;
		setTimeout(function(){
			me.scrollLeft = me.scrollWidth;
		}, 0);
	});

	$( document ).on( 'keyup', '#fbuilder .cff-text-field :input', function(){

		if ( 'cff_autocomplete_settings' in window ) {
			let o = getField( this.name ), v;
			if ( o && 'SmartAutocomplete' in o && o.SmartAutocomplete ) {
				try {
					request_id.abort();
				} catch( err ){}

				try {
					clearTimeout( timeout_id );
				} catch( err ){}

				timeout_id = setTimeout(
					function() {
						v = _get_last_x_words( new String( o.val(true, true) ) ).join( ' ' );
						if ( 3 <= v.length ) {

							request_id = $.ajax(
								cff_autocomplete_settings['url'],
								{
									'method' 	: 'POST',
									'dataType'	: 'JSON',
									'data' 		: {
										'wp_nonce' 	: cff_autocomplete_settings['wp_nonce'],
										'action'   	: 'cff-autocomplete',
										'terms'	  	: v
									},
									'success'	: (function( o ){
										let v = ( new String( o.val(true, true) ) ).split(/\s+/);
										v.splice(-1 * words_count);

										return function( data, status ) {
											let f = o.jQueryRef(),
												t = f.find(':input'),
												d = $( '<datalist id="' + t.attr('name') + '_list"></datalist>' ),
												p;

											f.find('datalist').remove();
											t.attr('list', t.attr('name')+'_list');
											t.after(d);
											if( data.length ) {
												for ( var i in data ) {
													p = $('<option>');
													p.attr('value', v.concat(data[i]).join(' '))
													 .text('...'+data[i]);
													d.append(p);
												}
											}
										};
									})( o )
								}
							);
						}
					},
					500
				);
			}
		}
	});

} )();