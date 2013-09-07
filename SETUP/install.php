#! /usr/bin/php
<?php
require_once dirname(__FILE__) . '/../classes/autoloader.php';
$objRequest = Container_Request::getRequest();
if ($objRequest->get_strRequestMethod() != 'file') {
    do_die("Must only be run from the command line.", 100);
}
echo "Welcome to CampFireManager2 Installation!\r\n\r\n";

echo "(1/10) Parsing config options\r\n";

$run_init = 0;
$config_file = dirname(__FILE__) . '/../config/local.php';

if ( ! file_exists($config_file)) {
    $fh = fopen($config_file, 'w') or do_die("\n/config/local.php is not creatable. Please make sure you have permission to create and edit this file.\nYou may need root to run this script with root privileges\n", 99);
    fwrite($fh, '');
    fclose($fh);
}

if ( ! is_writable($config_file)) {
    do_die("\n/config/local.php is not writable. Please make sure you have permission to create and edit this file.\nYou may need to run this script with root privileges\n", 98);
}

$arrConfig = array(
    'roottype' => 'mysql',
    'roothost' => 'localhost',
    'rootuser' => 'root',
    'rootpass' => null,
    'rootport' => '3306',
    'coretype' => '',
    'corehost' => '',
    'coreuser' => '',
    'corepass' => '',
    'coreport' => '',
    'coredatabase' => 'cfm2',
    'gammuenable' => '1',
    'gammufile' => '/usr/share/doc/gammu/examples/sql/mysql.sql.gz',
    'gammutype' => '',
    'gammuhost' => '',
    'gammuuser' => '',
    'gammupass' => '',
    'gammuport' => '',
    'gammudatabase' => 'gammu',
    'gammudevice' => '/dev/ttyUSB0',
    'gammuservice' => 'SMSD',
    'twitterenable' => '1',
    'twitterconsumerkey' => '',
    'twitterconsumersecret' => '',
    'twitterusertoken' => '',
    'twitterusersecret' => '',
    'webhost' => 'localhost',
    'forceyes' => 0,
    'loaddemo' => 0,
    'help' => 0
);

$arrOptions = array(
    'roottype' => array(
        '--[Tt][Yy][Pp][Ee]',
        '-[Tt]'
    ),
    'roothost' => array(
        '--[Hh][Oo][Ss][Tt][Nn][Aa][Mm][Ee]',
        '--[Hh][Oo][Ss][Tt]',
        '-[Hh]'
    ),
    'rootport' => array(
        '--[Pp][Oo][Rr][Tt]',
        '-[Pp]'
    ),
    'rootuser' => array(
        '--[Uu][Ss][Ee][Rr][Nn][Aa][Mm][Ee]',
        '--[Uu][Ss][Ee][Rr]',
        '-[Uu]'
    ),
    'rootpass' => array(
        '--[Pp][Aa][Ss][Ss][Ww][Oo][Rr][Dd]',
        '--[Pp][Aa][Ss][Ss]',
        '-[Pp][Ww]'
    ),
    'coreuser' => array(
        '--[Cc][Oo][Rr][Ee][Uu][Ss][Ee][Rr][Nn][Aa][Mm][Ee]',
        '--[Cc][Oo][Rr][Ee][Uu][Ss][Ee][Rr]',
        '-[Cc][Uu]'
    ),
    'corepass' => array(
        '--[Cc][Oo][Rr][Ee][Pp][Aa][Ss][Ss][Ww][Oo][Rr][Dd]',
        '--[Cc][Oo][Rr][Ee][Pp][Aa][Ss][Ss]',
        '-[Cc][Pp][Ww]'
    ),
    'coredatabase' => array(
        '--[Cc][Oo][Rr][Ee][Dd][Aa][Tt][Aa][Bb][Aa][Ss][Ee]',
        '--[Cc][Oo][Rr][Ee][Bb][Aa][Ss][Ee]',
        '-[Cc][Dd]'
    ),
    'gammutype' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Tt][Yy][Pp][Ee]',
        '-[Gg][Tt]'
    ),
    'gammuhost' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Hh][Oo][Ss][Tt][Nn][Aa][Mm][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu][Hh][Oo][Ss][Tt]',
        '-[Gg][Hh]'
    ),
    'gammuport' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Pp][Oo][Rr][Tt]',
        '-[Gg][Pp]'
    ),
    'gammuuser' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Uu][Ss][Ee][Rr][Nn][Aa][Mm][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu][Uu][Ss][Ee][Rr]',
        '-[Gg][Uu]'
    ),
    'gammupass' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Pp][Aa][Ss][Ss][Ww][Oo][Rr][Dd]',
        '--[Gg][Aa][Mm][Mm][Uu][Pp][Aa][Ss][Ss]',
        '-[Gg][Pp][Ww]'
    ),
    'gammudatabase' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Dd][Aa][Tt][Aa][Bb][Aa][Ss][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu][Bb][Aa][Ss][Ee]',
        '-[Gg][Dd]'
    ),
    'gammufile' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Ff][Ii][Ll][Ee]',
        '-[Gg][Ff]'
    ),
    'gammudevice' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Dd][Ee][Vv][Ii][Cc][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu][Dd][Ee][Vv]',
        '-[Gg][Dd][Ee]'
    ),
    'gammuservice' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Ss][Ee][Rr][Vv][Ii][Cc][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu][Ss][Vv][Cc]',
        '-[Gg][Ss]'
    ),
    'gammuenable' => array(
        '--[Gg][Aa][Mm][Mm][Uu][Ee][Nn][Aa][Bb][Ll][Ee]',
        '--[Gg][Aa][Mm][Mm][Uu]',
        '-[Gg][Ee]'
    ),
    'twitterconsumerkey' => array(
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Cc][Oo][Nn][Ss][Uu][Mm][Ee][Rr][Kk][Ee][Yy]',
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Cc][Kk]',
        '-[Tt][Cc][Kk]'
    ),
    'twitterconsumersecret' => array(
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Cc][Oo][Nn][Ss][Uu][Mm][Ee][Rr][Ss][Ee][Cc][Rr][Ee][Tt]',
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Cc][Ss]',
        '-[Tt][Cc][Ss]'
    ),
    'twitterusertoken' => array(
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Uu][Ss][Ee][Rr][Tt][Oo][Kk][Ee][Nn]',
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Uu][Tt]',
        '-[Tt][Uu][Tt]'
    ),
    'twitterusersecret' => array(
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Uu][Ss][Ee][Rr][Ss][Ee][Cc][Rr][Ee][Tt]',
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Uu][Ss]',
        '-[Tt][Uu][Ss]'
    ),
    'twitterenable' => array(
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr][Ee][Nn][Aa][Bb][Ll][Ee]',
        '--[Tt][Ww][Ii][Tt][Tt][Ee][Rr]',
        '-[Tt][Ee]'
    ),
    'webhost' => array(
        '--[Ww][Ee][Bb][Hh][Oo][Ss][Tt][Nn][Aa][Mm][Ee]',
        '--[Ww][Ee][Bb][Hh][Oo][Ss][Tt]',
        '--[Ww][Ee][Bb]',
        '-[Ww]',
    ),
    'forceyes' => array(
        '--[Ff][Oo][Rr][Cc][Ee][Yy][Ee][Ss]',
        '--[Yy][Ee][Ss]',
        '-[Yy]'
    ),
    'loaddemo' => array(
        '--[Ll][Oo][Aa][Dd][Dd][Ee][Mm][Oo]',
        '--[Ll][Oo][Aa][Dd]',
        '-[Ll]'
    ),
    'help' => array(
        '--[Hh][Ee][Ll][Pp]',
        '\/\?'
    )
);

foreach ($objRequest->get_arrRqstParameters() as $key => $parameter) {
    $matchfound = false;
    foreach ($arrOptions as $strKey => $arrOption) {
        foreach ($arrOption as $strOption) {
            switch(1) {
            case preg_match('/^' . $strOption . '=(.*)$/', $parameter, $match):
            case preg_match('/^' . $strOption . '=(.*)$/', $key, $match):
                $oldkey = $arrConfig[$strKey];
                if (strlen($match[1]) == 0) {
                    $match[1] = null;
                }
		$arrConfig[$strKey] = $match[1];
		$matchfound = true;
                break 3;
            case preg_match('/^' . $strOption . '$/', $key, $match):
                $oldkey = $arrConfig[$strKey];
		$arrConfig[$strKey] = $parameter;
		$matchfound = true;
                break 3;
            case preg_match('/^' . $strOption . '$/', $parameter, $match):
                $oldkey = $arrConfig[$strKey];
		$arrConfig[$strKey] = readline("\r\nPlease supply the configuration value for $strKey: ");                    
		$matchfound = true;
		break 3;
	    }
        }	
    }
    if ($matchfound) {
        echo "\r\n * $strKey: Done";
    } else {
        help();
        do_die("Option $key | $parameter not found", 96);	
    }	
}

foreach (array('type', 'host', 'user', 'pass', 'port') as $part) {
    if ($arrConfig['core' . $part] == '') {
        $arrConfig['core' . $part] = $arrConfig['root' . $part];
    }
    if ($arrConfig['gammuenable'] == 1 && $arrConfig['gammu' . $part] == '') {
        $arrConfig['gammu' . $part] = $arrConfig['root' . $part];
    }
}

foreach (array('rootuser', 'coreuser', 'coredatabase') as $part) {
    while ($arrConfig[$part] == '') {
        $arrConfig[$part] = readline("\r\nPlease supply a non-blank value for $part: ");
    }
}

foreach (array('rootpass', 'corepass', 'gammupass') as $part) {
    if ($arrConfig[$part] == '') {
        $arrConfig[$part] = null;
    }
}

if ($arrConfig['gammuenable'] == 1) {
    foreach (array('gammuuser', 'gammudatabase') as $part) {
        while ($arrConfig[$part] == '') {
            $arrConfig[$part] = readline("\r\nPlease supply a non-blank value for $part: ");
        }
    }
}

if ($arrConfig['coretype'] != 'mysql' || ($arrConfig['gammutype'] != 'mysql' && $arrConfig['gammuenable'] == 1)) {
    do_die("\r\nSorry, right now, we only support mysql based databases.\r\n",95);
}

echo "\r\n\r\nDone\r\n\r\n";
if ($arrConfig['help'] === "") {
    help();
    die();
}

echo "(2/10) Accessing and configuring core database: ";

if ($arrConfig['rootpass'] == null || $arrConfig['rootpass'] == '') {
    $rootdb = mysql_connect($arrConfig['roothost'] . ':' . $arrConfig['rootport'], $arrConfig['rootuser'], '');
} else {
    $rootdb = mysql_connect($arrConfig['roothost'] . ':' . $arrConfig['rootport'], $arrConfig['rootuser'], $arrConfig['rootpass']);
}
if ($arrConfig['corepass'] == null || $arrConfig['corepass'] == '') {
    $coredb = @mysql_connect($arrConfig['corehost'] . ':' . $arrConfig['coreport'], $arrConfig['coreuser'], '');
} else {
    $coredb = @mysql_connect($arrConfig['corehost'] . ':' . $arrConfig['coreport'], $arrConfig['coreuser'], $arrConfig['corepass']);
}

if (! $coredb && $rootdb != false) {
    switch(substr(force_readline("\r\nThe non-root core user account does not exist, or the password is not correct.\r\nWould you like me to set this up for you? (Y/N):"), 0, 1)) {
    case 'Y':
    case 'y':
        if ($arrConfig['corepass'] == '') {
            switch(force_readline("\r\nThe non-root core user account has a blank password. Would you like me to set that to a random string? (Y/N): ")) {
            case 'Y':
            case 'y':
                $chars = str_split(sha1(rand()),2);
                foreach ($chars as $k => $char) {
                    $c = chr((hexdec($char)/255*95)+31);
                    if ($c != "'") {
                        $chars[$k] = $c;
                    }
                }
                $arrConfig['corepass'] = implode($chars);
            }
        }
        if (! mysql_query("CREATE USER '{$arrConfig['coreuser']}'@'%' IDENTIFIED BY '{$arrConfig['corepass']}';", $rootdb)) {
            do_die("\r\nCouldn't create the core database user - are you sure you've provided the root credentials?\r\n");
        } else {
            mysql_query("GRANT USAGE ON *.* TO '{$arrConfig['coreuser']}'@'%' IDENTIFIED BY '{$arrConfig['corepass']}' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");
            mysql_query("FLUSH PRIVILEGES;", $rootdb);
            if ($arrConfig['corepass'] == null || $arrConfig['corepass'] == '') {
                $coredb = @mysql_connect($arrConfig['corehost'] . ':' . $arrConfig['coreport'], $arrConfig['coreuser'], '');
            } else {
                $coredb = @mysql_connect($arrConfig['corehost'] . ':' . $arrConfig['coreport'], $arrConfig['coreuser'], $arrConfig['corepass']);
            }
            if (! $coredb) {
                do_die("\r\nThere was an error creating the core database user.");
            }
        }
        break;
    case 'N':
    case 'n':
        help();
        do_die("\r\nNot prepared to configure CFM2 with an invalid username or password\r\n");
    }
} elseif (! $coredb) {
    do_die("\r\nCouldn't proceed - the non-core user account did not exist, and the root credentials were not supplied.\r\n");
}

echo "Done\r\n";

echo "(3/10) Accessing and configuring gammu database: ";

if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
    if ($arrConfig['gammupass'] == null || $arrConfig['gammupass'] == '') {
        $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], '');
    } else {
        $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
    }

    while ($arrConfig['gammuenable'] == '1' && ! $gammudb) {
        if ($coredb != false) {
            switch(substr(force_readline("\r\nThe gammu database user account does not exist, or the password is not correct. Would you like me to set this up for you? (Y/N): "), 0, 1)) {
            case 'Y':
            case 'y':
                if ($arrConfig['gammupass'] == '') {
                    switch(force_readline("\r\nThe gammu database user account has a blank password. Would you like me to set that to a random string? (Y/N): ")) {
                    case 'Y':
                    case 'y':
                        $chars = str_split(sha1(rand()),2);
                        foreach ($chars as $k => $char) {
                            $c = chr((hexdec($char)/255*95)+31);
                            if ($c != "'") {
                                $chars[$k] = $c;
                            }
                        }
                        $arrConfig['gammupass'] = implode($chars);
                    }
                }
                $sql = "CREATE USER '{$arrConfig['gammuuser']}'@'%' IDENTIFIED BY '{$arrConfig['gammupass']}';";
                $qry = mysql_query($sql, $rootdb);
                if (mysql_errno($rootdb) > 0) {
                    do_die("\r\nCouldn't create the gammu database user - are you sure you've provided the root credentials?\r\n");
                }
                if ($arrConfig['gammupass'] == null || $arrConfig['gammupass'] == '') {
                    $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], '');
                } else {
                    $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
                }
                break;
            case 'N':
            case 'n':
                echo "\r\nDisabling the Gammu account - invalid credentials";
                $arrConfig['gammuenable'] = 0;
                break;
            }
        } else {
            do_die("\r\nCouldn't proceed - the gammu database user account did not exist, and the root credentials were not supplied.\r\n");
        }
    }
} elseif ($arrConfig['gammuenable'] == '1') {
    $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
    while ($arrConfig['gammuenable'] == '1' && ! $gammudb) {
        switch(substr(force_readline_reverse("The gammu database credentials do not work, but are not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - invalid credentials";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            do_die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database credentials.\r\n");
        }
        if ($arrConfig['gammuenable'] == '1') {
            $gammudb = @mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
        }
    }
}

while ($arrConfig['gammuenable'] == '1' && ! mysql_select_db($arrConfig['gammudatabase'], $gammudb)) {
    if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport'] && $rootdb) {
        switch (force_readline("\r\nThe Gammu detabase does not exist. Would you like to set it up? (Y/N)")) {
        case 'Y':
        case 'y':
            $sql = "CREATE DATABASE IF NOT EXISTS `{$arrConfig['gammudatabase']}`;";
            $qry = mysql_query($sql, $rootdb);
            if(mysql_errno($rootdb) > 0) {
                do_die("Couldn't create database. Are you sure you provided the correct root credentials?");
            }
            $sql = "GRANT ALL PRIVILEGES ON `{$arrConfig['gammudatabase']}` . * TO '{$arrConfig['gammuuser']}'@'%';";
            $qry = mysql_query($sql, $rootdb);
            if(mysql_errno($rootdb) > 0) {
                do_die("Couldn't grant database privileges. Are you sure you provided the correct root credentials?");
            }
            break;
        case 'N':
        case 'n':
            do_die("\r\nCould not connect to the gammu database. Please ensure this exists before proceeding.\r\n");
            break;
        }
    } elseif($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] != $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
        switch(substr(force_readline_reverse("The gammu database does not exist, but it is not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - invalid credentials";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            do_die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database credentials.\r\n");
        }
    } else {
        do_die("\r\nThe gammu database does not exist, and we can't proceed as root access has not been provided.\r\n");
    }
}

echo "Done\r\n";

echo "(4/10) Ensuring your external libraries are installed\r\n";

$Libraries = array(
    'php-openid' => array('ver' => 'current', 'source' => 'git'),
    'TwitterHelper' => array('ver' => 'current', 'source' => 'git'),
    'Smarty' => array('ver' => '3.1.14', 'source' => 'http://www.smarty.net/files/Smarty-3.1.14.tar.gz'),
    'jQueryMobile' => array('ver' => '1.3.1', 'source' => 'http://jquerymobile.com/resources/download/jquery.mobile-1.3.1.zip'),
    'jQuery' => array('ver' => '1.10.2', 'source' => 'http://code.jquery.com/jquery-1.10.2.min.js'),
    'jQueryClock' => array('ver' => 'current', 'source' => 'git'),
    'sketchdocicons' => array('ver' => 'current', 'source' => 'http://github.com/downloads/sketchdock/111-Free-Ecommerce-Icons/111-free-ecommerce-icons-by-sketchdock.zip', 'license' => 'CC By V3 Unported')
);

echo " * Git Submodules (php-openid, TwitterHelper and jQueryClock): ";
chdir(dirname(__FILE__) . '/..');
do_exec('git submodule update --init');
echo "Done\r\n";

echo " * Smarty {$Libraries['Smarty']['ver']}: ";
if (!file_exists(dirname(__FILE__) . '/../ExternalLibraries/Smarty')) {
    mkdir(dirname(__FILE__) . '/../ExternalLibraries/Smarty');
}
chdir(dirname(__FILE__) . '/../ExternalLibraries/Smarty');
do_exec("wget -O Smarty-{$Libraries['Smarty']['ver']}.tar.gz {$Libraries['Smarty']['source']}");
do_exec("tar xfz Smarty-{$Libraries['Smarty']['ver']}.tar.gz");
echo "Done\r\n";

echo " * jQueryMobile {$Libraries['jQueryMobile']['ver']}: ";
if (!file_exists(dirname(__FILE__) . '/../Media/JQM')) {
    mkdir(dirname(__FILE__) . '/../Media/JQM');
}
chdir(dirname(__FILE__) . '/../Media/JQM');
do_exec("wget -O jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip {$Libraries['jQueryMobile']['source']}");
do_exec("unzip -u jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip -d .");
unlink("jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip");
echo "Done\r\n";

chdir(dirname(__FILE__) . '/../Media');
echo " * jQuery {$Libraries['jQuery']['ver']}: ";
do_exec("wget -O JQM/jquery-{$Libraries['jQuery']['ver']}.min.js {$Libraries['jQuery']['source']}");
echo "Done\r\n";

echo " * Sketchdoc Icon Library {$Libraries['sketchdocicons']['ver']}: ";
if (!file_exists(dirname(__FILE__) . '/../Media/images')) {
    do_exec("wget -O icons-{$Libraries['sketchdocicons']['ver']}.zip {$Libraries['sketchdocicons']['source']}");
    do_exec("unzip icons-{$Libraries['sketchdocicons']['ver']}.zip -d .");
    unlink("icons-{$Libraries['sketchdocicons']['ver']}.zip");
    rename('sketchdock-ecommerce-icons', 'images');
    echo "Done\r\n";
} else {
    echo "Skipped\r\n";
}
chdir(dirname(__FILE__));

echo "(5/10) Building config file: ";

$oldfile = explode("\n", file_get_contents(dirname(__FILE__) . '/../config/local.dist.php'));
$newfile = array();
foreach ($oldfile as $oldline) {
    switch(substr($oldline, 4, 7)) {
    case 'RW_TYPE':
        $newfile[] = "\$RW_TYPE = '{$arrConfig['coretype']}';";
        break;
    case 'RW_HOST':
        $newfile[] = "\$RW_HOST = '{$arrConfig['corehost']}';";
        break;
    case 'RW_PORT':
        $newfile[] = "\$RW_PORT = '{$arrConfig['coreport']}';";
        break;
    case 'RW_BASE':
        $newfile[] = "\$RW_BASE = '{$arrConfig['coredatabase']}';";
        break;
    case 'RW_USER':
        $newfile[] = "\$RW_USER = '{$arrConfig['coreuser']}';";
        break;
    case 'RW_PASS':
        $newfile[] = "\$RW_PASS = '{$arrConfig['corepass']}';";
        break;
    default:
        $newfile[] = $oldline;
        break;
    }
}
file_put_contents($config_file, implode("\n", $newfile));
echo "Done\r\n";

echo "(6/10) Running Core Database Configuration: ";
while (! mysql_select_db($arrConfig['coredatabase'], $coredb)) {
    switch (force_readline("\r\nThe core detabase does not exist. Would you like to set it up? (Y/N)")) {
    case 'Y':
    case 'y':
        if ($rootdb) {
            try {
                if(!mysql_query("CREATE DATABASE IF NOT EXISTS `{$arrConfig['coredatabase']}`;", $rootdb)) {
                    throw new Exception("Couldn't create database. Are you sure you provided the correct root credentials?");
                }
                if(!mysql_query("GRANT ALL PRIVILEGES ON `{$arrConfig['coredatabase']}` . * TO '{$arrConfig['coreuser']}'@'%';", $rootdb)) {
                    throw new Exception("Couldn't grant database privileges. Are you sure you provided the correct root credentials?");
                }
            } catch (Exception $e) {
                help();
                do_die("\r\n".$e->getMessage()."\r\n");
            }
        } else {
            do_die("\r\nCould not connect to the databse as the root user. Are you sure you provided the right credentials?\r\n");
        }
        $run_init = 1;
        break;
    case 'N':
    case 'n':
        do_die("\r\nCould not connect to the core database. Please ensure this exists before proceeding.");
        break;
    }
}

while ($run_init == 0) {
    switch (force_readline("\r\nWould you like to drop and initialize the database tables? (Y/N)")) {
    case 'Y':
    case 'y':
        $run_init = 1;
        break;
    case 'N':
    case 'n':
        $run_init = -1;
        break;
    }
}
if ($run_init == 1) {
    if ($arrConfig['loaddemo'] == 1) {
        include_once dirname(__FILE__) . '/initialize_with_demo_data.php';
    } else {
        include_once dirname(__FILE__) . '/initialize.php';
    }
}
echo "\r\nDone\r\n";

echo "(7/10) Accessing and configuring Gammu Databases: ";

mysql_query('SELECT Version FROM gammu', $gammudb);
while ($arrConfig['gammuenable'] == '1' && mysql_errno($gammudb) > 0) {
    if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport'] && $rootdb) {
        switch (force_readline("\r\nThe Gammu tables have not been created. Would you like to set it up? (Y/N)")) {
        case 'Y':
        case 'y':
            $file = explode(';', `gunzip -c "{$arrConfig['gammufile']}"`);
            mysql_select_db($arrConfig['gammudatabase'], $rootdb);
            foreach ($file as $sql) {
                if (mysql_query($sql, $rootdb) && mysql_errno($rootdb) > 0) {
                    do_die("\r\nUnable to import Gammu file: " . mysql_errno($rootdb) . " " . mysql_error($rootdb));
                }
            }
            break;
        case 'N':
        case 'n':
            do_die("\r\nCould not see the tables in the Gammu Database. Please ensure this exists before proceeding.\r\n");
            break;
        }
    } elseif($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] != $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
        switch(substr(force_readline_reverse("The gammu tables do not exist, but it is not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - no tables.";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            do_die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database tables.\r\n");
        }
    } else {
        do_die("\r\nThe gammu database does not exist, and we can't proceed as root access has not been provided.\r\n");
    }
    mysql_query('SELECT Version FROM gammu', $gammudb);
}
echo "Done";

echo "\r\n(8/10) Building Gammu SMSD config file: ";
if ($arrConfig['gammuenable'] == 1) {
    switch(force_readline("\nWould you like to configure Gammu to enable the SMS interface? (Y/N)")) {
    case 'Y':
    case 'y':
        $sql = 'INSERT INTO ' . $arrConfig['coredatabase'] . '.secureconfig (`key`, `value`, `lastChange`) VALUES '
        . "('Gammu_DBType', '{$arrConfig['gammutype']}', now()), "
        . "('Gammu_DBHost', '{$arrConfig['gammuhost']}', now()), "
        . "('Gammu_DBPort', '{$arrConfig['gammuport']}', now()), "
        . "('Gammu_DBUser', '{$arrConfig['gammuuser']}', now()), "
        . "('Gammu_DBPass', '{$arrConfig['gammupass']}', now()), "
        . "('Gammu_DBBase', '{$arrConfig['gammudatabase']}', now())";
        mysql_query($sql, $coredb);
        if (mysql_errno($coredb) > 0) {
            echo "Error setting Gammu Credentials: " . mysql_error($coredb) . "\r\n";
        }
        $contents = ';<?php $hold = "//This line is here to prevent casual evesdroppers from getting to the SMS SQL credentials
[gammu]
port = ' . $arrConfig['gammudevice'] . '
Connection = at

[smsd]
PhoneID = cfm2
CommTimeout = 30
DeliveryReport = sms

service = mysql
user = ' . $arrConfig['gammuuser'] . '
password = ' . $arrConfig['gammupass'] . '
host = ' . $arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] . '
database = '.$arrConfig['gammudatabase'] . '

LogFormat = textall
logfile = stdout
debuglevel = 3
;";';
        if(file_put_contents(dirname(__FILE__) . '/../config/gammu.php', $contents) === false || !file_exists(dirname(__FILE__) . '/../config/gammu.php')) {
            echo "Unable to write the config file to the file system. Please create the following file:\r\n\r\n" . implode("\n", $contents);
        }
    }
    echo "Done (to: " . dirname(__FILE__).'/../config/gammu.php' . ")";
} else {
    echo "Skipped";
}

echo "\r\n(9/10) Configuring Twitter API access: ";
if ($arrConfig['twitterenable'] == 1
    && $arrConfig['twitterconsumerkey'] != ''
    && $arrConfig['twitterconsumersecret'] != ''
    && $arrConfig['twitterusertoken'] != ''
    && $arrConfig['twitterusersecret'] != ''
) {
    $sql = 'INSERT INTO ' . $arrConfig['coredatabase'] . '.secureconfig (`key`, `value`, `lastChange`) VALUES ' 
    . "('Twitter_ConsumerKey', '{$arrConfig['twitterconsumerkey']}', now()), "
    . "('Twitter_ConsumerSecret', '{$arrConfig['twitterconsumersecret']}', now()), "
    . "('Twitter_UserToken', '{$arrConfig['twitterusertoken']}', now()), "
    . "('Twitter_UserSecret', '{$arrConfig['twitterusersecret']}', now())";
    mysql_query($sql, $coredb);
    if (mysql_errno($coredb) > 0) {
        echo "Error setting Twitter Credentials: " . mysql_error($coredb) . "\r\n";
    }
}
echo "Done\r\n";

echo "(10/10) Linking _htaccess to .htaccess: ";
if (!file_exists(dirname(__FILE__) . '/../.htaccess')) {
    @link(dirname(__FILE__) . '/../_htaccess', dirname(__FILE__) . '/../.htaccess') || copy(dirname(__FILE__) . '/../_htaccess', dirname(__FILE__) . '/../.htaccess');
    echo "Done\r\n";
} else {
    echo "Skipped\r\n";
}

echo "Checking your web server: ";
if (function_exists('curl_init')) {
    sleep(5);
    $ch = curl_init("http://" . $arrConfig['webhost'] . "/SETUP/install.php");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl = curl_exec($ch);
    if (strstr($curl, "Must only be run from the command line.")) {
        echo "\r\nYou need to either enable .htaccess files, or ensure your rewrite rules\r\nre-map everything to /index.php.\r\n"
           . "To do this, check out your files in (somewhere like)\r\n"
           . "/etc/apache2/sites-enabled/000-default\r\n"
           . "and ensure that for your Document Root, the line:\r\n"
           . "Allow Override All\r\n"
           . "is set.\r\n";
    } elseif (strstr($curl, "500 Internal Server Error")) {
        echo "\r\nWhile it looks like your rewrite files are setup, there might be an issue\r\n"
           . "with them. The server is returning an HTTP status of 500.\r\n";
    } elseif (strstr($curl, "200 OK") && strstr($curl, "Done")) {
        echo "Done\r\n";
    } else {
        echo "There is an error this script has not anticipated.\r\n";
    }
} else {
    echo "Unable to detect whether your web server configuration is correct.\r\n";
}

echo "\nInstall complete. Run the following commands to start the daemons:\n\n";
echo "touch nohup.out\n";
if ($arrConfig['gammuenable'] == '1') {
    echo "nohup gammu-smsd -c " . dirname(__FILE__) . '/../config/gammu.php' . " & \n";
}
echo "nohup php -q " . dirname(__FILE__) . "/../cron.php &";
echo "\n";

function force_readline_reverse($string) {
    global $arrConfig;
    if ($arrConfig['forceyes'] == 1) {
        $arrConfig['forceyes'] = -1;
        $return = force_readline($string);
        $arrConfig['forceyes'] = 1;
    } elseif($arrConfig['forceyes'] == -1) {
        $arrConfig['forceyes'] = 1;
        $return = force_readline($string);
        $arrConfig['forceyes'] = -1;
    } else {
        $return = force_readline($string);
    }
    return $return;
}

function force_readline($string) {
    global $arrConfig;
    switch ($arrConfig['forceyes']) {
        case '1':
            echo "$string FORCE: y\r\n";
            return "y";
            break;
        case '-1':
            echo "$string FORCE: n\r\n";
            return "n";
            break;
        default:
            return readline($string);
    }
}

function help()
{
    echo "
 =============================================================================
 ======================== CampFireManager2  Installer ========================
 =============================================================================

All options can be specified with their short, medium (where offered) and long
versions, and can be specified as -v, --var, --variable (in which case, all
options barring the --ServiceENABLE should prompt you for that value), or
providing the option on the command line, as -v=value, --var=value or
--variable=value. All variable names are non-case specific, but their values
are case specific. If left blank, all values should self-populate.

 ============================= Database Defaults =============================
-t   | --type                                = Default: mysql
-h   | --host | --hostname                   = Default: localhost
-p   | --port                                = Default: 3306
-u   | --user | --username                   = Default: root
-pw  | --pass | --password                   = Default: <empty>

 =============================== Core Defaults ===============================

-cu  | --coreuser | --coreusername           = Default: <default username>
-cpw | --corepass | --corepassword           = Default: <default password>
-cd  | --corebase | --coredatabase           = Default: cfm2
-w   | --web      | --webhost                = Default: localhost

 ============================== Gammu  Defaults ==============================

-gt  | --gammutype                           = Default: <default type>
-gh  | --gammuhost | --gammuhostname         = Default: <default hostname>
-gp  | --gammuport                           = Default: <default port>
-gu  | --gammuuser | --gammuusername         = Default: <default username>
-gpw | --gammupass | --gammupassword         = Default: <default password>
-gd  | --gammubase | --gammudatabase         = Default: gammu
-gf  | --gammufile                           = Default: /usr/share/doc/
                                               ...cont: gammu-smsd/examples/
                                               ...cont: mysql.sql.gz
-gde | --gammudev  | --gammudevice           = Default: /dev/ttyUSB0
-gs  | --gammusvc  | --gammuservice          = Default: Generic
-ge  | --gammu     | --gammuenable           = Default: 1 (0 to disable)

 ============================= Twitter  Defaults =============================

-tck | --twitterck | --twitterconsumerkey    = Default: <empty>
-tck | --twitterck | --twitterconsumerkey    = Default: <empty>
-tcs | --twittercs | --twitterconsumersecret = Default: <empty>
-tut | --twitterut | --twitterusertoken      = Default: <empty>
-tus | --twitterus | --twitterusersecret     = Default: <empty>
-te  | --twitter   | --twitterenable         = Default: <empty>

============================ Other Settings =========== For Automation Only =

-y   | --yes       | --forceyes              = Default: 0 (1 to answer Yes
                                               ...cont: to everything)
-l   | --load      | --loaddemo              = Default: 0 (1 to load demo
                                               ...cont: data on top of the
                                               ...cont: predefined tables)

=============================================================================
 
";
}

function do_die($string, $value = 1) {
    echo $string;
    exit($value);
}

function do_exec($command) {
    exec($command);
}