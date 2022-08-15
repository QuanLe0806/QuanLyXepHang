<?php

namespace GSWPTS\includes\classes\controller\ajax;

defined('ABSPATH') || wp_die(__('You can\'t access this page', 'sheetstowptable'));

class FetchProducts {

    /**
     * @var array
     */
    private static $output = [];

    public function fetch_products() {
        if (sanitize_text_field($_POST['action']) != 'gswpts_product_fetch') {
            self::$output['response_type'] = 'invalid_action';
            self::$output['output'] = '<b>' . esc_html__('Action is invalid', 'sheetstowptable') . '</b>';
            echo json_encode(self::$output);
            wp_die();
        }

        echo self::get_other_products();

        wp_die();
    }

    public static function get_other_products() {

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        remove_all_filters('plugins_api');

        $plugins_allowedtags = array(
            'a'       => array(
                'href'   => array(),
                'title'  => array(),
                'target' => array()
            ),
            'abbr'    => array('title' => array()),
            'acronym' => array('title' => array()),
            'code'    => array(),
            'pre'     => array(),
            'em'      => array(),
            'strong'  => array(),
            'ul'      => array(),
            'ol'      => array(),
            'li'      => array(),
            'p'       => array(),
            'br'      => array()
        );

        $recommended_plugins = array();

        /* WP Dark Mode Plugin */
        $args = [
            'slug'   => 'wp-dark-mode',
            'fields' => [
                'short_description' => true,
                'icons'             => true,
                'reviews'           => false // excludes all reviews
            ]
        ];
        $data = plugins_api('plugin_information', $args);
        if ($data && !is_wp_error($data)) {
            $recommended_plugins['wp-dark-mode'] = $data;
            $recommended_plugins['wp-dark-mode']->name = 'WP Dark Mode';
            $recommended_plugins['wp-dark-mode']->short_description = 'Help your website visitors spend more time and an eye-pleasing reading experience. Personal preference rules always king. WP Dark Mode can be a game-changer for your website.';
        }

        /* jitsi meet Plugin */
        $args = [
            'slug'   => 'webinar-and-video-conference-with-jitsi-meet',
            'fields' => [
                'short_description' => true,
                'icons'             => true,
                'reviews'           => false // excludes all reviews
            ]
        ];
        $data = plugins_api('plugin_information', $args);
        if ($data && !is_wp_error($data)) {
            $recommended_plugins['webinar-and-video-conference-with-jitsi-meet'] = $data;
            $recommended_plugins['webinar-and-video-conference-with-jitsi-meet']->name = 'Webinar and Video Conference with Jitsi Meet';
            $recommended_plugins['webinar-and-video-conference-with-jitsi-meet']->short_description = 'Webinar and Video Conference with Jitsi Meet.';
        }

        /* easy-video-reviews Plugin */
        $args = [
            'slug'   => 'easy-video-reviews',
            'fields' => [
                'short_description' => true,
                'icons'             => true,
                'reviews'           => false // excludes all reviews
            ]
        ];
        $data = plugins_api('plugin_information', $args);
        if ($data && !is_wp_error($data)) {
            $recommended_plugins['easy-video-reviews'] = $data;
            $recommended_plugins['easy-video-reviews']->name = 'Easy Video Reviews';
            $recommended_plugins['easy-video-reviews']->short_description = 'Easy Video Reviews is the best and easiest video review plugin for WordPress, fully compatible with WooCommerce and Easy Digital Downloads plugins.';
        }

        /* Chat Widgets for Multivendor Marketplaces Plugin */
        $args = [
            'slug'   => 'chat-widgets-for-multivendor-marketplaces',
            'fields' => [
                'short_description' => true,
                'icons'             => true,
                'reviews'           => false // excludes all reviews
            ]
        ];
        $data = plugins_api('plugin_information', $args);
        if ($data && !is_wp_error($data)) {
            $recommended_plugins['chat-widgets-for-multivendor-marketplaces'] = $data;
            $recommended_plugins['chat-widgets-for-multivendor-marketplaces']->name = 'Chat Widgets for Multivendor Marketplaces';
            $recommended_plugins['chat-widgets-for-multivendor-marketplaces']->short_description = 'Chat with your customers via their favorite channels. Show a chat button on the bottom…';
        }

        /* Flexiaddons Plugin */
        $args = [
            'slug'   => 'flexiaddons',
            'fields' => [
                'short_description' => true,
                'icons'             => true,
                'reviews'           => false // excludes all reviews
            ]
        ];

        $data = plugins_api('plugin_information', $args);
        if ($data && !is_wp_error($data)) {
            $recommended_plugins['flexiaddons'] = $data;
            $recommended_plugins['flexiaddons']->name = 'Flexi Addons for Elementor';
            $recommended_plugins['flexiaddons']->short_description = 'A collection of premium quality & highly customizable addons or modules for use in Elementor page builder.';
        }

        ?>

<?php
foreach ((array) $recommended_plugins as $plugin) {
            if (is_object($plugin)) {
                $plugin = (array) $plugin;
            }

            // Display the group heading if there is one.
            if (isset($plugin['group']) && $plugin['group'] != $group) {

                $group_name = $plugin['group'];

                // Starting a new group, close off the divs of the last one.
                if (!empty($group)) {
                    echo '</div>';
                }

                echo '<div class="plugin-group"><h3>' . esc_html($group_name) . '</h3>';
                // Needs an extra wrapping div for nth-child selectors to work.
                echo '<div class="plugin-items">';

                $group = $plugin['group'];
            }
            $title = wp_kses($plugin['name'], $plugins_allowedtags);

            // Remove any HTML from the description.
            $description = strip_tags($plugin['short_description']);
            $version = wp_kses($plugin['version'], $plugins_allowedtags);

            $name = strip_tags($title . ' ' . $version);

            $author = wp_kses($plugin['author'], $plugins_allowedtags);
            if (!empty($author)) {
                /* translators: %s: Plugin author. */
                $author = ' <cite>' . sprintf(__('By %s'), $author) . '</cite>';
            }

            $requires_php = isset($plugin['requires_php']) ? $plugin['requires_php'] : null;
            $requires_wp = isset($plugin['requires']) ? $plugin['requires'] : null;

            $compatible_php = is_php_version_compatible($requires_php);
            $compatible_wp = is_wp_version_compatible($requires_wp);
            $tested_wp = (empty($plugin['tested']) || version_compare(get_bloginfo('version'), $plugin['tested'], '<='));

            $action_links = array();

            if (current_user_can('install_plugins') || current_user_can('update_plugins')) {
                $status = install_plugin_install_status($plugin);

                switch ($status['status']) {
                case 'install':
                    if ($status['url']) {
                        if ($compatible_php && $compatible_wp) {
                            $action_links[] = sprintf(
                                '<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
                                esc_attr($plugin['slug']),
                                esc_url($status['url']),
                                /* translators: %s: Plugin name and version. */
                                esc_attr(sprintf(_x('Install %s now', 'plugin'), $name)),
                                esc_attr($name),
                                __('Install Now')
                            );
                        } else {
                            $action_links[] = sprintf(
                                '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                _x('Cannot Install', 'plugin')
                            );
                        }
                    }
                    break;

                case 'update_available':
                    if ($status['url']) {
                        if ($compatible_php && $compatible_wp) {
                            $action_links[] = sprintf(
                                '<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
                                esc_attr($status['file']),
                                esc_attr($plugin['slug']),
                                esc_url($status['url']),
                                /* translators: %s: Plugin name and version. */
                                esc_attr(sprintf(_x('Update %s now', 'plugin'), $name)),
                                esc_attr($name),
                                __('Update Now')
                            );
                        } else {
                            $action_links[] = sprintf(
                                '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                _x('Cannot Update', 'plugin')
                            );
                        }
                    }
                    break;

                case 'latest_installed':
                case 'newer_installed':
                    if (is_plugin_active($status['file'])) {
                        $action_links[] = sprintf(
                            '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                            _x('Active', 'plugin')
                        );
                    } elseif (current_user_can('activate_plugin', $status['file'])) {
                        $button_text = __('Activate');
                        /* translators: %s: Plugin name. */
                        $button_label = _x('Activate %s', 'plugin');
                        $activate_url = add_query_arg(
                            array(
                                '_wpnonce' => wp_create_nonce('activate-plugin_' . $status['file']),
                                'action'   => 'activate',
                                'plugin'   => $status['file']
                            ),
                            network_admin_url('plugins.php')
                        );

                        if (is_network_admin()) {
                            $button_text = __('Network Activate');
                            /* translators: %s: Plugin name. */
                            $button_label = _x('Network Activate %s', 'plugin');
                            $activate_url = add_query_arg(array('networkwide' => 1), $activate_url);
                        }

                        $action_links[] = sprintf(
                            '<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
                            esc_url($activate_url),
                            esc_attr(sprintf($button_label, $plugin['name'])),
                            $button_text
                        );
                    } else {
                        $action_links[] = sprintf(
                            '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                            _x('Installed', 'plugin')
                        );
                    }
                    break;
                }
            }

            $details_link = self_admin_url(
                'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] .
                '&amp;TB_iframe=true&amp;width=600&amp;height=550'
            );

            $action_links[] = sprintf(
                '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
                esc_url($details_link),
                /* translators: %s: Plugin name and version. */
                esc_attr(sprintf(__('More information about %s'), $name)),
                esc_attr($name),
                __('More Details')
            );

            if (!empty($plugin['icons']['svg'])) {
                $plugin_icon_url = $plugin['icons']['svg'];
            } elseif (!empty($plugin['icons']['2x'])) {
                $plugin_icon_url = $plugin['icons']['2x'];
            } elseif (!empty($plugin['icons']['1x'])) {
                $plugin_icon_url = $plugin['icons']['1x'];
            } else {
                $plugin_icon_url = $plugin['icons']['default'];
            }

            /**
             * Filters the install action links for a plugin.
             *
             * @param string[] $action_links An array of plugin action links. Defaults are links to Details and Install Now.
             * @param array    $plugin       The plugin currently being listed.
             */
            $action_links = apply_filters('plugin_install_action_links', $action_links, $plugin);

            $last_updated_timestamp = strtotime($plugin['last_updated']);
            ?>
<div class="plugin-card plugin-card-<?php echo sanitize_html_class($plugin['slug']); ?>">
    <?php
if (!$compatible_php || !$compatible_wp) {
                echo '<div class="notice inline notice-error notice-alt"><p>';
                if (!$compatible_php && !$compatible_wp) {
                    _e('This plugin doesn&#8217;t work with your versions of WordPress and PHP.');
                    if (current_user_can('update_core') && current_user_can('update_php')) {
                        printf(
                            /* translators: 1: URL to WordPress Updates screen, 2: URL to Update PHP page. */
                            ' ' . __('<a href="%1$s">Please update WordPress</a>, and then <a href="%2$s">learn more about updating PHP</a>.'),
                            self_admin_url('update-core.php'),
                            esc_url(wp_get_update_php_url())
                        );
                        wp_update_php_annotation('</p><p><em>', '</em>');
                    } elseif (current_user_can('update_core')) {
                        printf(
                            /* translators: %s: URL to WordPress Updates screen. */
                            ' ' . __('<a href="%s">Please update WordPress</a>.'),
                            self_admin_url('update-core.php')
                        );
                    } elseif (current_user_can('update_php')) {
                        printf(
                            /* translators: %s: URL to Update PHP page. */
                            ' ' . __('<a href="%s">Learn more about updating PHP</a>.'),
                            esc_url(wp_get_update_php_url())
                        );
                        wp_update_php_annotation('</p><p><em>', '</em>');
                    }
                } elseif (!$compatible_wp) {
                    _e('This plugin doesn&#8217;t work with your version of WordPress.');
                    if (current_user_can('update_core')) {
                        printf(
                            /* translators: %s: URL to WordPress Updates screen. */
                            ' ' . __('<a href="%s">Please update WordPress</a>.'),
                            self_admin_url('update-core.php')
                        );
                    }
                } elseif (!$compatible_php) {
                    _e('This plugin doesn&#8217;t work with your version of PHP.');
                    if (current_user_can('update_php')) {
                        printf(
                            /* translators: %s: URL to Update PHP page. */
                            ' ' . __('<a href="%s">Learn more about updating PHP</a>.'),
                            esc_url(wp_get_update_php_url())
                        );
                        wp_update_php_annotation('</p><p><em>', '</em>');
                    }
                }
                echo '</p></div>';
            }
            ?>
    <div class="plugin-card-top">
        <div class="name column-name">
            <h3>
                <a href="<?php echo esc_url($details_link); ?>" class="thickbox open-plugin-details-modal">
                    <?php echo $title; ?>
                    <img src="<?php echo esc_attr($plugin_icon_url); ?>" class="plugin-icon" alt="" />
                </a>
            </h3>
        </div>
        <div class="action-links">
            <?php
if ($action_links) {
                echo '<ul class="plugin-action-buttons"><li>' . implode('</li><li>', $action_links) . '</li></ul>';
            }
            ?>
        </div>
        <div class="desc column-description">
            <p><?php echo $description; ?></p>
            <p class="authors"><?php echo $author; ?></p>
        </div>
    </div>
    <div class="plugin-card-bottom">
        <div class="vers column-rating">
            <?php
wp_star_rating(
                array(
                    'rating' => $plugin['rating'],
                    'type'   => 'percent',
                    'number' => $plugin['num_ratings']
                )
            );
            ?>
            <span class="num-ratings"
                aria-hidden="true">(<?php echo number_format_i18n($plugin['num_ratings']); ?>)</span>
        </div>
        <div class="column-updated">
            <strong><?php _e('Last Updated:');?></strong>
            <?php
/* translators: %s: Human-readable time difference. */
            printf(__('%s ago'), human_time_diff($last_updated_timestamp));
            ?>
        </div>
        <div class="column-downloaded">
            <?php
if ($plugin['active_installs'] >= 1000000) {
                $active_installs_millions = floor($plugin['active_installs'] / 1000000);
                $active_installs_text = sprintf(
                    /* translators: %s: Number of millions. */
                    _nx('%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations'),
                    number_format_i18n($active_installs_millions)
                );
            } elseif (0 == $plugin['active_installs']) {
                $active_installs_text = _x('Less Than 10', 'Active plugin installations');
            } else {
                $active_installs_text = number_format_i18n($plugin['active_installs']) . '+';
            }
            /* translators: %s: Number of installations. */
            printf(__('%s Active Installations'), $active_installs_text);
            ?>
        </div>
        <div class="column-compatibility">
            <?php
if (!$tested_wp) {
                echo '<span class="compatibility-untested">' . __('Untested with your version of WordPress') . '</span>';
            } elseif (!$compatible_wp) {
                echo '<span class="compatibility-incompatible">' . __('<strong>Incompatible</strong> with your version of WordPress') . '</span>';
            } else {
                echo '<span class="compatibility-compatible">' . __('<strong>Compatible</strong> with your version of WordPress') . '</span>';
            }
            ?>
        </div>
    </div>
</div>
<?php }?>


<?php
}
}