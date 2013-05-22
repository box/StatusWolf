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
                <h4>StatusWolf Login</h4>
              </div>
            </div>
            <div class="widget-main">
              <div class="login-logo"></div>
              <div class="login-form" id="statuswolf-login">
                <div class="login-label"><h4 style="display: inline-block;">Username </h4>
                  <input type="text" id="login-username" name="username">
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
      $(document).keypress(function(e)
      {
        if (e.which == 13)
        {
          $('#statuswolf-login-form').submit();
        }
      })
    </script>
