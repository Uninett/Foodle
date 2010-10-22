<?php 
  require_once("OAuth.php");
  
  class FastPass {
    public static $domain = "getsaitsfaction.com";
    
    public static function url($key, $secret, $email, $name, $uid, $is_secure=false, $additionalFields=array()) {
      $consumer = new OAuthConsumer($key, $secret);
      $url = $is_secure ? ("https://" . self::$domain . "/fastpass") : "http://" . self::$domain . "/fastpass";
      $params = array_merge($additionalFields, array("email" => $email, "name" => $name, "uid" => $uid));
      $request = OAuthRequest::from_consumer_and_token($consumer, null, "GET", $url, $params);
      $sigmethod = new OAuthSignatureMethod_HMAC_SHA1();
      $request->sign_request($sigmethod, $consumer, null);
      return $request->to_url();
    }
    
    public static function image($key, $secret, $email, $name, $uid, $is_secure=false, $additionalFields=array()) {
      $url = FastPass::url($key, $secret, $email, $name, $uid, $is_secure, $additionalFields);
      return '<img src="' . htmlspecialchars($url) . '" alt="" />';
    }
        
    public static function script($key, $secret, $email, $name, $uid, $is_secure=false, $additionalFields=array()) {
      $url = FastPass::url($key, $secret, $email, $name, $uid, $is_secure, $additionalFields);
      $result = "<script type=\"text/javascript\">\n" .
                " var GSFN;\n" .
                " if(GSFN == undefined) { GSFN = {}; }\n" .
                "  (function(){\n" .
                "    add_js = function(jsid, url) {\n" .
                "      var head = document.getElementsByTagName(\"head\")[0];\n" .
                "      script = document.createElement('script');\n" .
                "      script.id = jsid;\n" .
                "      script.type = 'text/javascript';\n" .
                "      script.src = url;\n" .
                "      head.appendChild(script);\n" .
                "    }\n" .
                "\n" .
                "    add_js(\"fastpass_common\", document.location.protocol + \"//getsatisfaction.com/javascripts/fastpass.js\");\n" .
                "\n" .
                "    if(window.onload) { var old_load = window.onload; }\n" .
                "    window.onload = function() {\n" .
                "      if(old_load) old_load();\n" .
                "      add_js(\"fastpass\", \"" . $url . "\");\n" .
                "    }\n" .
                "  })()\n" .
                "\n" .
                "</script>\n";
      return $result;
    }
  }
  
  // echo(FastPass::script(
  //   "xi2vaxgpp06m", 
  //   "ly68der0hk8idfr5c73ozyq56jpwstd1", 
  //   "scott@getsatisfaction.com", 
  //   "Scott", 
  //   "nullstyle",
  //   false,
  //   array("foo" => "bar")
  // ));
?>