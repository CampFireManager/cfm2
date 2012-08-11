<!--SM:foreach $renderPage as $DirectionScreen:SM-->
<!--SM:/foreach:SM-->
<html>
    <head>
        <title>Direction Screen</title>
        <link rel="stylesheet" type="text/css" href="../Media/directions.css" />
        <script type="text/javascript" src="<!--SM:$SiteConfig.baseurl:SM-->media/JQM/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="<!--SM:$SiteConfig.baseurl:SM-->media/jQueryClock/jquery.jclock.js"></script>
    </head>
    <body>
   
        <table width="100%" height="100%">
            <!--SM:if $DirectionScreen.toprow:SM-->
            <tr height="<!--SM:$DirectionScreen.trheight:SM-->">
                <!--SM:if $DirectionScreen.leftcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='upleft':SM-->
                <!--SM:/if:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='upcentre':SM-->
                <!--SM:if $DirectionScreen.rightcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='upright':SM-->
                <!--SM:/if:SM-->
            </tr>
            <!--SM:/if:SM-->
            <tr height="<!--SM:$DirectionScreen.trheight:SM-->">
                <!--SM:if $DirectionScreen.leftcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='left':SM-->
                <!--SM:/if:SM-->
                <td id="C" width="<!--SM:$DirectionScreen.tdwidth:SM-->">
                    <div class="time">The time now is: <span class="clock" /></div>
                    <div class="NextTime">Next talk starts at <span id="next_talk_time">
                            <!--SM:if $DirectionScreen.NextSlot == false:SM-->
                                the next event!
                            <!--SM:else:SM-->
                                <!--SM:$DirectionScreen.NextSlot.timeStart:SM-->
                            <!--SM:/if:SM-->
                        </span>
                    </div>
                </td>
                <!--SM:if $DirectionScreen.rightcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='right':SM-->
                <!--SM:/if:SM-->
            </tr>
            <!--SM:if $DirectionScreen.bottomrow:SM-->
            <tr height="<!--SM:$DirectionScreen.trheight:SM-->">
                <!--SM:if $DirectionScreen.leftcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='downleft':SM-->
                <!--SM:/if:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='downcentre':SM-->
                <!--SM:if $DirectionScreen.rightcolumn:SM-->
<!--SM:include file="Collection_DirectionScreen.tpl" direction='downright':SM-->
                <!--SM:/if:SM-->
            </tr>
            <!--SM:/if:SM-->
        </table>
        <script type="text/javascript">
          $(function($) {
            $('.clock').jclock();
          });
        </script>
    </body>
</html>
