<?php
/**
 * Plugin Name: Smart Video Gallery
 * Description: Infinite looping video gallery with custom controls and SVG navigation.
 * Version: 1.3.0
 * Author: Adnan Habib
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Smart_Video_Gallery {

    const OPTION_KEY = 'svgallery_items';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_shortcode( 'smart_video_gallery', [ $this, 'render_shortcode' ] );
    }

    /* ================= ADMIN ================= */

    public function add_settings_page() {
        add_options_page(
            'Smart Video Gallery',
            'Smart Video Gallery',
            'manage_options',
            'smart-video-gallery',
            [ $this, 'settings_page_html' ]
        );
    }

    public function register_settings() {
        register_setting(
            'smart_video_gallery_group',
            self::OPTION_KEY,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_items' ],
                'default'           => [],
            ]
        );
    }

    public function sanitize_items( $items ) {
        $clean = [];
        if ( is_array( $items ) ) {
            foreach ( $items as $item ) {
                if ( empty( $item['video'] ) && empty( $item['poster'] ) ) {
                    continue;
                }
                $clean[] = [
                    'video'  => esc_url_raw( $item['video'] ?? '' ),
                    'poster' => esc_url_raw( $item['poster'] ?? '' ),
                ];
            }
        }
        return $clean;
    }

    public function settings_page_html() {
        $items = get_option( self::OPTION_KEY, [] );
        ?>
        <div class="wrap">
            <h1>Smart Video Gallery</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'smart_video_gallery_group' ); ?>

                <table class="form-table" id="svgallery-table">
                    <thead>
                        <tr>
                            <th>Video</th>
                            <th>Preview image</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $items as $i => $item ) : ?>
                            <tr>
                                <td>
                                    <input type="text" class="regular-text svgallery-video-field"
                                        name="<?php echo self::OPTION_KEY; ?>[<?php echo $i; ?>][video]"
                                        value="<?php echo esc_attr( $item['video'] ); ?>">
                                    <button type="button" class="button svgallery-select-video">Select video</button>
                                </td>
                                <td>
                                    <input type="text" class="regular-text svgallery-poster-field"
                                        name="<?php echo self::OPTION_KEY; ?>[<?php echo $i; ?>][poster]"
                                        value="<?php echo esc_attr( $item['poster'] ); ?>">
                                    <button type="button" class="button svgallery-select-poster">Select image</button>
                                </td>
                                <td>
                                    <button type="button" class="button svgallery-remove-row">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- TEMPLATE ROW -->
                        <tr class="svgallery-row-template" style="display:none;">
                            <td>
                                <input type="text" class="regular-text svgallery-video-field"
                                    name="<?php echo self::OPTION_KEY; ?>[INDEX][video]">
                                <button type="button" class="button svgallery-select-video">Select video</button>
                            </td>
                            <td>
                                <input type="text" class="regular-text svgallery-poster-field"
                                    name="<?php echo self::OPTION_KEY; ?>[INDEX][poster]">
                                <button type="button" class="button svgallery-select-poster">Select image</button>
                            </td>
                            <td>
                                <button type="button" class="button svgallery-remove-row">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    <button type="button" class="button button-secondary" id="svgallery-add-row">
                        + Add new video
                    </button>
                </p>

                <?php submit_button(); ?>
            </form>

            <p><strong>Shortcode:</strong> <code>[smart_video_gallery]</code></p>
        </div>
        <?php
    }

    public function admin_assets( $hook ) {
        if ( $hook !== 'settings_page_smart-video-gallery' ) return;

        wp_enqueue_media();
        wp_enqueue_script(
            'svgallery-admin',
            plugins_url( 'assets/js/smart-video-gallery-admin.js', __FILE__ ),
            [ 'jquery' ],
            '1.3.0',
            true
        );
    }

    /* ================= FRONTEND ================= */

    public function enqueue_assets() {
        if ( is_admin() ) return;

        wp_enqueue_style(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11'
        );

        wp_enqueue_script(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11',
            true
        );

        wp_enqueue_style(
            'svgallery-style',
            plugins_url( 'assets/css/smart-video-gallery.css', __FILE__ ),
            [],
            '1.3.0'
        );

        wp_enqueue_script(
            'svgallery-script',
            plugins_url( 'assets/js/smart-video-gallery.js', __FILE__ ),
            [ 'swiper' ],
            '1.3.0',
            true
        );
    }

    public function render_shortcode() {
        $items = get_option( self::OPTION_KEY, [] );
        if ( empty( $items ) ) return '';

        ob_start(); ?>
        <div class="svgallery-wrapper">

            <!-- PREV -->
            <button class="svgallery-arrow svgallery-arrow-prev" aria-label="Previous">
                <img src="<?php echo plugins_url( 'assets/svg/arrow-left.svg', __FILE__ ); ?>" alt="">
            </button>

            <div class="svgallery-slider swiper">
                <div class="swiper-wrapper">
                    <?php foreach ( $items as $item ) :
                        if ( empty( $item['video'] ) ) continue;
                    ?>
                        <div class="swiper-slide">
                            <div class="play-thumb">
                                <video class="svgallery-video"
                                    src="<?php echo esc_url( $item['video'] ); ?>"
                                    poster="<?php echo esc_url( $item['poster'] ); ?>"
                                    preload="metadata"
                                    playsinline
                                    webkit-playsinline></video>

                                <div class="svgallery-controls">
                                    <button class="svgallery-btn svgallery-mute">🔊</button>
                                    <button class="svgallery-btn svgallery-fullscreen">⛶</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- NEXT -->
            <button class="svgallery-arrow svgallery-arrow-next" aria-label="Next">
                <img src="<?php echo plugins_url( 'assets/svg/arrow-right.svg', __FILE__ ); ?>" alt="">
            </button>

        </div>
        <?php
        return ob_get_clean();
    }
}

new Smart_Video_Gallery();
