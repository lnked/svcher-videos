((d, b, w) => {

	var player = videojs('player', {});
	var videos = d.querySelectorAll('.j-load-video');

	var sidebar = d.getElementById('sidebar');
	var content = d.getElementById('content');
	var sendButton = d.getElementById('send-video');

	var virtual = {};
	var current = 0;
	var count = videos.length;

	var clienHeight = w.innerHeight;
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

	function _clear()
	{
		for (var i = 0; i < videos.length; i++)
		{
			videos[i].classList.remove('is-active');
		}
	}

	function _setActive()
	{
		var top = virtual[current].offset - 20;

		$(sidebar).animate({'scrollTop': top}, 'medium');

		videos[current].classList.add('is-active');
	}

	function playItem()
	{
		_clear();

		if (typeof (virtual[current]) !== 'undefined')
		{
			var item = virtual[current];

			_setActive();

			player.src(item.video);
			player.poster(item.poster);
			player.play();
		}

		// myPlayer.src({
		//    "src":"http://mysite.com/video/video.mp4",
		//    "type":"video/mp4", 
		//    "poster":"http://mysite.com/img/poster.jpg"
		// });


		// var player = videojs('my_player_id');

		// // Get/set poster:
		// console.log(player.poster());
		// player.poster('//example.com/poster.jpg');

		// // Get source:
		// console.log(player.currentSrc());

		// // Update source:
		// player.src({src: '//example.com/video.mp4', type: 'video/mp4'});

		// // Multiple sources:
		// player.src([
		// {src: '//example.com/video.m3u8', type: 'application/x-mpegURL'},
		// {src: '//example.com/video.mp4', type: 'video/mp4'}
		// ]);
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

})(document, document.body, window);

// var myplayer;
// var playCount = 0;
// videojs("example_video_1").ready(function(){

// 	  myplayer = this;

// 	  // EXAMPLE: Start playing the video.
// 	  //myplayer.play();
// 	  myplayer.on("play", function(){
// 		playCount++;
//         $("#count").text(playCount)
// 	  });

// });
// $("#test").click(function (){  
//     myplayer.pause();
//     myplayer.play();
// });

function subscribe(url) {
	var xhr = new XMLHttpRequest();

	xhr.onreadystatechange = function() {
		if (this.readyState != 4) return;

		if (this.status == 200) {
			onMessage(this.responseText);
		} else {
			onError(this);
		}

		subscribe(url);
	}

	xhr.open("GET", url, true);
	xhr.send();
}

// Посылка запросов -- обычными XHR POST
function PublishForm(form, url) {

  function sendMessage(message) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    // просто отсылаю сообщение "как есть" без кодировки
    // если бы было много данных, то нужно было бы отослать JSON из объекта с ними
    // или закодировать их как-то иначе
    xhr.send(message);
  }

  form.onsubmit = function() {
    var message = form.message.value;
    if (message) {
      form.message.value = '';
      sendMessage(message);
    }
    return false;
  };
}

// Получение сообщений, COMET
function SubscribePane(elem, url) {

  function showMessage(message) {
    var messageElem = document.createElement('div');
    messageElem.appendChild(document.createTextNode(message));
    elem.appendChild(messageElem);
  }

  function subscribe() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (this.readyState != 4) return;

      if (this.status == 200) {
        if (this.responseText) {
          // сервер может закрыть соединение без ответа при перезагрузке
          showMessage(this.responseText);
        }
        subscribe();
        return;
      }

      if (this.status != 502) {
        // 502 - прокси ждал слишком долго, надо пересоединиться, это не ошибка
        showMessage(this.statusText); // показать ошибку
      }

      setTimeout(subscribe, 1000); // попробовать ещё раз через 1 сек
    }
    xhr.open("GET", url, true);
    xhr.send();
  }

  subscribe();

}

// var options = {};

// var player = videojs('player', options, function onPlayerReady() {
// 	videojs.log('Your player is ready!');

// 	// var video = this;

// 	// // In this context, `this` is the player that was created by Video.js.
// 	// video.play();

// 	// // How about an event listener?
// 	// this.on('ended', function() {
// 	// 	video.play();
// 	// 	playNext();
// 	// 	videojs.log('Awww...over so soon?!');
// 	// });
// });
