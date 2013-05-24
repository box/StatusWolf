<?php
/**
 * login.php
 *
 * Login screen for StatusWolf, displayed when the auth function detects
 * that there is no current authenticated user session.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 21 May 2013
 *
 * @package StatusWolf.Views
 */
?>

    <div class="container">
      <form id="statuswolf-login-form" method="post" action="<?php echo URL; ?>" style="height: 100%;">
      <div class="widget-container" id="login-widget-container">
        <div class="widget" id="login-widget">
          <div class="widget-front" id="login-widget-front">
            <div class="widget-title">
              <div class="widget-title-head">
                <h4 style="width: 40%;">StatusWolf Login</h4>
              </div>
            </div>
            <div class="widget-main">
              <div class="login-logo"></div>
              <div class="login-form" id="statuswolf-login">
                <div class="login-label"><h4 style="display: inline-block;">Username </h4>
                  <input type="text" id="login-username" name="username" <?php if (!is_null($username)) { echo 'value="' . $username . '"'; } ?>>
                </div>
                <div class="login-label"><h4 style="display: inline-block;">Password </h4>
                  <input type="password" id="login-password" name="password">
                </div>
              </div>
            </div>
            <div class="widget-footer">
              <div class="widget-footer-btn" id="login-submit" onClick="$('#statuswolf-login-form').submit()" style="none;">Login</div>
            </div>
          </div>
        </div>
      </div>
    </form>
    </div>

    <script type="text/javascript">
      var main_height = $('.widget').height() - ($('.widget-title').height() + $('.widget-footer').height());
      $('.widget-main').css('height', main_height);

      if (navigator.userAgent.search("Chrome") > 0)
      {
        $('.widget-container').css('border-bottom-width', '2px');
      }

      auth_fail = "<?php if (array_key_exists('_auth_fail', $_SESSION)) { echo $_SESSION['_auth_fail']; }?>";
      if (auth_fail.length > 0)
      {
        $('#statuswolf-login').prepend('<div id="login-fail" style="text-align: center;"></div>');
        $('#login-fail').append('<h6 style="display: inline-block; color: red;">' + auth_fail + '</h6>');
      }
      <?php unset($_SESSION['_auth_fail']); ?>

      $(document).keypress(function(e)
      {
        if (e.which == 13)
        {
          $('#statuswolf-login-form').submit();
        }
      })
    </script>
