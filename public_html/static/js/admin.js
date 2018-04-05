((d, b, w) => {

	function getfolder(e) {
	    var files = e.target.files;
	    var path = files[0].webkitRelativePath;
	    var Folder = path.split("/");
	    alert(Folder[0]);
	}

	$(window).bind("beforeunload", function() {
	    return confirm("Закрыть?");
	});

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

})(document, document.body, window);
