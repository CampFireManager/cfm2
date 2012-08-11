<!--SM:if isset($DirectionScreen[$direction]) && count($DirectionScreen[$direction]) > 0:SM-->
    <td id="<!--SM:$direction:SM-->" width="<!--SM:$DirectionScreen.tdwidth:SM-->">
    <!--SM:foreach $DirectionScreen[$direction] as $arrRoom:SM-->
		 
            <div id="<!--SM:$direction:SM-->_arrow">
             <img src="../Media/direction_arrows/<!--SM:$direction:SM-->.png"/>
              </div>
          <div id="<!--SM:$direction:SM-->_room">
            <br/>
            <div class = "roomName"><!--SM:$arrRoom.strRoom:SM--></div>
            <div class="Now">Now: 
            
            <!--SM:if ! isset($arrRoom.now) || $arrRoom.now == null:SM-->
                Empty
            <!--SM:else:SM-->
                <!--SM:$arrRoom.now.strTalk:SM--> by
                <!--SM:foreach $arrRoom.now.arrPresenters as $arrPresenter:SM-->
                    <!--SM:$arrPresenter.strUser:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
            <!--SM:/if:SM-->
            </div>
            <div class="Next">Next:
            <!--SM:if ! isset($arrRoom.next) || $arrRoom.next == null:SM-->
                Empty
            <!--SM:else:SM-->
                <!--SM:$arrRoom.next.strTalk:SM--> by
                <!--SM:foreach $arrRoom.next.arrPresenters as $arrPresenter:SM-->
                    <!--SM:$arrPresenter.strUser:SM--><!--SM:if ! $arrPresenter@last:SM-->,<!--SM:/if:SM-->
                <!--SM:/foreach:SM-->
            <!--SM:/if:SM-->
           </div>
        
            
            
        </div>
        <!--SM:if ! $arrRoom@last:SM--><br /><!--SM:/if:SM-->
    <!--SM:/foreach:SM-->
   <br/>
    </td>
<!--SM:else:SM-->
    <td width="<!--SM:$DirectionScreen.tdwidth:SM-->">&nbsp;</td>
<!--SM:/if:SM-->
