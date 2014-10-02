<!--SM:include file="Common_Object_Header.tpl" timeout="300000":SM-->
            <div data-role="content">
                <ul data-role="listview" id="thegrid" data-theme="d" data-divider-theme="d">
                    <!--SM:include file="Default_Timetable.tpl":SM-->
                </ul>
            </div>
            <script type="text/javascript">
                if (typeof localStorage.autoscroll === "undefined") {
                    localStorage.autoscroll = "1";
                }
                if (localStorage.autoscroll === "1") {
                    /* Nasty, but only way to do it due to known race condition */
                    setTimeout(function () {
                        var x = $(".now").offset().top;
                        $.mobile.silentScroll(x);
                    }, 100);
                }
            </script>
        </div>
        <div data-role="page" id="object">
            <div data-role="header" data-theme="e">
<!-- This block replaces the generic title with the Smarty Assigned Site_Name value -->
<!--SM:if isset($SiteConfig.Site_Name):SM-->
        <!--SM:assign var=title_open value='<h1>':SM-->
        <!--SM:assign var=title_close value='</h1>':SM-->
        <!--SM:$title_open:SM--><!--SM:$SiteConfig.Site_Name:SM--><!--SM:$title_close:SM-->
<!--SM:else:SM-->
                <h1>Campfire Manager</h1>
<!--SM:/if:SM-->
            </div>
            <div data-role="content" data-theme="d">	
                <p>Sorry, you've not loaded any objects yet. Please go back to the grid and find something!</p>
            </div>

            <div data-role="footer" data-theme="e">
                <p><a href="#timetable" data-rel="back" data-role="button" data-inline="true" data-icon="back">Cancel</a></p>
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
               <div data-role="collapsible-set">
                <div data-role="collapsible" data-collapsed="false"> 
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
                    <input type="hidden" name="id" value="https://openid.stackexchange.com" />
                    <input type="submit" value="Login with StackExchange" />
                </form>
                <!--
                <form method="post" action="openid/" data-ajax="false">
                    <input type="text" name="id" size="10" value="http://" />
                    <input type="submit" value="Login with your Own OpenID Provider" />
                </form>
                -->
                </div>
                <div data-role="collapsible"> 
                <h3>Basic Authentication</h3>
                <form method="post" action="#" data-ajax="false">
                    <label>Username: <input type="text" size="10" name="username" value="" /></label>
                    <label>Password: <input type="password" size="10" name="password" value="" /></label> 
                    <label><input type="checkbox" checked="checked" name="register" />Register?</label>
                    <input type="submit" value="Login" />
                </form>
                </div>
                <div data-role="collapsible"> 
                <h3>Auth Code</h3>
                <form method="post" action="#" data-ajax="false">
                    <label>Auth Code: <input type="text" size="10" name="code" value="" /></label> 
                    <input type="submit" value="Login" />
                </form>	
                </div>
                </div>
            </div>

            <div data-role="footer" data-theme="e">
                <p><a href="#timetable" data-rel="back" data-role="button" data-inline="true" data-icon="back">Cancel</a></p>
            </div>
        </div>
    </body>
</html>
