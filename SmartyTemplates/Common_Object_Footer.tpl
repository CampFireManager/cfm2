            <div data-role="footer" data-position="fixed">
                <div data-role="navbar">
                    <ul>
                        <li><a href="<!--SM:$SiteConfig.baseurl:SM-->timetable" data-icon="arrow-l">The Grid</a></li>
                        <li><a href="#theobject" class="ui-btn-active" data-icon="grid">Object</a></li>
<!--SM:if $Object_User.current != null && $Object_User.current != false:SM-->
    <!--SM:assign var=attending_open value='<li><a href="':SM-->
    <!--SM:assign var=attending_close value='attendee/me" data-icon="star">I\'m attending</a></li>':SM-->
    <!--SM:assign var=profile_open value='<li><a href="':SM-->
    <!--SM:assign var=profile_middle value='user/':SM-->
    <!--SM:assign var=profile_close value='" data-icon="arrow-r">My Profile</a></li>':SM-->
                        <!--SM:$attending_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$attending_close:SM-->
                        <!--SM:$profile_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$profile_middle:SM--><!--SM:$Object_User.current.intUserID:SM--><!--SM:$profile_close:SM-->
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
        <!--SM:$title:SM-->
<!--SM:else:SM-->
                <h1>Login to Campfire Manager</h1>
<!--SM:/if:SM-->
            </div>
            <div data-role="content" data-theme="d">	
                <h3>OpenID</h3>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://www.google.com/accounts/o8/id" />
                    <input type="submit" value="Login with Google" />
                </form>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://yahoo.com" />
                    <input type="submit" value="Login with Yahoo!" />
                </form>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->openid/" data-ajax="false">
                    <input type="hidden" name="id" value="http://myspace.com" />
                    <input type="submit" value="Login with MySpace" />
                </form>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->openid/" data-ajax="false">
                    <input type="text" name="id" size="10" value="http://" />
                    <input type="submit" value="Login with your Own OpenID Provider" />
                </form>
                <h3>Basic Authentication</h3>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->" data-ajax="false">
                    Username: <input type="text" size="10" name="username" value="" /> 
                    Password: <input type="password" size="10" name="password" value="" /> 
                    Register? <input type="checkbox" value="true" name="register" />
                    <input type="submit" value="Login" />
                </form>
                <h3>Auth Code</h3>
                <form method="post" action="<!--SM:$SiteConfig.baseurl:SM-->" data-ajax="false">
                    Auth Code: <input type="text" size="10" name="code" value="" /> 
                    <input type="submit" value="Login" />
                </form>	
            </div>

            <div data-role="footer" data-theme="e">
                <p><a href="#theobject" data-rel="back" data-role="button" data-inline="true" data-icon="back">Cancel</a></p>
            </div>
        </div>
    </body>
</html>