<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2011 - 2015
 * @license LICENSE (see attached file)
 */

class Func extends Base
{
    public static function getNotice()
    {
        if (isset($_SESSION['notice']))
        {
            $notice = '<div class="alert alert-info">' . $_SESSION['notice'] . '</div>';
            unset($_SESSION['notice']);
        }
        else
            $notice = '';
        return $notice;
    }

    public static function generatePassword($length = 10)
    {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvzBDGHJLMNPQRSTVWXZ23456789';
        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++)
        {
            if ($alt == 1)
            {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            }
            else
            {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    public static function validateRoute($route)
    {
        $route = '/' . substr($route, 0, 1) == '/' ? substr($route, 1) : $route;
        $route = strtr(trim($route), array('//' => '/', '\\' => '/'));
        $route = preg_replace_callback('/[^a-zA-Z0-9\_\-\.\/]/', function ($match)
        {
            return ''; }
        , $route);
        $ro = array();
        $routes = explode('/', $route);
        foreach ($routes as $r)
        {
            if ($r != '.' && $r != '..' && $r != '')
                $ro[] = $r;
        }
        return implode('/', $ro);
    }
    public static function deleteSite($site)
    {
        Base::db()->query("DELETE FROM `site` WHERE `site_id` = {$site['site_id']}");
        self::deleteDir(ROOTPATH . 'iwbx-sites/' . $site['url']);

    }

    public static function redirect($url)
    {
        header('Location: ' . parent::$set['baseurl'] . $url);
        exit();
    }
    public static function displayDate($var)
    {
        $shift = (self::$set['timezone']) * 3600;
        if (date('Y', $var) == date('Y', time()))
        {
            if (date('z', $var + $shift) == date('z', time() + $shift))
                return 'Hari ini, ' . date("H:i", $var + $shift);
            if (date('z', $var + $shift) == date('z', time() + $shift) - 1)
                return 'Kemarin, ' . date("H:i", $var + $shift);
        }

        return date("d/m/Y H:i", $var + $shift);
    }

    public static function deleteDir($directory, $empty = false)
    {
        if (substr($directory, -1) == "/")
        {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory))
        {
            return false;
        }
        elseif (!is_readable($directory))
        {
            return false;
        }
        else
        {
            $directoryHandle = opendir($directory);

            while ($contents = readdir($directoryHandle))
            {
                if ($contents != '.' && $contents != '..')
                {
                    $path = $directory . "/" . $contents;

                    if (is_dir($path))
                    {
                        self::deleteDir($path);
                    }
                    else
                    {
                        unlink($path);
                    }
                }
            }

            closedir($directoryHandle);

            if ($empty == false)
            {
                if (!rmdir($directory))
                {
                    return false;
                }
            }

            return true;
        }
    }

    public static function getExt($file)
    {
        return strtolower(substr(strrchr($file, "."), 1));
    }

    public static function displayPagination($url, $start, $total, $kmess, $query_string = false)
    {
        $page_str = $query_string == false ? 'page/%d' : $query_string;
        $url = substr($url, -1) == '?' ? substr($url, 0, -1) . '/' : $url;

        $url = substr($url, -1) == '/' ? $url : $url . '/';

        $ssid = rand(1111, 9999);
        $neighbors = 5;
        if ($start >= $total)
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        else
            $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        $base_link = '<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
            '">%s</a></li>';
        $out[] = $start == 0 ? '<li class="disabled"><span>&laquo;</span></li>' :
            sprintf('<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
            '">%s</a></li>', $start / $kmess, '&laquo;');
        if ($start > $kmess * $neighbors)
            $out[] = sprintf($base_link, 1, '1');
        if ($start > $kmess * ($neighbors + 1))
        {
            $out[] = '<li class="disable"><span>...</span></li>';
        }
        for ($nCont = $neighbors; $nCont >= 1; $nCont--)
            if ($start >= $kmess * $nCont)
            {
                $tmpStart = $start - $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        $out[] = '<li class="active"><span>' . ($start / $kmess + 1) . '</span></li>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;
        for ($nCont = 1; $nCont <= $neighbors; $nCont++)
            if ($start + $kmess * $nCont <= $tmpMaxPages)
            {
                $tmpStart = $start + $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages)
        {
            $out[] = '<li class="disable"><span>...</span></li>';
        }
        if ($start + $kmess * $neighbors < $tmpMaxPages)
            $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess +
                1);
        if ($start + $kmess < $total)
        {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf('<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
                '">%s</a></li>', $display_page, '&raquo;');
        }
        else
        {
            $out[] = '<li class="disabled"><span>&raquo;</span></li>';
        }

        $html = '<div class="paging"><ul class="pagination pagination-sm">' . implode('',
            $out) . '</ul></div>';

        return $html;
    }

    public static function displayError($error = '', $link = '')
    {
        if (!empty($error))
        {
            $out = '<div class="alert alert-danger"><strong>Kesalahan!</strong>:';
            if (is_array($error))
            {
                $out .= '<ol>';
                foreach ($error as $err)
                {
                    $out .= '<li>' . $err . '</li>';
                }
                $out .= '</ol>';
            }
            else
            {
                $out .= ' ' . $error;
            }
            if (!empty($link))
                $out .= '<br />' . $link;
            $out .= '</div>';
            return $out;
        }
        else
        {
            return false;
        }
    }

    public static function permalink($str)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $plink = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $plink = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $plink);
        $plink = strtolower(trim($plink, '-'));
        $plink = preg_replace("/[\/_| -]+/", '-', $plink);

        return $plink;
    }

    public static function readDir($dir, $recurse = false)
    {
        $files = array();
        $folders = array();
        if (false == ($dh = @opendir($dir)))
            return false;
        while ($el = readdir($dh))
        {
            $path = $dir . '/' . $el;

            if (is_dir($path) && $el != '.' && $el != '..')
            {
                $folders[] = $el;
                if ($recurse)
                {
                    self::read_dir($path);
                }
            }
            elseif (is_file($path))
            {
                $files[] = $el;
            }
        }
        closedir($dh);

        return array_merge_recursive($folders, $files);
    }
}

?>