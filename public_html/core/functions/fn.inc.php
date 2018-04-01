<?php declare(strict_types = 1);

function _scan($dir)
{
	return array_diff(scandir($dir), [ '.', '..', '.DS_Store', '.gitkeep', '.gitignore' ]);
}

function _scandir($source, $response = [])
{
	if (is_dir($source))
    {
        $files = _scan($source);

        foreach ($files as $name)
        {
        	$dir = $source.DS.$name;

        	if (is_dir($dir)) {
        		$explored = new \stdClass;
        		$sample = _scan($dir);
        		$path = str_replace(PATH_ROOT, '', $dir);

        		foreach ($sample as $file)
        		{
        			$type = '';

                    if ($file == 'images' && is_dir($dir.DS.$file))
                    {
                        $images = _scan($dir.DS.$file);

                        foreach ($images as $image)
                        {
                            if (is_image($image))
                            {
                                $explored->poster = $path.DS.$file.DS.$image;
                                $explored->preview = $path.DS.$file.DS.$image;
                            }
                        }
                    }

        			if (is_video($file)) {
        				$type = 'video';
        			}

                    if (is_image($file)) {
                        $type = 'poster';
                    }

        			if (is_gif($file)) {
        				$type = 'gif';
        			}

        			if ($type) {
        				$explored->{$type} = $path.DS.$file;

        				if ($type == 'poster')
        				{
        					$explored->preview = $path.DS.$file;
        				}
        			}
        		}

                if (isset($explored->video)) {
                    // exit(__('explored: ', $explored));
                    $response[$name] = $explored;
                }
        	}
        }

        return $response;
    }
}

if (!function_exists('__')) {
    function __()
    {
        $args = func_get_args();
        $nargs = func_num_args();
        $trace = debug_backtrace();
        $caller = array_shift($trace);

        $key = $caller['file'].':'.$caller['line'];

        echo '<pre>', $key, "\n";
        for ($i=0; $i<$nargs; $i++) {
            echo print_r($args[$i], true), "\n";
        }

        echo '</pre>';
    }
}

function elvis($first = '', $second = '')
{
    return $first ?: $second;
}

function redirect($url = '', $referer = '')
{
    header('Referrer-Policy: no-referrer');

    if ($url !== '') {
        header("Location: $url", true, 301);
    } else {
        if ($referer !== '') {
            header("Location: $referer", true, 301);
        } else {
            if ($_SERVER['QUERY_STRING'] !== '') {
                header("Location: ". $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING']);
            } else {
                header("Location: " . $_SERVER['SCRIPT_NAME']);
            }
        }
    }

    exit;
}

if (!function_exists('mb_ucwords'))
{
    function mb_ucwords($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
    }
}
