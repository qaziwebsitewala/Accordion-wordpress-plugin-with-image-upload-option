<?php
/*
Plugin Name: Custom Accordion Plugin
Description: A simple plugin to create a toggle accordion with user-defined content.
Version: 1.0
Author: Hasan Qazi 
*/

function custom_accordion_enqueue_styles() {
    wp_enqueue_style('custom-accordion-style', plugin_dir_url(__FILE__) . 'style.css');
}

add_action('wp_enqueue_scripts', 'custom_accordion_enqueue_styles');

function custom_accordions_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-accordion-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
}

add_action('wp_enqueue_scripts', 'custom_accordions_enqueue_scripts');

function custom_accordion_enqueue_scripts() {
    if (is_admin()) {
        wp_enqueue_media();
        wp_enqueue_script('custom-accordion-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery', 'media-upload'), null, true);

        // Pass necessary data to the script
        wp_localize_script('custom-accordion-script', 'customAccordionData', array(
            'upload' => esc_url(admin_url('upload.php?post_id=0&type=image&TB_iframe=1')),
            'nonce'  => wp_create_nonce('media-form'),
        ));
    }
}

add_action('admin_enqueue_scripts', 'custom_accordion_enqueue_scripts');

function custom_accordion_options_page() {
    add_menu_page(
        'Accordion Options',
        'Accordion Options',
        'manage_options',
        'custom_accordion_options',
        'custom_accordion_options_page_content'
    );
}

function custom_accordion_options_page_content() {
    ?>
    <div class="wrap">
        <h2>Accordion Options</h2>
        <form method="post" action="options.php">
            <?php settings_fields('custom_accordion_options_group'); ?>
            <?php do_settings_sections('custom_accordion_options'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function custom_accordion_register_options() {
    register_setting('custom_accordion_options_group', 'custom_accordion_options');
    add_settings_section('custom_accordion_options_section', 'Accordion Options', '__return_false', 'custom_accordion_options');

    for ($i = 1; $i <= 5; $i++) {
        add_settings_field(
            'item' . $i . '_title',
            'Accordion Title ' . $i,
            'custom_accordion_render_field',
            'custom_accordion_options',
            'custom_accordion_options_section',
            array('label_for' => 'item' . $i . '_title', 'field' => 'item' . $i . '_title')
        );

        add_settings_field(
            'item' . $i . '_description',
            'Accordion Description ' . $i,
            'custom_accordion_render_field',
            'custom_accordion_options',
            'custom_accordion_options_section',
            array('label_for' => 'item' . $i . '_description', 'field' => 'item' . $i . '_description')
        );

        add_settings_field(
            'item' . $i . '_image',
            'Accordion Image ' . $i,
            'custom_accordion_render_field',
            'custom_accordion_options',
            'custom_accordion_options_section',
            array('label_for' => 'item' . $i . '_image', 'field' => 'item' . $i . '_image', 'type' => 'image')
        );
    }
}

function custom_accordion_render_field($args) {
    $options = get_option('custom_accordion_options');
    $field = $args['field'];
    $value = isset($options[$field]) ? esc_attr($options[$field]) : '';

    echo "<div>";

    if (isset($args['type']) && $args['type'] === 'image') {
        echo "<input type='text' id='$field' name='custom_accordion_options[$field]' value='$value' class='image' />";
        echo "<button class='upload-image-button button'>Select Image</button>";
        echo "<div class='image-preview'>";
        if ($value) {
            echo "<img src='$value' alt='Image Preview'>";
        }
        echo "</div>";
    } else {
        echo "<textarea id='$field' name='custom_accordion_options[$field]'>$value</textarea>";
    }

    echo "</div>";
}


add_action('admin_menu', 'custom_accordion_options_page');
add_action('admin_init', 'custom_accordion_register_options');

function custom_accordion_shortcode($atts, $content = null) {
    $options = get_option('custom_accordion_options');

    if (!$options) {
        return '<p>No options found. Please check if options are being saved on the "Accordion Options" page.</p>';
    }

    // Output HTML for the accordions
    ob_start();
    ?>
    <div class="custom-accordion">
        <?php for ($i = 1; $i <= 5; $i++) : ?>
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="accordion-title"><?php echo esc_html($options['item' . $i . '_title']); ?></h3>
                    <span class="accordion-toggle">+</span>
                </div>
                <div class="accordion-content">
                    <?php if (isset($options['item' . $i . '_image']) && $options['item' . $i . '_image']) : ?>
                        <div class="image-preview">
                            <img src="<?php echo esc_url($options['item' . $i . '_image']); ?>" alt="Image Preview">
                        </div>
                    <?php endif; ?>
                    <p><?php echo isset($options['item' . $i . '_description']) ? wp_kses_post($options['item' . $i . '_description']) : ''; ?></p>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode('custom_accordion', 'custom_accordion_shortcode');
