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
    console.log("Add Syntax Highlight Button Loaded.");
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        section: 'advanced',
        group: 'format',
        tools: {
            buttonId: {
                label: 'Insert quotation',
                type: 'button',
		icon: 'extensions/SimpleBlogPage/resources/images/btn_quote.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: '<div style="background-color: #ddf5eb; border-style: dotted;">\n',
                        peri: 'insert quotation here',
                        post: '\n</div>'
                    }
                }
            }
        }
    } );
    console.log("Add Quote Button Loaded.");
    console.log("Credits for quote button icon due to Juxn, licensed under GPL v2. Juxn is a freelanced designer from Germany with a degree in visual communication.");
};

/* Check if view is in edit mode and that the required modules are available. Then, customize the toolbar … */
if ( [ 'view', 'edit', 'submit' ].indexOf( mw.config.get( 'wgAction' ) ) !== -1 ) {
	mw.loader.using( 'user.options' ).then( function () {
		// This can be the string "0" if the user disabled the preference ([[phab:T54542#555387]])
		if ( mw.user.options.get( 'usebetatoolbar' ) == 1 ) {
			$.when(
				mw.loader.using( 'ext.wikiEditor' ), $.ready
			).then( addMoreButton );
		}
	} );
}


