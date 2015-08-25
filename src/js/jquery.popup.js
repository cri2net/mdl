/*
	$(function(){
			$('#TMactive').corner({
			  tl: { radius: 10 },
			  tr: { radius: 10 },
			  bl: { radius: 0 },
			  br: { radius: 0 },
			  antiAlias: true,
			  autoPad: true,
			  validTags: ["li"]});
	});
*/
$(function() { 
    $("a[rel]").overlay({ 
 
        // custom expose settings 
        expose: { 
            color: 'none', 
            opacity: 0.5, 
            closeSpeed: 1000
        },
		closeOnClick: false,
		finish: {top: 'center'}
    }); 
}); 

function fade (objName) {
	  if ( $(objName).css('display') == 'none' ) {
		$(objName).animate({height: 'show'}, 9);
	  } else {
		$(objName).animate({height: 'hide'}, 9);
	  }
	}
