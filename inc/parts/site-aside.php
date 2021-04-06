<aside id="site-aside">
<?php do_action( 'eksell_site_aside_start' ); ?>
<div class="site-aside-menu">
<?php
          if (has_nav_menu("main")) {
              wp_nav_menu(array(
                  "container" => "",
                  # "items_wrap" => "%3$s",
                  "show_toggles" => true,
                  "theme_location" => "Main",
                  "menu_class" => "main-menu reset-list-style"
              ));
          } else { ?>
              <ul class="main-menu reset-list-style"> <?php
              wp_list_pages(array(
                  "match_menu_classes" => true,
                  "title_li" => false
              ));
          ?></ul><?php
          }
?>
</div>
<?php do_action("eksell_site_aside_end"); ?>
</aside>
