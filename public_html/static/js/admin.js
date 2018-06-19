((d, b, w) => {

	// $(window).bind("beforeunload", function() {
	//     return confirm("Закрыть?");
	// });

	$('.datepicker').datepicker({
		language: 'ru'
	});

	$('.input-daterange').datepicker({
		language: 'ru',
		todayHighlight: true
	});

	$buttonRemove = $('#button-remove');

	$('body').on('change', '.j-change-all', function() {
		const checked = $(this).prop('checked');
		$('.j-change-item').prop('checked', checked);
		$buttonRemove.toggleClass('is-active', checked);
	});

	$('body').on('change', '.j-change-item', function() {
		const checked = $(this).prop('checked');
		$buttonRemove.toggleClass('is-active', !!($('.j-change-item:checked').length));
	});

	$buttonRemove.on('click', function(e) {
		e.preventDefault();

		$('.j-change-item:checked').each(function(index, item) {
			const id = $(item).val();

			$.ajax({
	            url: `/api/remove-statistic/${id}`,
	            type: 'POST',
	            success: function(response)
	            {
			    	$(`#statistic-element-${id}`).remove();
			    	$buttonRemove.removeClass('is-active');
			    }
	        });
		});
	});

	$('#button-remove-logo').on('click', function(e) {
		e.preventDefault();

		$.ajax({
            url: '/api/remove-logo',
            type: 'POST',
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
