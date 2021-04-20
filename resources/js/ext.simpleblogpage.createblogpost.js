( function () {
	var CreateBlogPost = {
		/**
		 * Insert a tag (category) from the category cloud into the inputbox below
		 * it on Special:CreateBlogPost
		 *
		 * @param {string} tagname category name
		 * @param {number} tagnumber
		 */
		insertTag: function ( tagname, tagnumber ) {
			$( '#tag-' + tagnumber ).css( 'color', '#CCCCCC' ).text( tagname );
			// Funny...if you move this getElementById call into a variable and use
			// that variable here, this won't work as intended
			document.getElementById( 'pageCtg' ).value +=
				( ( document.getElementById( 'pageCtg' ).value ) ? ', ' : '' ) +
				tagname;
		},

	$( function () {
		// Tag cloud
		$( 'a.tag-cloud-entry' ).each( function () {
			var $that = $( this );
			$that.on( 'click', function () {
				CreateBlogPost.insertTag(
					$that.data( 'blog-slashed-tag' ),
					$that.data( 'blog-tag-number' )
				);
			} );
		} );
	} );
}() );
