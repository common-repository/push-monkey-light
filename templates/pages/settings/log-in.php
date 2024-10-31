<div class="row">
  <div class="col-md-4 col-md-offset-1">
    <!-- START PRIMARY PANEL -->
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Mobile and Desktop Push Notifications', 'push-monkey-light' ); ?></h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_smart phone computer screen.png'; ?>" /><?php _e( 'Automatically send push notifications directly to desktops or mobiles when new content is fresh from the oven.', 'push-monkey-light' ); ?></p>
        <p><?php _e( 'You would like a more personal touch? Sending custom notifications is just as easy.', 'push-monkey-light' ); ?> <a href="<?php echo esc_url( 'https://getpushmonkey.com?source=pm-light' ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'Learn more', 'push-monkey-light' ); ?></a></p>



      </div>
    </div>
    <!-- END PRIMARY PANEL -->
    <!-- START SUCCESS PANEL -->
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Subscribers Insigths &amp; Statistics', 'push-monkey-light' ); ?></h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_chart document.png'; ?>" /><?php _e( 'Relevant and powerful insights about your subscribers and their behaviour, right in your WordPress Dashboard.', 'push-monkey-light' ); ?></p>
        <p><?php _e( 'Easily track growth, platform and location in your', 'push-monkey-light' ); ?> <a href="<?php echo esc_url( 'https://getpushmonkey.com?source=pm-light' ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'Dashboard on geptushmonkey.com', 'push-monkey-light' ); ?></a>.</p>
      </div>
    </div>
    <!-- END SUCCESS PANEL -->
    <!-- START WARNING PANEL -->
    <div class="panel panel-warning">
      <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Best WordPress Integration', 'push-monkey-light' ); ?></h3>
      </div>
      <div class="panel-body">
        <p><img style="float: left; margin: 2rem;" src="<?php echo $pluginPath . 'img/illustrations/64_resize.png'; ?>" /><?php _e( 'A deep integration with your entire WordPress setup ensure that push notifications are sent according to the content you publish.', 'push-monkey-light' ); ?></p>
        <p><?php _e( 'To get access to the full list of features, please install the Push Monkey', 'push-monkey-light' ); ?><strong><?php _e( 'Pro', 'push-monkey-light' ); ?></strong> <?php _e( 'WordPress plugin.', 'push-monkey-light' ); ?></p>
      </div>
    </div>
    <!-- END WARNING PANEL -->

  </div><!-- .col -->
  <div class="col-md-5 col-md-offset-1">

    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Mobile and Desktop Push Notifications', 'push-monkey-light' ); ?></h3>
      </div>
      <div class="panel-body">

        <p><strong><?php _e( 'Before', 'push-monkey-light' ); ?></strong> <?php _e( 'you enable push notifications for your website, you need to create a free account on', 'push-monkey-light' ); ?> <a href="https://getpushmonkey.com/register?source=pm-light" target="_blank" rel="noopener noreferrer"><?php _e( 'getpushmonkey.com', 'push-monkey-light' ); ?></a>. <a href="https://getpushmonkey.com/register?source=pm-light" class="button" target="_blank" rel="noopener noreferrer"><?php _e( 'Click here &gt;', 'push-monkey-light' ); ?></a> <?php _e( 'to create one now.', 'push-monkey-light' ); ?> <strong><?php _e( 'No credit cards required!', 'push-monkey-light' ); ?></strong></p>

        <p><?php _e( 'After creating an account, copy your', 'push-monkey-light' ); ?> <strong><?php _e( 'Account Key', 'push-monkey-light' ); ?></strong> <?php _e( 'from the Installation page and paste it in the field below. Check this article to see', 'push-monkey-light' ); ?> <a href="https://intercom.help/push-monkey/how-to-s/how-to-get-your-account-key" target="_blank" rel="noopener noreferrer"><?php _e( 'how to get your Account Key', 'push-monkey-light' ); ?></a>.<br><?php _e( 'You can then manage your account and see more options by signing in at', 'push-monkey-light' ); ?> <a href="<?php echo esc_url( 'https://getpushmonkey.com?source=pm-light' ); ?>" target="_blank" rel="noopener noreferrer"><?php _e( 'getpushmonkey.com', 'push-monkey-light' ); ?></a>.</p>

        <h2><?php _e( 'Account Key', 'push-monkey-light' ); ?></h2>

        <form action="" class="form-horizontal" method="post">
          <div class="form-group">
            <?php if ( isset( $sign_in_error ) ) { ?>
            <div class="col-md-12">
              <div class="alert alert-danger" role="alert">
                <?php echo $sign_in_error; ?>
              </div>
            </div>
            <!-- .col -->
            <?php } ?>
            <div class="col-md-12">
              <div class="input-group">
                <div class="input-group-addon">
                  <span class="fa fa-key"></span>
                </div>
                <input type="text" name="account_key" class="form-control" placeholder="Paste here" value="<?php echo esc_html( $login_account_key ); ?>" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              <?php if( ! $signed_in ) : ?>
              <button type="submit" name="push_monkey_light_sign_in" class="btn btn-success"><?php _e( 'Activate', 'push-monkey-light' ); ?></button>
            <?php else: ?>
              <button type="submit" name="logout" class="btn btn-danger"><?php _e( 'Deactivate', 'push-monkey-light' ); ?></button>
            <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
