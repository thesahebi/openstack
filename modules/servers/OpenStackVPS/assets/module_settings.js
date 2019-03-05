jQuery(document).ready(function(){
	
	// checkboxes support
	jQuery('input[type="checkbox"][name^="customconfigoption"]').change(function(){
		var newvalue = jQuery(this).is(':checked') ? 1 : 0;
		var newname = jQuery(this).attr('name').replace('customconfigoption[', 'customconfigoption[hidden_');
		jQuery('input[type="hidden"][name="'+newname+'"]').val(newvalue);
	});
       
});