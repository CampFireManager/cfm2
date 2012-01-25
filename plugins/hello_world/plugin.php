<?php

/**
 * This class is an example of how I believe a plugin will work.
 * It is licensed, as much as something like this can be, under
 * the GNU Affero General Public License, version 3.0 or later.
 *
 * @author Jon Spriggs <jon@sprig.gs>
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3.0 or later
 */
class plugin_hello_world
{
    function hook_render_every_page()
    {
        echo "Hello World!";
    }

    function hook_login()
    {
        echo "Well hello there!";
    }

    function hook_logout()
    {
        echo "Sorry to see you go now!";
    }
}
