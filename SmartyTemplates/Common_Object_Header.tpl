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
    <!--SM:assign var=localStorage_close value='rest"));}</script>':SM-->
        <!--SM:$localStorage_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$localStorage_close:SM-->
<!--SM:/if:SM-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="media/JQM/jquery.mobile-1.1.1.min.css">
        <script src="media/JQM/jquery-1.7.1.min.js"></script>
        <script src="media/JQM/jquery.mobile-1.1.1.min.js"></script>
        <!-- <script src="media/CampFireManager.js"></script> -->
    </head>
    <body>
        <input type="hidden" id="pageIdentifier" page="theobject" />
        <div data-role="page" id="theobject">
            <div data-role="header">
                <a href="<!--SM:$SiteConfig.thisurl:SM-->" data-icon="refresh">Refresh</a>
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
    <!--SM:assign var=logout_open value='<a href="':SM-->
    <!--SM:assign var=logout_close value='?logout=1" data-role="button">Logout</a>':SM-->
                <!--SM:$logout_open:SM--><!--SM:$SiteConfig.baseurl:SM--><!--SM:$logout_close:SM-->
<!--SM:else:SM-->
                <a href="#login" data-role="button" data-rel="dialog" data-transition="pop" data-icon="gear">Login</a>
<!--SM:/if:SM-->
            </div>