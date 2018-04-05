((d, b, w) => {

	// $(window).bind("beforeunload", function() {
	//     return confirm("Закрыть?");
	// });

	$('#button-remove-logo').on('click', function(e) {
		e.preventDefault();

		$.ajax({
            url: '/api/remove-logo',
            type: 'post',
            processData: false,
            success: function(response)
		    {
		    	window.location.reload();
		    },
            error: function(response)
		    {
		    	alert('error');
		    }
        });
	});

	$('.j-color-picker').each(function() {
		var $picker = $(this);

		$picker.ColorPicker({
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			},
			onHide: function (colpkr) {
				$(colpkr).hide();
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$picker.val(`#${hex}`);
			}
		});
	});

})(document, document.body, window);
