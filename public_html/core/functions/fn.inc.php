<?php declare(strict_types = 1);

use Intervention\Image\ImageManager;

function escape($value = '')
{
    return $value ? htmlspecialchars($value) : '';
}

function _scan($dir)
{
	return array_diff(scandir($dir), [ '.', '..', '.DS_Store', '.gitkeep', '.gitignore' ]);
}

function _scannode($source, $name, $mode = 'video', $base_name = '01')
{
    $dir = $source.DS.$name;

    if (is_dir($dir)) {
        $path = str_replace(PATH_ROOT, '', $dir);
        $sample = _scan($dir);
        $explored = new \stdClass;

        foreach ($sample as $file)
        {
            $type = '';

            if (is_gif($file)) {
                if ($mode == 'gif') {
                    $explored->video = $path.DS.$file;
                } else {
                    $explored->fallback = $path.DS.$file;
                }
            }

            if (is_video($file)) {
                if ($mode == 'video') {
                    $explored->video = $path.DS.$file;
                } else {
                    $explored->fallback = $path.DS.$file;
                }
            }

            if ($file == 'images' && is_dir($dir.DS.$file))
            {
                $images = _scan($dir.DS.$file);

                if ($base_name) {
                    foreach($images as $image) {
                        $temp = explode('_', $image);

                        if (isset($temp[1]) && $temp[1] == $base_name) {
                            $shift_image = $image;
                        };
                    }
                }

                if (!$base_name || !isset($shift_image)) {
                    $shift_image = array_shift($images);
                }

                if (is_image($shift_image))
                {
                    $explored->poster = $path.DS.$file.DS.$shift_image;
                }
            }
        }

        $explored->preview = makePreview($explored->poster, $name);

        if (isset($explored->video)) {
            $explored->name = $name;
        }

        return $explored;
    }

    return [];
}

function _scandir($source, $mode = 'video')
{
    $result = [];

	if (is_dir($source))
    {
        $files = _scan($source);

        foreach ($files as $name)
        {
            $result[$name] = _scannode($source, $name, $mode);
        }

        return $result;
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

function makePreview($filename, $pathname)
{
    $source = PATH_ROOT.$filename;

    $preview = sprintf('%s/%s.jpg', PATH_CACHE, $pathname);
    $preview_cache = sprintf('%s/%s.cache.jpg', PATH_CACHE, $pathname);

    if (!file_exists($preview)) {
        copy($source, $preview_cache);

        $manager = new ImageManager([
            'driver' => 'Gd'
        ]);

        $img = $manager->make($preview_cache);

        $img->resize(376, 250);

        $img->save($preview);

        unlink($preview_cache);
    }

    return str_replace(PATH_ROOT, '', $preview);
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
