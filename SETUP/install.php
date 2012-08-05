#! /usr/bin/php
<?php
require_once dirname(__FILE__) . '/../classes/autoloader.php';
$objRequest = Container_Request::getRequest();
if ($objRequest->get_strRequestMethod() != 'file') {
    die("Must only be run from the command line.");
}

echo "Welcome to CampFireManager2 Installation!\r\n\r\n";

echo "(1/9) Ensuring your external libraries are installed\r\n";

$Libraries = array(
    'php-openid' => array('ver' => 'current', 'source' => 'git'),
    'TwitterHelper' => array('ver' => 'current', 'source' => 'git'),
    'Smarty' => array('ver' => '3.1.11', 'source' => 'http://www.smarty.net/files/Smarty-3.1.11.tar.gz'),
    'jQueryMobile' => array('ver' => '1.1.1', 'source' => 'http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.zip'),
    'jQuery' => array('ver' => '1.7.1', 'source' => 'http://code.jquery.com/jquery-1.7.1.min.js'),
    'jQueryClock' => array('ver' => 'current', 'source' => 'git')
);

echo " * Git Submodules (php-openid, TwitterHelper and jQueryClock): ";
chdir(dirname(__FILE__) . '/..');
exec('git submodule update --init', $return);
echo "Done\r\n";

echo " * Smarty {$Libraries['Smarty']['ver']}: ";
if (!file_exists(dirname(__FILE__) . '/../ExternalLibraries/Smarty')) {
    mkdir(dirname(__FILE__) . '/../ExternalLibraries/Smarty');
}
chdir(dirname(__FILE__) . '/../ExternalLibraries/Smarty');
exec("wget -O Smarty-{$Libraries['Smarty']['ver']}.tar.gz {$Libraries['Smarty']['source']}", $return);
exec("tar xfz Smarty-{$Libraries['Smarty']['ver']}.tar.gz", $return);
echo "Done\r\n";

echo " * jQueryMobile {$Libraries['jQueryMobile']['ver']}: ";
chdir(dirname(__FILE__) . '/../Media');
exec("wget -O jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip {$Libraries['jQueryMobile']['source']}", $return);
exec("unzip jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip -d .", $return);
unlink("jQueryMobile-{$Libraries['jQueryMobile']['ver']}.zip");
rename('jquery.mobile-' . $Libraries['jQueryMobile']['ver'], 'JQM');
echo "Done\r\n";

echo " * jQuery {$Libraries['jQuery']['ver']}: ";
exec("wget -O JQM/jquery-{$Libraries['jQuery']['ver']}.min.js {$Libraries['jQuery']['source']}", $return);
echo "Done\r\n";

chdir(dirname(__FILE__));

echo "(2/9) Parsing config options\r\n";

$run_init = 0;
$config_file = dirname(__FILE__) . '/../config/local.php';

if ( ! file_exists($config_file)) {
    $fh = fopen($config_file, 'w') or die("\n/config/local.php is not creatable. Please make sure you have permission to create and edit this file.\nYou may need root to run this script with root privileges\n");
    fwrite($fh, '');
    fclose($fh);
}

if ( ! is_writable($config_file)) {
    die("\n/config/local.php is not writable. Please make sure you have permission to create and edit this file.\nYou may need to run this script with root privileges\n");
}

$arrConfig = array(
    'roottype' => 'mysql',
    'roothost' => 'localhost',
    'rootuser' => 'root',
    'rootpass' => '',
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
    'gammuservice' => 'Generic',
    'twitterenable' => '1',
    'twitterconsumerkey' => '',
    'twitterconsumersecret' => '',
    'twitterusertoken' => '',
    'twitterusersecret' => ''
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
            if ($strKey == 'gammufile' && ! file_exists($arrConfig['gammufile'])) {
                $arrConfig['gammufile'] = $oldkey;
            }
        }	
    }
    if ($matchfound) {
        echo "\r\n * $strKey: Done";
    } else {
        help();
        die("Option $key | $parameter not found");	
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

if ($arrConfig['gammuenable'] == 1) {
    foreach (array('gammuuser', 'gammudatabase') as $part) {
        while ($arrConfig[$part] == '') {
            $arrConfig[$part] = readline("\r\nPlease supply a non-blank value for $part: ");
        }
    }
}

if ($arrConfig['coretype'] != 'mysql' || ($arrConfig['gammutype'] != 'mysql' && $arrConfig['gammuenable'] == 1)) {
    die("\r\nSorry, right now, we only support mysql based databases.\r\n");
}

echo "Done\r\n";

echo "(3/9) Accessing and configuring core database: ";

$rootdb = mysql_connect($arrConfig['roothost'] . ':' . $arrConfig['rootport'], $arrConfig['rootuser'], $arrConfig['rootpass']);
$coredb = mysql_connect($arrConfig['corehost'] . ':' . $arrConfig['coreport'], $arrConfig['coreuser'], $arrConfig['corepass']);
if (! $coredb && $coredb != false) {
    switch(substr(readine("\r\nThe non-root core user account does not exist, or the password is not correct. Would you like me to set this up for you? (Y/N): "), 0, 1)) {
    case 'Y':
    case 'y':
        if ($arrConfig['corepass'] == '') {
            switch(readline("\r\nThe non-root core user account has a blank password. Would you like me to set that to a random string? (Y/N): ")) {
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
        if (! mysql_query("CREATE USER '{$arrConfig['coreuser']}'@'%' IDENTIFIED BY '{$arrConfig['corepass']}'", $rootdb)) {
            help();
            die("\r\nCouldn't create the core database user - are you sure you've provided the root credentials?\r\n");
        }
        break;
    case 'N':
    case 'n':
        help();
        die("\r\nNot prepared to configure CFM2 with an invalid username or password\r\n");
    }
} elseif (! $coredb) {
    die("\r\nCouldn't proceed - the non-core user account did not exist, and the root credentials were not supplied.\r\n");
}

while (! mysql_select_db($arrConfig['coredatabase'], $coredb)) {
    switch (readline("\r\nThe core detabase does not exist. Would you like to set it up? (Y/N)")) {
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
                die("\r\n".$e->getMessage()."\r\n");
            }
        } else {
            die("\r\nCould not connect to the databse as the root user. Are you sure you provided the right credentials?\r\n");
        }
        $run_init = 1;
        break;
    case 'N':
    case 'n':
        die("\r\nCould not connect to the core database. Please ensure this exists before proceeding.");
        break;
    }
}

echo "Done\r\n";

echo "\r\n(4/9) Building config file: ";

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

echo "(5/9) Running Core Database Configuration: ";
while ($run_init == 0) {
    switch (readline("\r\nWould you like to drop and initialize the database tables? (Y/N)")) {
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
    include_once dirname(__FILE__) . '/initialize.php';
}
echo "\r\nDone\r\n";

echo "(6/9) Accessing and configuring Gammu Databases: ";

if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
    $gammudb = mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
    while ($arrConfig['gammuenable'] == '1' && ! $gammudb) {
        if ($coredb != false) {
            switch(substr(readine("\r\nThe gammu database user account does not exist, or the password is not correct. Would you like me to set this up for you? (Y/N): "), 0, 1)) {
            case 'Y':
            case 'y':
                if ($arrConfig['gammupass'] == '') {
                    switch(readline("\r\nThe gammu database user account has a blank password. Would you like me to set that to a random string? (Y/N): ")) {
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
                if (! mysql_query("CREATE USER '{$arrConfig['coreuser']}'@'%' IDENTIFIED BY '{$arrConfig['corepass']}'", $rootdb)) {
                    help();
                    die("\r\nCouldn't create the gammu database user - are you sure you've provided the root credentials?\r\n");
                }
                break;
            case 'N':
            case 'n':
                echo "\r\nDisabling the Gammu account - invalid credentials";
                $arrConfig['gammuenable'] = 0;
                break;
            }
            if ($arrConfig['gammuenable'] == '1') {
                $gammudb = mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
            }
        } else {
            die("\r\nCouldn't proceed - the gammu database user account did not exist, and the root credentials were not supplied.\r\n");
        }
    }
} elseif ($arrConfig['gammuenable'] == '1') {
    $gammudb = mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
    while ($arrConfig['gammuenable'] == '1' && ! $gammudb) {
        switch(substr(readline("The gammu database credentials do not work, but are not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - invalid credentials";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database credentials.\r\n");
        }
        if ($arrConfig['gammuenable'] == '1') {
            $gammudb = mysql_connect($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'], $arrConfig['gammuuser'], $arrConfig['gammupass']);
        }
    }    
}

while ($arrConfig['gammuenable'] == '1' && ! mysql_select_db($arrConfig['gammudatabase'], $gammudb)) {
    if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport'] && $rootdb) {
        switch (readline("\r\nThe Gammu detabase does not exist. Would you like to set it up? (Y/N)")) {
        case 'Y':
        case 'y':
            try {
                if(!mysql_query("CREATE DATABASE IF NOT EXISTS `{$arrConfig['gammudatabase']}`;", $rootdb)) {
                    throw new Exception("Couldn't create database. Are you sure you provided the correct root credentials?");
                }
                if(!mysql_query("GRANT ALL PRIVILEGES ON `{$arrConfig['gammudatabase']}` . * TO '{$arrConfig['gammuuser']}'@'%';", $rootdb)) {
                    throw new Exception("Couldn't grant database privileges. Are you sure you provided the correct root credentials?");
                }
            } catch (Exception $e) {
                help();
                die("\n".$e->getMessage()."\n");
            }
            break;
        case 'N':
        case 'n':
            die("\r\nCould not connect to the gammu database. Please ensure this exists before proceeding.\r\n");
            break;
        }
    } elseif($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] != $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
        switch(substr(readline("The gammu database does not exist, but it is not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - invalid credentials";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database credentials.\r\n");
        }
    } else {
        die("\r\nThe gammu database does not exist, and we can't proceed as root access has not been provided.\r\n");
    }
}

while ($arrConfig['gammuenable'] == '1' && ! mysql_query('SELECT Version FROM gammu', $gammudb)) {
    if ($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] == $arrConfig['roothost'] . ':' . $arrConfig['rootport'] && $rootdb) {
        switch (readline("\r\nThe Gammu tables have not been created. Would you like to set it up? (Y/N)")) {
        case 'Y':
        case 'y':
            $file = explode(';', `gunzip -c "{$arrConfig['gammufile']}"`);
            foreach ($file as $sql) {
                mysql_select_db($arrConfig['gammudatabase'], $rootdb);
                if (! mysql_query($sql, $rootdb) && mysql_errno() != 1065) {
                    die("\r\nUnable to import Gammu file: " . mysql_error());
                }
            }
            break;
        case 'N':
        case 'n':
            die("\r\nCould not see the tables in the Gammu Database. Please ensure this exists before proceeding.\r\n");
            break;
        }
    } elseif($arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'] != $arrConfig['roothost'] . ':' . $arrConfig['rootport']) {
        switch(substr(readline("The gammu tables do not exist, but it is not on this server. Do you want to proceed without configuring Gammu? If you say No, I will terminate this script to start again. You can press return to retry. (Y/N/other): "), 0, 1)) {
        case 'Y':
        case 'y':
            echo "\r\nDisabling the Gammu account - no tables.";
            $arrConfig['gammuenable'] = 0;
            break;
        case 'N':
        case 'n':
            die("\r\nYou requested we stop this script so you can identify what is wrong with your Gammu database tables.\r\n");
        }
    } else {
        die("\r\nThe gammu database does not exist, and we can't proceed as root access has not been provided.\r\n");
    }
}
echo "Done\r\n";

echo "\r\n(7/9) Building Gammu SMSD config file: ";
if ($arrConfig['gammuenable'] == 1 && is_writable(dirname(__FILE__) . '/../config/gammu.php')) {
    switch(readline("\nWould you like to configure Gammu to enable the SMS interface? (Y/N)")) {
    case 'Y':
    case 'y':
        $contents = array(
            ';<?php $hold = "//This line is here to prevent casual evesdroppers from getting to the SMS SQL credentials',
            '[gammu]',
            'port = ' . $arrConfig['gammudevice'],
            'Connection = at',
            '',
            '[smsd]',
            'PhoneID = phone' . $arrConfig['gammuservice'],
            'CommTimeout = 30',
            'DeliveryReport = sms',
            '',
            'service = mysql',
            'user = ' . $arrConfig['gammuuser'],
            'password = ' . $arrConfig['gammupass'],
            'host = ' . $arrConfig['gammuhost'] . ':' . $arrConfig['gammuport'],
            'database = '.$arrConfig['gammudatabase'].
            '',
            'LogFormat = textall',
            'logfile = stdout',
            'debuglevel = 3',
            ';";'
        );
        file_put_contents($_SERVER['HOME'].'/phone'.$id.'.gammu', implode("\n", $contents));
        $sql = 'INSERT INTO secureconfig (`key`, `value`) VALUES '
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBType', '{$arrConfig['gammutype']}'), "
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBHost', '{$arrConfig['gammuhost']}'), "
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBPort', '{$arrConfig['gammuport']}'), "
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBUser', '{$arrConfig['gammuuser']}'), "
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBPass', '{$arrConfig['gammupass']}'), "
        . "('Glue_Gammu-{$arrConfig['gammuservice']}_DBBase', '{$arrConfig['gammudatabase']}')";
        mysql_query($sql, $coredb);
    }
}
echo "Done\r\n";

echo "\r\n(8/9) Configuring Twitter API access: ";
if ($arrConfig['twitterenable'] == 1
    && $arrConfig['twitterconsumerkey'] != ''
    && $arrConfig['twitterconsumersecret'] != ''
    && $arrConfig['twitterusertoken'] != ''
    && $arrConfig['twitterusersecret'] != ''
) {
    $sql = 'INSERT INTO secureconfig (`key`, `value`) VALUES ' 
    . "('Glue_TwitterAPI-Broadcast_ConsumerPrefix', 'Twitter'), "
    . "('Twitter_ConsumerKey', '{$arrConfig['twitterconsumerkey']}'), "
    . "('Twitter_ConsumerSecret', '{$arrConfig['twitterconsumersecret']}'), "
    . "('Glue_TwitterAPI-Broadcast_UserToken', '{$arrConfig['twitterusertoken']}'), "
    . "('Glue_TwitterAPI-Broadcast_UserSecret', '{$arrConfig['twitterusersecret']}')";
    mysql_query($sql, $coredb);
}
echo "Done\r\n";

echo "\r\n(9/9) Linking _htaccess to .htaccess: ";
link(dirname(__FILE__) . '/../_htaccess', dirname(__FILE__) . '/../.htaccess');
echo "Done\r\n";

echo "\nInstall complete. Run the following commands to start the daemons:\n\n";
echo "touch nohup.out\n";
echo "nohup gammu-smsd -c " . dirname(__FILE__) . '/../config/gammu.php' . " & \n";
echo "nohup php -q " . dirname(__FILE__) . "/../cron.php &\n";
echo "nohup php -q " . dirname(__FILE__) . "/../glue.php &";
echo "\n";

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

 =============================================================================
";
}
