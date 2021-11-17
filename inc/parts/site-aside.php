<aside id="site-aside">
    <div class="site-aside-identity">
        <?php do_action("eksell_site_aside_start");
        $logo = eksell_get_custom_logo();
        $site_title = wp_kses_post(get_bloginfo("name"));
        $site_description = wp_kses_post(get_bloginfo("description"));
        $show_header_text = get_theme_mod("header_text", true);

        if ($logo) {
            $site_title_class = "site-logo";
            $home_link_contents = $logo . "<span class=\"screen-reader-text\">" . $site_title . "</span>";
        } else {
            $site_title_class = "site-title";
            $home_link_contents = "<a href=\"" . esc_url(home_url("/")) . "\" rel=\"home\">" . $site_title . "</a>";
        }

        if (is_front_page() && is_home() && !is_paged()) : ?>
            <h1 class="<?php echo $site_title_class; ?>"><?php echo $home_link_contents; ?></h1>
        <?php else : ?>
            <div class="<?php echo $site_title_class; ?>"><?php echo $home_link_contents; ?></div>
        <?php endif; ?>
        <?php if ($logo && $show_header_text && ($site_title || $site_description)) : ?>
            <div class="header-logo-text">
                <?php
                /*
		* The site title is included as screen reader text next to the logo (in the H1 element),
		* so it's hidden from screen readers here.
		*/
                if ($site_title) : ?>
                    <div class="site-title" aria-hidden="true"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php echo $site_title; ?></a></div>
                <?php endif;

                if ($site_description) : ?>
                    <div class="site-description color-secondary"><?php echo $site_description; ?></div>
                <?php endif; ?>
            </div>
        <?php elseif ($show_header_text && $site_description) : ?>
            <div class="site-description color-secondary"><?php echo $site_description; ?></div>
        <?php endif; ?>
    </div>
<?php if (is_woocommerce())  {
            sg_woocommerce_go_to_cart();
        } ?>
    <div class="site-aside-menu">
        <ul class="main-menu reset-list-style">
            <?php
            sg_aside_filters();
            wp_list_pages(array(
                // "match_menu_classes" => true,
                "exclude" => "576,577",
                "title_li" => false
            ));
            ?>
        </ul>
    </div>
    <?php do_action("eksell_site_aside_end"); ?>
</aside>
