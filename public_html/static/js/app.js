((d, b, w) => {

    Modal.init();

	var hasStarted = false;

	var $player = null;
	var interval = null;
	var timeout = 10000;
	var playTimeout = baseTimeout * 1000;

	var $sidebar = $('#sidebar');
	var $content = $('#content');
	var $sendButton = $('#send-video');

	var virtual = [];
	var current = 0;

	var scrollHeight = Math.max(
		b.scrollHeight, document.documentElement.scrollHeight,
		b.offsetHeight, document.documentElement.offsetHeight,
		b.clientHeight, document.documentElement.clientHeight
	);

	var preloader = $(template('tmpl-preloader'));

	if (mode === 'video') {
		$player = videojs('player-video', {});

		// $player.on('ended', function(){
		// 	playNext();
		// });
	} else {
		$player = $('#player-gif');
	}

	$sendButton.on('click', function(e) {
		e.preventDefault();

		pauseVideo();

        Modal.show('tmpl-send-message', {
        	session: virtual[current].name
        });

        $('#send-phone').mask('+7 (999) 999-99-99');

	    $('#send-email').autoEmail([
			'mail.ru',
			'bk.ru',
			'inbox.ru',
			'list.ru',
			'yandex.ru',
			'ya.ru',
			'gmail.com',
			'google.com',
			'rotmail.ru',
			'rochta.ru',
			'rambler.ru',
			'lenta.ru',
			'autorambler.ru',
			'myrambler.ru',
			'ro.ru',
			'rambler.ua',
			'mailru.com',
			'pisem.net'
		]);
	});

	function startInterval()
	{
		pauseVideo();

		playItem();

		interval = setInterval(function() {
			playNext();
		}, playTimeout);
	}

	function pauseVideo()
	{
		if (mode === 'video') {
			$player.pause();
		}

		clearInterval(interval);
	}

	function clearActive()
	{
		$sidebar.find('.j-play-video').removeClass('is-active');
	}

	function setActive(callback)
	{
		var top = virtual[current].offset - 20;

		$sidebar.animate({'scrollTop': top}, 'medium');

		$sidebar.find('.j-play-video').eq(current).addClass('is-active');

		callback();
	}

	function playItem()
	{
		clearActive();

		if (typeof (virtual[current]) !== 'undefined')
		{
			var item = virtual[current];

			setActive(function() {
				if (mode === 'video') {
					$player.src(item.video);
					$player.poster(item.poster);
					$player.play();
				} else {
					$player.html($(template('tmpl-gif', { src: item.video })));
				}
			});
		}
	}

	function playNext()
	{
		current++;

		if (current >= virtual.length)
		{
			current = 0;
		}

		playItem();
	}

	function startCycle()
	{
		startInterval();
	}

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

            validation(form, errors);
        }

        if (response.hasOwnProperty('message'))
        {
        	var _tpl;

        	if (response.status) {
        		_tpl = 'tmpl-popup-message';
        	} else {
        		_tpl = 'tmpl-popup-error';
        	}

            Modal.show(_tpl, {
            	title: response.title,
            	message: response.message
            });

            setTimeout(function() {
            	Modal.close(d.querySelectorAll('.popup-mesage.is-open'));
            }, 3000);
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
		    	if (response.status) {
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

    $('body').on('click', '.j-play-video', function(e){
    	current = $sidebar.find($(this)).index();
    	startInterval();
    });

    function renderData(videos) {
    	var _count = Object.keys(videos).length;

    	$sidebar.find('.preloader').fadeOut().remove();

    	if (_count) {
    		Object.keys(videos).forEach(function(key) {
    			var data = videos[key];

    			if (!($sidebar.find(`#${key}`).length)) {
	    			var $videoItem = $(template('tmpl-video-item', {
			    		id: key,
			    		mode: mode,
			    		file: {
			    			name: data.name,
			    			video: data.video,
			    			poster: data.poster,
			    			preview: data.preview
			    		}
			    	}));

					$sidebar.append($videoItem);

					virtual.push({
						name: data.name,
						video: data.video,
						poster: data.poster,
						offset: $videoItem.offset().top
					});
    			}
    		});
    	}
    }

    $('body').on('modal.close', function() {
    	startInterval();
    });

    function subscribe() {
    	$sidebar.append(preloader);

    	$.ajax({
            url: '/api/get-data',
            type: 'POST',
            processData: false,
            success: function(response) {
				if (response.status) {
					renderData(response.videos);
				}

				if (!hasStarted) {
					hasStarted = true;
		    		startInterval();
		    	}

				setTimeout(subscribe, timeout);
		    },
            error: function(response) {
		    	setTimeout(subscribe, timeout);
		    }
        });
    }

    subscribe();

})(document, document.body, window);
