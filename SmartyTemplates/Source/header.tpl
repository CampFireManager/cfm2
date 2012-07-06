<!DOCTYPE html>
<html>
    <head>
        <title>{$title}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="{$SiteConfig.baseurl}media/css.css" type="text/css" rel="stylesheet">
    </head>
    <body>
        <div>
            <div id="header">
                <h1>{$SiteConfig['Site_Name']}</h1>
            </div>
            <div id="useractions">
{if isset($Object_User.current.strUserName)}
                [ <a href="{$SiteConfig.baseurl}?logout">Logout</a>
                | <a href="{$SiteConfig.baseurl}user/{$User_Object.current.intUserID}">Edit my settings</a>]
{else}
                [ OpenID Login/Register: 
                    <form method="post" action="{$SiteConfig.baseurl}openid/">
                        <input type="hidden" name="id" value="http://www.google.com/accounts/o8/id" />
                        <input type="submit" value="Google" />
                    </form>
                    <form method="post" action="{$SiteConfig.baseurl}openid/">
                        <input type="hidden" name="id" value="http://yahoo.com" />
                        <input type="submit" value="Yahoo!" />
                    </form>
                    <form method="post" action="{$SiteConfig.baseurl}openid/">
                        <input type="hidden" name="id" value="http://myspace.com" />
                        <input type="submit" value="MySpace" />
                    </form>
                    <form method="post" action="{$SiteConfig.baseurl}openid/">
                        <input type="text" name="id" size="10" value="http://" />
                        <input type="submit" value="Own" />
                    </form>
                | <form method="post" action="{$SiteConfig.baseurl}">
                    Username: <input type="text" size="10" name="username" value="" /> 
                    Password: <input type="password" size="10" name="password" value="" /> 
                    Register? <input type="checkbox" value="true" name="register" />
                    <input type="submit" value="Login" />
                </form>
                | <form method="post" action="{$SiteConfig.baseurl}user">
                    Auth Code: <input type="text" size="10" name="code" value="" /> 
                    <input type="submit" value="Login" />
                </form> ]
{/if}
            </div>
            <div id="navigation"><a href="{$SiteConfig.baseurl}timetable">To the timetable!</a></div>