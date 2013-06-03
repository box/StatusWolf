<?php
/**
 * footer.php
 *
 * Footer stub file for views
 *
 * Author: Mark Troyer
 * Date Created: 21 May 2013
 *
 * @package StatusWolf.Views
 */
?>

    <script type="text/javascript">
      function loadScript(url, callback){

        var script = document.createElement("script")
        script.type = "text/javascript";

        script.onload = function(){
          callback();
        };

        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
      }
    </script>

  </body>

</html>