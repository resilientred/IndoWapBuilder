<?php

/**
 * @package IndoWapBuilder
 * @version VERSION (see attached file)
 * @author Achunk JealousMan
 * @link http://facebook.com/achunks
 * @copyright 2011 - 2015
 * @license LICENSE (see attached file)
 */

echo '</div><div id="footer"><div class="nav-footer"><a href="' . $baseurl .
    '/"><i class="fa fa-home"></i> Home</a>';
if ($user->id)
{
    echo '<a href="' . $baseurl . '/account"><i class="fa fa-user"></i> ' .
        htmlspecialchars($user->getName()) . '</a><a href="' . $baseurl .
        '/panel"><i class="fa fa-dashboard"></i> Panel</a><a href="' . $baseurl .
        '/site/logout"><i class="fa fa-sign-out"></i> Keluar</a>';
}
else
{
    echo '<a href="' . $baseurl .
        '/panel"><i class="fa fa-sign-in"></i> Masuk</a><a href="' . $baseurl .
        '/site/register"><i class="fa fa-user"></i> Pendaftaran</a>';
}
echo '</div><p>&copy; 2014 ' . htmlspecialchars($set['sitename']) .
    '</p></div></div><script src="' . $set['siteurl'] .
    '/iwbx-assets/js/jquery-2.1.1.min.js"></script><script src="' . $set['siteurl'] .
    '/iwbx-assets/js/bootstrap.min.js"></script></body></html>';

?>