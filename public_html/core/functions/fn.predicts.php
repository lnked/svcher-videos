<?php declare(strict_types = 1);

function is_phone($phone)
{
    return (strlen(preg_replace("/[^0-9]/", '', $phone)) == 11);
}

function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_image($file)
{
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
}

function is_video($file)
{
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	return in_array($extension, ['avi', 'mp4']);
}