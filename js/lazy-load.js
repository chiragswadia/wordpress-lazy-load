
jQuery(document).ready(function($) {
    
	function lazyload(){
		$("img.wpl_lazyimg").each(function(){
			wpl_this = $(this);
			if (wpl_this.attr("lazyloadpass")===undefined
					&& wpl_this.attr("file")
					&& (!wpl_this.attr("src")
							|| (wpl_this.attr("src") && wpl_this.attr("file")!=wpl_this.attr("src"))
						)
				) {
				if((wpl_this.offset().top - 100) < $(window).height()+$(document).scrollTop()
						&& (wpl_this.offset().left) < $(window).width()+$(document).scrollLeft()
					) {
					wpl_this.attr("src",wpl_this.attr("file"));
					wpl_this.attr("lazyloadpass", "1");
					wpl_this.animate({opacity:1}, 500);
				}
			}
		});
		$("img").each(function(){
			wpl_this = $(this);
                        if( ! wpl_this.hasClass('wpl_lazyimg')){
                            wpl_this.attr('src',wpl_this.attr('file'));
                        }
		});
	}
        if( ! jQuery('body').hasClass('ie8')) {
            lazyload();
        }

	var wpl_var;
	$(window).scroll(function(){clearTimeout(wpl_var);wpl_var=setTimeout(lazyload,400);});
	$(window).resize(function(){clearTimeout(wpl_var);wpl_var=setTimeout(lazyload,400);});
});