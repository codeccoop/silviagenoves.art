<?php
# require get_template_directory() . "/inc/template-tags.php";

add_action("wp_enqueue_scripts", "silvia_genoves_enqueue_scripts");
function silvia_genoves_enqueue_scripts () {
    wp_enqueue_style(
        "silvia-genoves-style",
        get_stylesheet_uri(),
        array(
            "eksell-style"
        ),
        wp_get_theme()->get("Version")
    );

    /* wp_register_script(
	    "silvia-genoves-dropdown",
            get_stylesheet_directory_uri() . "/js/dropdown.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-dropdown"); */

    wp_register_script(
	    "silvia-genoves-breadcrumb",
            get_stylesheet_directory_uri() . "/js/breadcrumb.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-breadcrumb");

    wp_register_script(
	    "silvia-genoves-custom-filters",
	    get_stylesheet_directory_uri() . "/js/custom_filters.js",
	    array("jquery"),
	    wp_get_theme()->get("Version"),
	    true
    );
    wp_enqueue_script("silvia-genoves-custom-filters");
    wp_localize_script("silvia-genoves-custom-filters", "sg_ajax_filters", array(
	"ajaxurl" => esc_url(admin_url("admin-ajax.php"))
    ));
}

add_action("pre_get_posts", "silvia_genoves_sort_reversed");
function silvia_genoves_sort_reversed ($query) {
    if ($query->is_home() && $query->is_main_query()) {
        $query->set("order", "ASC");
        $query->set("category_name", "Fotografía");
    } else if ($query->is_category()) {
	$query->set("order", "ASC");
    }
}

add_action("eksell_archive_header_end", "eksell_the_archive_filter");
function eksell_the_archive_filter () {

	// Check if we're showing the filter
	if (!eksell_show_home_filter()) return;

	$terms = sg_get_filter_terms();

	if (!$terms) return;

	$home_url = "";
	$post_type 	= "";

	// Determine the correct home URL to link to.
	if (is_home()) {
		$post_type = "post";
		$home_url = home_url();
	} elseif (is_post_type_archive()) {
		$post_type	= get_post_type();
		$home_url = get_post_type_archive_link($post_type);
	}

	// Make the home URL filterable. If you change the taxonomy of the filtration with `eksell_home_filter_get_terms_args`,
	// you might want to filter this to make sure it points to the correct URL as well (or maybe remove it altogether).
	$home_url = apply_filters("eksell_filter_home_url", $home_url); ?>
		<div class="filter-wrapper i-a a-fade-up a-del-200">
			<ul class="filter-list reset-list-style"><!--
			<?php if ($home_url) : ?>
				<li><a class="filter-link active" data-filter-post-type="<?php echo esc_attr($post_type);?>" href="<?php echo esc_url($home_url);?>"><?php esc_html_e("Show At", "eksell"); ?></a></li>
            <?php endif; ?> -->
			<?php
            $is_first = true;
            # foreach ($terms as $term) :
	    foreach ($terms["main"] as $term) :
                if ($term->slug != "joyeria") : ?>
                <li><a class="filter-link <?php echo $is_first ? 'active' : ''; ?>" data-filter-term-id="<?php echo esc_attr( $term->term_id ); ?>" data-filter-taxonomy="<?php echo esc_attr( $term->taxonomy ); ?>" data-filter-post-type="<?php echo esc_attr( $post_type ); ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo $term->name; ?></a></li>
                <?php else : ?>
                <li class="filter-link-parent">
                    <a class="filter-link" data-filter-term-id="<?php echo esc_attr($term->term_id); ?>" data-filter-taxonomy="<?php echo esc_attr($term->taxonomy); ?>" data-filter-post-type="<?php echo esc_attr($post_type); ?>" href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>
                    <ul class="filter-link-menu">
                    <?php # foreach ($nested_terms as $term) : ?>
		    <?php foreach ($terms["nested"] as $term) : ?>
                        <li><a class="filter-link" data-filter-term-id="<?php echo esc_attr($term->term_id); ?>" data-filter-taxonomy="<?php echo esc_attr($term->taxonomy); ?>" data-filter-post-type="<?php echo esc_attr($post_type); ?>" href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a></li>
                    <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; $is_first = false; ?>
			<?php endforeach; ?>
		</ul>
	</div> <?php
}

function sg_get_filter_terms () {
	$db_terms = get_terms(apply_filters("eksell_home_filter_get_terms_args", array(
		"depth" => 1,
		"taxonomy" => "category"
	)));

    $terms_order = array("fotografia", "espectaculos", "plastica", "joyeria");
    $nested_terms_order = array("oro-plata-y-piedras preciosas", "resina", "resina-y-plata", "resina-y-piedras-semipreciosas");

    $terms = array();
    $nested_terms = array();
    foreach ($db_terms as $term) {
        $index = array_search($term->slug, $terms_order);
        if ($index !== false) {
            $terms[$index] = $term;
        } else if ($term->parent) {
            $index = array_search($term->slug, $nested_terms_order);
       		$nested_terms[$index] = $term;
        }
    }

    ksort($terms);
	ksort($nested_terms);

	return array(
		"main" => $terms,
		"nested" => $nested_terms
	);
}

function sg_aside_filters () {
	$terms = sg_get_filter_terms();
    $cat = get_query_var("cat");

    $nested_ids = array();
    foreach ($terms["nested"] as $term) {
        $nested_ids[] = $term->term_id;
    }

	foreach ($terms["main"] as $term) : ?>
		<li class="page_item <?php echo $term->term_id == $cat ? "current_page_item" : ""; ?>">
		<a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>
		<?php if ($term->slug == "joyeria") : ?>
                <ul class="nested-menu reset-list-style <?php echo $term->term_id == $cat || in_array($cat, $nested_ids) ? "nested-menu__open" : ""; ?>">
		<?php foreach ($terms["nested"] as $term) : ?>
			<li class="page_item page_item_nested <?php echo $term->term_id == $cat ? "current_page_item" : ""; ?>">
				<a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo $term->name; ?></a>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</li>
	<?php endforeach;?>
    <?php
}

function eksell_ajax_filters () {

	// Get the filters from AJAX.
	$term_id = isset($_POST["term_id"]) ? $_POST["term_id"] : null;
	$taxonomy = isset($_POST["taxonomy"]) ? $_POST["taxonomy"] : "";
	$post_type = isset($_POST["post_type"]) ? $_POST["post_type"] : "";

	$args = array(
		"ignore_sticky_posts" => false,
		"post_status" => "publish",
		"post_type" => $post_type,
        	"order" => "ASC"
	);

	if ($term_id && $taxonomy) {
		$args["tax_query"] = array(array(
			"taxonomy"=> $taxonomy,
			"terms" => $term_id
		));
	}

	$custom_query = new WP_Query($args);

	// Combine the query with the query_vars into a single array.
	$query_args = array_merge($custom_query->query, $custom_query->query_vars);

	// If max_num_pages is not already set, add it.
	if (!array_key_exists("max_num_pages", $query_args)) {
		$query_args["max_num_pages"] = $custom_query->max_num_pages;
	}

	// Format and return.
	echo json_encode( $query_args );

	wp_die();
}

add_action("wp_ajax_nopriv_eksell_ajax_filters", "eksell_ajax_filters");
add_action("wp_ajax_eksell_ajax_filters", "eksell_ajax_filters");


function eksell_ajax_load_more () {
	$query_args = json_decode(wp_unslash($_POST["json_data"]), true);

	$ajax_query = new WP_Query($query_args);

	// Determine which preview to use based on the post_type.
	$post_type = $ajax_query->get("post_type");

	// Default to the "post" post type for mixed content.
	if ( !$post_type || is_array($post_type)) {
		$post_type = "post";
	}

	if ($ajax_query->have_posts()) :
		while ($ajax_query->have_posts()) : 
			$ajax_query->the_post();
			global $post;

			$tags = wp_get_post_tags($post->ID);
			$span = count($tags) > 0;
			if ($span) {
				foreach ($tags as $tag) {
					if ($tag->slug == "col-3") {
						$span = 3;
					} else if ($tag->slug == "col-2") {
						$span = 2;
					} else {
						$span = 1;
					}
				}
			}
			?>
			<div class="article-wrapper col <?php echo $span ? "col-span-{$span}" : ""; ?>">
				<?php get_template_part("inc/parts/preview", $post_type); ?>
			</div>
			<?php
		endwhile;
	endif;
	wp_die();
}

add_action("wp_ajax_nopriv_eksell_ajax_load_more", "eksell_ajax_load_more");
add_action("wp_ajax_eksell_ajax_load_more", "eksell_ajax_load_more");

add_action("after_setup_theme", "woocommerce_support");
function woocommerce_support () {
    add_theme_support("woocommerce");
}

// if (class_exists("Woocommerce")) {
//     add_filter("woocommerce_enqueue_styles", "__return_empty_array");
// }

add_filter("woocommerce_show_page_title", "sg_hide_woocommerce_title");
function sg_hide_woocommerce_title () {
    return false;
}

remove_action("woocommerce_before_main_content", "woocommerce_breadcrumb", 20);
remove_action("woocommerce_before_shop_loop", "woocommerce_catalog_ordering", 30);
remove_action("woocommerce_before_shop_loop", "woocommerce_result_count", 20);
remove_action("woocommerce_sidebar", "woocommerce_get_sidebar", 10);

add_action("woocommerce_before_shop_loop", "sg_before_shop_loop");
function sg_before_shop_loop () {
    echo sg_woocommerce_shop_menu();
}

function sg_woocommerce_shop_menu () {
    $cat = get_query_var("product_cat");

    $terms = get_terms("product_cat", array());
    if ($terms) {
        echo "<ul class=\"sg-woocommerce-categories-menu\">";
        foreach ($terms as $term) {
            $is_active = $term->slug == $cat;
            if ($is_active) {
                echo "<li class=\"sg-woocommerce-categories-menu__item active\">";
            } else {
                echo "<li class=\"sg-woocommerce-categories-menu__item\">";
            }
            echo "<a href=\"" . esc_url(get_term_link($term)) . "\" class=\"" . $term->slug . "\">";
            echo $term->name;
            echo "</a></li>";
        }
        echo "</ul>";
    }
}

function woocommerce_template_loop_add_to_cart ($args=array()) {}

add_action("woocommerce_after_shop_loop", "sg_after_shop_loop");
function sg_after_shop_loop () {
    echo "<div class=\"sg-woocommerce-global-message\"><p>Todos los productos de esta tienda están elaborados fuera de la industria, cada pieza es única y exclusiva que esta concebida y elaborada como una pequeña escultura. A cada una me he entregado con insistencia para procurarle alma.</p></div>";
}


/*CANVIS PAU*/
/*Treiem les pestanyes de valoracions i descripció de sota */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
/*Treiem les etiquetes de categoria i element*/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
/*Eliminem els related products*/
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

?>
