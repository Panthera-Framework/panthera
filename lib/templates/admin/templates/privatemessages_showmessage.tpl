<style>
#message_window {
      width: 91%;
      background-color: rgb(221, 243, 255);
      padding: 5px;
      border: 1px solid #d4d4d4;
      font-size: 11px;
      font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
      padding: 20px;
      margin: 20px;
}
</style>

    <div>{$message->sender} {function="localize(' sent a message to ', 'pmessages')"} {$message->recipient}.</div>

    <div class="grid1">
        <center><h2>{$message->title}</h2></center>
          <div id="message_window">
           <table>
             <tr> 
                 <td>{$message_content}</td>
             </tr>
           </table> 
          </div>
    </div>
