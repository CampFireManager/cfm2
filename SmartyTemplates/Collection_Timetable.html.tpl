<!DOCTYPE html>
<html lang="en">
    <head>
<!-- This block replaces the generic title with the Smarty Assigned Site_Name value -->
<!--SM:if isset($SiteConfig.Site_Name):SM-->
    <!--SM:assign var=title_open value='<title>':SM-->
    <!--SM:assign var=title_close value='</title>':SM-->
        <!--SM:$title_open:SM--><!--SM:$SiteConfig.Site_Name:SM--><!--SM:$title_close:SM-->
<!--SM:else:SM-->
        <title>Campfire Manager</title>
<!--SM:/if:SM-->
<!-- This block sets the base path for all HTTP requests. Only valid for non-phonegap -->
<!--SM:if isset($SiteConfig.baseurl):SM-->
    <!--SM:assign var=base_open value='<base href="':SM-->
    <!--SM:assign var=base_close value='" />':SM-->
        <!--SM:$base_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$base_close:SM-->
    <!--SM:assign var=localStorage_open value='<script type="text/Javascript">if (!localStorage.getItem("CFM")) {localStorage.setItem("CFM", JSON.stringify("':SM-->
    <!--SM:assign var=localStorage_close value='rest"));}</script>':SM-->}
        <!--SM:$localStorage_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$localStorage_close:SM-->
<!--SM:/if:SM-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="media/JQM/jquery.mobile-1.1.1.min.css">
        <script src="media/JQM/jquery-1.7.1.min.js"></script>
        <script src="media/JQM/jquery.mobile-1.1.1.min.js"></script>
        <!-- <script src="media/CampFireManager.js"></script> -->
    </head>
    <body>
        <input type="hidden" id="pageIdentifier" page="thegrid" />
        <div data-role="page" id="timetable">
            <div data-role="header">
                <a href="#" data-icon="refresh">Refresh</a>
<!-- This block replaces the generic title with the Smarty Assigned Site_Name value -->
<!--SM:if isset($SiteConfig.Site_Name):SM-->
    <!--SM:assign var=title_open value='<h1>':SM-->
    <!--SM:assign var=title_close value='</h1>':SM-->
                <!--SM:$title_open:SM--><!--SM:$SiteConfig.Site_Name:SM--><!--SM:$title_close:SM-->
<!--SM:else:SM-->
                <h1>Campfire Manager</h1>
<!--SM:/if:SM-->
<!-- This block replaces the generic title with the Smarty Assigned Site_Name value -->
<!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
    <!--SM:assign var=logout value='<a href="?logout=1" data-role="button">Logout</a>':SM-->
                <!--SM:$logout:SM-->
<!--SM:else:SM-->
                <a href="#login" data-role="button" data-rel="dialog" data-transition="pop" data-icon="gear">Login</a>
<!--SM:/if:SM-->
            </div>
            <div data-role="content">
                <ul data-role="listview" id="thegrid" data-theme="d" data-divider-theme="d">
<!--SM:include file="Default_Timetable.tpl":SM-->
                </ul>
            </div>
            <div data-role="footer" data-position="fixed">
                <div data-role="navbar">
                    <ul>
                        <li><a href="timetable" class="ui-btn-active ui-state-persist" data-icon="grid">The Grid</a></li>
<!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
    <!--SM:assign var=attending value='<li><a href="attendee/me" data-icon="star">I\'m attending</a></li>':SM-->
    <!--SM:assign var=profile_open value='<li><a href="user/':SM-->
    <!--SM:assign var=profile_close value='" data-icon="arrow-r">My Profile</a></li>':SM-->
                        <!--SM:$attending:SM-->
                        <!--SM:$profile_open:SM--><!--SM:$Object_User.current.intUserID--:SM--><!--SM:$profile_close:SM-->
<!--SM:else:SM-->
                        <li><a href="#login" data-rel="dialog" data-transition="pop" data-icon="star">I'm attending</a></li>
                        <li><a href="#login" data-rel="dialog" data-transition="pop" data-icon="arrow-r">My Profile</a></li>
<!--SM:/if:SM-->
                    </ul>
                </div>
            </div>		
        </div>
        <div data-role="page" id="login">
            <div data-role="header" data-theme="e">
<!-- This block replaces the generic title with the Smarty Assigned Site_Name value -->
<!--SM:if isset($SiteConfig.Site_Name):SM-->
        <!--SM:assign var=title_open value='<h1>Login to ':SM-->
        <!--SM:assign var=title_close value='</h1>':SM-->
        <!--SM:$title_open:SM--><!--SM:$SiteConfig.Site_Name:SM--><!--SM:$title_close:SM-->
<!--SM:else:SM-->
                <h1>Login to Campfire Manager</h1>
<!--SM:/if:SM-->
            </div>
            <div data-role="content" data-theme="d">	
                <h3>OpenID</h3>
                <form method="post" action="openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://www.google.com/accounts/o8/id" />
                    <input type="submit" value="Login with Google" />
                </form>
                <form method="post" action="openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://yahoo.com" />
                    <input type="submit" value="Login with Yahoo!" />
                </form>
                <form method="post" action="openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://myspace.com" />
                    <input type="submit" value="Login with MySpace" />
                </form>
                <form method="post" action="openid/" data-ajax="false">
                    <input type="text" name="id" size="10" value="http://" />
                    <input type="submit" value="Login with your Own OpenID Provider" />
                </form>
                <h3>Basic Authentication</h3>
                <form method="post" action="#" data-ajax="false">
                    Username: <input type="text" size="10" name="username" value="" /> 
                    Password: <input type="password" size="10" name="password" value="" /> 
                    Register? <input type="checkbox" value="true" name="register" />
                    <input type="submit" value="Login" />
                </form>
                <h3>Auth Code</h3>
                <form method="post" action="#" data-ajax="false">
                    Auth Code: <input type="text" size="10" name="code" value="" /> 
                    <input type="submit" value="Login" />
                </form>	
            </div>

            <div data-role="footer" data-theme="e">
                <p><a href="#one" data-rel="back" data-role="button" data-inline="true" data-icon="back">Cancel</a></p>
            </div>
        </div>
    </body>
</html>
