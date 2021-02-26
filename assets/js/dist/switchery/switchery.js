/*!
 * Switchery  jQuery Plugin - Copyright (c) 2016 Foxteam
 * Dual-licensed under the BSD or MIT licenses
 */
;(function()
{
     
    var Switchery = function(elem, params){



		var obj = $(elem);
		 obj.hide();
      var small = obj.hasClass('small');
        var checked = elem.checked;
		var switchery = $('<div class="switchery ' + (small?"switchery-small":"")   +'"><small></small></div>');
		obj.after(switchery);
		if(checked){
			switchery.addClass('checked');
		}
		switchery.click(function(e){
			switchery.toggleClass('checked');
			obj.click && obj.trigger('click',e);
		});
		return switchery;
    }

    $.fn.switchery = function(params)
    {
        var lists  = this,
            retval = this;

        lists.each(function()
        {
            var plugin = $(this).data("switchery");

            if (!plugin) {
                $(this).data("switchery", new Switchery(this, params));
                $(this).data("switchery-id", new Date().getTime());
            } else {
                if (typeof params === 'string' && typeof plugin[params] === 'function') {
                    retval = plugin[params]();
                }
            }
        });

        return retval || lists;
    };

})();
