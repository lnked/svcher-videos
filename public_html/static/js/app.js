((d, b, w) => {

	var player = videojs('player', {});
	var videos = d.querySelectorAll('.j-load-video');

	var sidebar = d.getElementById('sidebar');
	var content = d.getElementById('content');
	var sendButton = d.getElementById('send-video');

	var virtual = {};
	var current = 0;
	var count = videos.length;

	var scrollHeight = Math.max(
		b.scrollHeight, document.documentElement.scrollHeight,
		b.offsetHeight, document.documentElement.offsetHeight,
		b.clientHeight, document.documentElement.clientHeight
	);

	player.on('ended', function(){
		playNext();
	});

	if (count) {
		$(videos).each(function(index, item) {
			var $item = $(this);

			virtual[index] = {
				offset: $item.offset().top,
				video: $item.data('video'),
				poster: $item.data('poster')
			};

			(function(node, i) {
				node.addEventListener('click', function(e) {
					current = i;
					playItem();
				});
			})(item, index)

			if (count - 1 == index) {
				startCycle();
			}
		});
	}

	sendButton.addEventListener('click', function(e) {
		player.pause();
	});

	function clearActive()
	{
		for (var i = 0; i < videos.length; i++)
		{
			videos[i].classList.remove('is-active');
		}
	}

	function setActive(callback)
	{
		var top = virtual[current].offset - 20;

		$(sidebar).animate({'scrollTop': top}, 'medium');

		videos[current].classList.add('is-active');

		callback();
	}

	function playItem()
	{
		clearActive();

		if (typeof (virtual[current]) !== 'undefined')
		{
			var item = virtual[current];

			setActive(function() {
				player.src(item.video);
				player.poster(item.poster);
				player.play();
			});
		}
	}

	function playNext()
	{
		current++;

		if (current >= count)
		{
			current = 0;
		}

		playItem();
	}

	function startCycle()
	{
		current = 0;
		playItem();
	}

    Modal.init();

    function validation(form, errors)
    {
        form.find('.error').removeClass('error');

        var fieldName, field;

        setTimeout(function() {
            if (typeof errors !== 'undefined' && errors !== '')
            {
                for (fieldName in errors)
                {
                    if (form.find('input[name="'+fieldName+'"]').length > 0)
                    {
                        field = form.find('input[name="'+fieldName+'"]');
                    }

                    if (form.find('select[name="'+fieldName+'"]').length > 0)
                    {
                        field = form.find('select[name="'+fieldName+'"]');
                    }

                    if (form.find('textarea[name="'+fieldName+'"]').length > 0)
                    {
                        field = form.find('textarea[name="'+fieldName+'"]');
                    }

                    field.addClass('error');
                }
            }
        }, 16);
    }

    function formHandler(form, response) {
    	console.log(response);

        if (response.status)
        {
        	if (response.hasOwnProperty('redirect_url'))
            {
                window.location.href = response.redirect_url;
            }
        }
        else if (typeof response.errors !== 'undefined')
        {
        	var errors, error_message;

            if (typeof response.errors !== 'undefined')
            {
                errors = response.errors;
            }

            console.log(form, errors);

            validation(form, errors);
        }

        if (response.hasOwnProperty('message'))
        {
            console.log(response.title, response.message);
        }
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'JSON',
        contentType: false
    });

    $('body').on('submit', '.j-ajax-form', function(e) {
    	e.preventDefault();

    	var form    = $(this),
		    action  = form.attr('action'),
		    method  = (form.attr('method') || 'post'),
		    data    = !!window.FormData ? new FormData(form[0]) : form.serialize();

		if (form.data('is-busy')) {
		    return;
		}

		form.data('is-busy', true);
		form.addClass('is-busy');

    	$.ajax({
            url: action,
            type: method,
            data: data,
            processData: method.toLowerCase() == 'get',
            success: function(response)
		    {
		    	if (response.status)
		        {
		        	form.find('.error').removeClass('error');
		    		form.get(0).reset();
		    	}

		    	formHandler(form, response);

		        form.data('is-busy', false);
		        form.removeClass('is-busy');
		    },
            error: function(response)
		    {
		        formHandler(form, response.responseJSON);

		        form.data('is-busy', false);
		        form.removeClass('is-busy');
		    }
        });

    	return false;
    });

})(document, document.body, window);
