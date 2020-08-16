var addMoreButton = function () {

	var t0 = performance.now();


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
    console.log("Credits for quote button icon due to Juxn, licensed under GPL v2. Juxn is a freelanced designer from Germany with a degree in 	visual communication.");
	/* Functions for generating comment background color */
	/* Ideally this stuff would be stored serverside, since having to recalculate the colors each time you edit a page is obviously 		inefficient. Benchmarking with performance.now() shows this entire function takes 16-41ms to run, so, not in dire need of 			improvement. I would consider improving it when it starts getting to near 100ms...

	   Since we want each user to have the same color, all these functions MUST be deterministic i.e no randomness involved. 
	*/
	function hashCode(str) { // simple rolling hash 
	    var hash = 0;
	    var limit = 11485559;
	    var radix = 84401197;
	    var multiplier = radix;
	    //console.log("doing hash");
	    for (var i = 0; i < str.length; i++) {
	       hash = hash + str.charCodeAt(i) * multiplier;
	       hash *= str.charCodeAt(i) % 8736223;
	       hash %= limit;
	       multiplier *= radix;
	       multiplier %= limit;
	       //console.log(hash);
	    }
	    return hash;
	} 

	function getBrightness(c) {
		var c = c.substring(1);      // strip #
		var rgb = parseInt(c, 16);   // convert rrggbb to decimal
		var r = (rgb >> 16) & 0xff;  // extract red
		var g = (rgb >>  8) & 0xff;  // extract green
		var b = (rgb >>  0) & 0xff;  // extract blue
		var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709
		return luma;
	}

	var stringToColour = function(str) {
	    var hash = hashCode(str);
	    //console.log(hash);
	    var colour = '#';
	    for (var i = 0; i < 3; i++) {
		var value = (hash >> (i * 8)) & 0xFF;
		colour += ('00' + value.toString(16)).substr(-2);
	    }
	    //console.log(colour);
	    return colour;
	}

	var brightness_threshold = 230;
	var darknessness_threshold = 90;

	function generateColor(str) {
	    var old = str;
	    var lighter = stringToColour(str);
	    var darker = lighter;
	    var brightness = getBrightness(lighter);
	    //console.log(brightness); 
	    while (brightness < brightness_threshold) { //too dark
	    	str += 'x';
		lighter = stringToColour(str);
		brightness = getBrightness(lighter);
	    }    

	    brightness = getBrightness(darker);
	    str = old;
	    while (brightness < 60 && brightness > darknessness_threshold) { //too bright
	    	str += 'x';
		darker = stringToColour(str);
		brightness = getBrightness(darker);
	    } 

	    return [lighter, darker];
	}

	var brittle_username = document.getElementById("pt-userpage").innerText; /* TODO: Make this less brittle. */ 
	var returnedvalues = generateColor(brittle_username);
	var lightercolor = returnedvalues[0];
	var darkercolor = returnedvalues[1];
	//console.log(lightercolor, darkercolor);
	//console.log(getBrightness(lightercolor), getBrightness(darkercolor));
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        section: 'advanced',
        group: 'format',
        tools: {
            buttonId: {
                label: 'Insert comment',
                type: 'button',
		icon: 'extensions/SimpleBlogPage/resources/images/btn_comment.png',
                action: {
                    type: 'encapsulate',
                    options: {
                        pre: `<div class="messagebox" style="background-color: ` + lightercolor + `;
  border-left: 6px solid ` + darkercolor + `; ">
  <p><strong>` + brittle_username + "'s comments:</strong> ", 
                        peri: 'insert comment here',
                        post: ' </p>\n</div>'
                    }
                }
            }
        }
    } );
    console.log("Add Comment Button Loaded.");
    console.log("Credits for comment button icon due to Juxn, licensed under GPL.");

	$( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
	  'sections': {
	    'snippets': {
	      'type': 'booklet',
	      'label': 'Useful Snippets',
	      'pages': {
		'section-xml': {
		  'label': 'XML Tags',
		  'layout': 'characters',
		  'characters': [
		    '<references/>',
		    {
		      'action': {
		        'type': 'encapsulate',
		        'options': {
		          'pre': '<ref>',
		          'peri': '',
		          'post': '</ref>'
		        }
		      },
		      'label': '<ref></ref>'
		    }
		  ]
		},
		'section-links': {
		  'label': 'Wikilinks',
		  'layout': 'characters',
		  'characters': [
		    {
		      'action': {
		        'type': 'encapsulate',
		        'options': {
		          'pre': '[[Category:',
		          'peri': '',
		          'post': ']]'
		        }
		      },
		      'label': '[[Category:]]'
		    },
		    {
		      'action': {
		        'type': 'encapsulate',
		        'options': {
		          'pre': '[[File:',
		          'peri': '',
		          'post': ']]'
		        }
		      },
		      'label': '[[File:]]'
		    }
		  ]
		}
	      }
	    }
	  }
	} );


var t1 = performance.now();

console.log("Adding all extra buttons to toolbar took " + (t1 - t0) + " milliseconds in total.");

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


