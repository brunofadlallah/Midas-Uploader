<html>
  <head>
    <style type="text/css">
    .content { color: #000000; width: 100%; margin: 0 auto; }
    body{ background: #E1E1E1 url(../Midas/midas/webroot/images/img1.gif) repeat-x; }
    .header { height: 100px; margin: 0 auto; width: 100%; }
    body,.table { font: small "Trebuchet MS",Arial,Helvetica,sans-serif; }    
    </style>
  </head>
  <body>
    <div class="header"></div>
    <div class="content">
      <form name="upload" method="POST" action="#" enctype="multipart/form-data" >
        <table  border="0" width="100%" class="table">
          <tr>
            <td width="35%"></td>
            <td><h2 style="text-align:left;"><strong>Upload File To MIDAS</strong></h2></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Midas Server</strong></div></td>
            <td><input name="midasServer" type="text" size="40"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Email</strong></div></td>
            <td><input name="email" type="text" id="email" size="40"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Application Name</strong></div></td>
            <td><input name="appname" type="text" id="appname" size="40"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Api Key</strong></div></td>
            <td><input name="apiKey" type="text" id="apiKey" size="40"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Item ID</strong></div></td>
            <td><input name="itemid" type="text" id="itemid" size="40"></td>
          </tr>
          <tr>
            <td><div align="right"><strong>File</strong></div></td>
            <td><input name="file" type="file" id="file" size="40"></td>
          </tr>   
          <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="SubmitFile" value="Upload"></td>
          </tr>
        </table>
      </form>
    </div>
    <div>
      <?php
      if(isset($_POST['SubmitFile']) && $_POST['SubmitFile'] === 'Upload')
        {
        //Get the token from the apikey
        $post = array();
        $post['email'] = $_POST['email'];
        $post['appname'] = $_POST['appname'];
        $post['apikey'] = $_POST['apiKey'];
         
        $url1 = curl_init();
        curl_setopt ($url1, CURLOPT_URL, $_POST['midasServer']."/api/rest/midas.login");
        curl_setopt ($url1, CURLOPT_HEADER, false);
        curl_setopt ($url1, CURLOPT_POST, true);
        curl_setopt ($url1, CURLOPT_POSTFIELDS, $post);
        curl_setopt ($url1, CURLOPT_RETURNTRANSFER, true);
        $token = curl_exec ($url1);
        curl_close ($url1);

        //Set the token
        $xmlObj = simplexml_load_string($token);
        $token = $xmlObj->token;
        
        //Use the token so the user can upload the file
        $filename = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $fp = fopen($tmp_name,'r');
        $filesize = filesize($tmp_name);
        
        $url = $_POST['midasServer']."/api/rest/midas.upload.bitstream?uuid=&itemid=".$_POST['itemid']."&mode=stream&filename=".$filename."&path=".$filename."&size=".$filesize."&token=".$token;

        $url2 = curl_init();
        curl_setopt($url2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($url2, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($url2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($url2, CURLOPT_URL, $url);
        curl_setopt($url2, CURLOPT_UPLOAD, true);
        curl_setopt($url2, CURLOPT_INFILESIZE, $filesize);
        curl_setopt($url2, CURLOPT_INFILE, $fp);
        curl_setopt($url2, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($url2);
        curl_close($url2);

        $xml = simplexml_load_string($res);

        //Returned message
        if(empty($xml))
          {
          echo '<em>'.$_POST['midasServer'].'</em><strong> server does not exist</strong>';
          }
        else
          {
          $stat = $xml->attributes();
          if($stat == 'ok')
            {
            echo '<strong>Bitstream </strong><em>'.$filename.'</em> <strong>has been uploaded successfully.</strong>';
            }
          elseif($stat == 'fail')
            {
            $msg = $xml->err->attributes();
            if($msg["msg"] == "Upload failed")
              {
              echo '<strong>'.$msg["msg"].'. The</strong><em> Item ID</em><strong> may be wrong. If not, you have to choose a file.</strong>';
              }
            elseif($msg["msg"] == "Invalid policy")
              {
              echo '<strong>'.$msg["msg"].'. Either your </strong><em>Email</em><strong> or </strong><em>Application Name</em><strong> or </strong><em>Api Key</em><strong> is wrong.</strong>';
              }
            elseif($msg["msg"] == "Parameter file is not defined")
              {
              echo '<strong>'.$msg["msg"].'. You have to choose a file.</strong>';
              }
            }
          }
        }
      ?>
    </div>
  </body>
</html>
