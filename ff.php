<?php

// https://packagist.org/packages/php-ffmpeg/php-ffmpeg

$ffmpeg = FFMpeg\FFMpeg::create();
$video = $ffmpeg->open('video.mpg');
$video
    ->filters()
    ->resize(new FFMpeg\Coordinate\Dimension(320, 240))
    ->synchronize();
$video
    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
    ->save('frame.jpg');
$video
    ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
    ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
    ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');


$ffmpeg = FFMpeg\FFMpeg::create(array(
    'ffmpeg.binaries'  => '/opt/local/ffmpeg/bin/ffmpeg',
    'ffprobe.binaries' => '/opt/local/ffmpeg/bin/ffprobe',
    'timeout'          => 3600, // The timeout for the underlying process
    'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
), $logger);


// 
$ffmpeg->open('video.mpeg');


// 

$format = new FFMpeg\Format\Video\X264();
$format->on('progress', function ($video, $format, $percentage) {
    echo "$percentage % transcoded";
});

$format
    ->setKiloBitrate(1000)
    ->setAudioChannels(2)
    ->setAudioKiloBitrate(256);

$video->save($format, 'video.avi');

// 

// Open your video file
$video = $ffmpeg->open( 'video.mp4' );

// Set an audio format
$audio_format = new FFMpeg\Format\Audio\Mp3();

// Extract the audio into a new file as mp3
$video->save($audio_format, 'audio.mp3');

// Set the audio file
$audio = $ffmpeg->open( 'audio.mp3' );

// Create the waveform
$waveform = $audio->waveform();
$waveform->save( 'waveform.png' );