var addMoreButton = function () {
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        section: 'advanced',
        group: 'format',
        tools: {
            buttonId: {
                label: 'Insert Add More tag',
                type: 'button',
		icon: 'extensions/SimpleBlogPage/resources/images/addmorebutton3.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: '<!--',
                        peri: 'more',
                        post: '-->'
                    }
                }
            }
        }
    } );
    console.log("Add More Button Loaded.");
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        section: 'advanced',
        group: 'format',
        tools: {
            buttonId: {
                label: 'Insert syntax highlight tags',
                type: 'button',
		icon: 'extensions/SimpleBlogPage/resources/images/btn_src.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: '<syntaxhighlight lang="python" line="line">\n',
                        peri: 'insert code here',
                        post: '\n</syntaxhighlight>'
                    }
                }
            }
        }
    } );
    console.log("Syntax Highlight Button Loaded.");
};

/* Check if view is in edit mode and that the required modules are available. Then, customize the toolbar … */
if ( [ 'edit', 'submit' ].indexOf( mw.config.get( 'wgAction' ) ) !== -1 ) {
	mw.loader.using( 'user.options' ).then( function () {
		// This can be the string "0" if the user disabled the preference ([[phab:T54542#555387]])
		if ( mw.user.options.get( 'usebetatoolbar' ) == 1 ) {
			$.when(
				mw.loader.using( 'ext.wikiEditor' ), $.ready
			).then( addMoreButton );
		}
	} );
}


