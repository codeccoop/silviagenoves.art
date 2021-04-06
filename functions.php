<?php
add_action("wp_enqueue_scripts", "custom_theme_enqueue_scripts");
function custom_theme_enqueue_scripts () {
    wp_enqueue_style(
        "silvia-genoves-style",
        get_stylesheet_uri(),
        array(
            "eksell-style"
        ),
        wp_get_theme()->get("Version")
    );
}
?>
