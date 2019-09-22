<?php

/**
 *  @package Webhook Netlify Deploy
 */
/*
Plugin Name: Webhook Netlify Deploy
Plugin URI: http://github.com/lukethacoder/wp-webhook-netlify-deploy
Description: Adds a Build Website button that sends a webhook request to build a netlify hosted website when clicked
Version: 1.1.3
Author: Luke Secomb
Author URI: https://lukesecomb.digital
License: GPLv3 or later
Text Domain: webhook-netlify-deploy
*/

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or die('You do not have access to this file, sorry mate');

class deployWebhook {

    /**
    * Constructor
    *
    * @since 1.0.0
    **/
    public function __construct() {

      // Stop crons on uninstall
      register_deactivation_hook(__FILE__,  array( $this, 'deactivate_scheduled_cron'));

    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

      // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
      add_action( 'admin_init', array( $this, 'setup_schedule_fields' ) );
      add_action( 'admin_init', array( $this, 'setup_developer_fields' ) );
      add_action( 'admin_footer', array( $this, 'run_the_mighty_javascript' ) );
      add_action( 'admin_bar_menu', array( $this, 'add_to_admin_bar' ), 90 );

      // Listen to cron scheduler option updates
      add_action('update_option_enable_scheduled_builds', array( $this, 'build_schedule_options_updated' ), 10, 3 );
      add_action('update_option_select_schedule_builds', array( $this, 'build_schedule_options_updated' ), 10, 3 );
      add_action('update_option_select_time_build', array( $this, 'build_schedule_options_updated' ), 10, 3 );

      // Trigger cron scheduler every WP load
      add_action('wp', array( $this, 'set_build_schedule_cron') );

      // Add custom schedules
      add_filter( 'cron_schedules', array( $this, 'custom_cron_intervals' ) );

      // Link event to function
      add_action('scheduled_netlify_build', array( $this, 'fire_netlify_webhook' ) );

    }

    /**
    * Main Plugin Page markup
    *
    * @since 1.0.0
    **/
    public function plugin_settings_page_content() {?>
    	<div class="wrap">
    		<h2><?php _e('Webhook Netlify Deploy', 'webhook-netlify-deploy');?></h2>
        <hr>
        <h3><?php _e('Build Website', 'webhook-netlify-deploy');?></h3>
        <button id="build_button" class="button button-primary" name="submit" type="submit">
          <?php _e('Build Site', 'webhook-netlify-deploy');?>
        </button>
        <br>
        <p id="build_status" style="font-size: 12px; margin: 0;"></p>
        <p style="font-size: 12px">*<?php _e('Do not abuse the Build Site button', 'webhook-netlify-deploy');?>*</p><br>
        <hr>
        <h3><?php _e('Deploy Status', 'webhook-netlify-deploy');?></h3>
        <button id="status_button" class="button button-primary" name="submit" type="submit" style="margin: 0 0 16px;">
          <?php _e('Get Deploys Status', 'webhook-netlify-deploy');?>
        </button>

        <div style="margin: 0 0 16px;">
            <a id="build_img_link" href="">
                <img id="build_img" src=""/>
            </a>
        </div>
        <div>
            <!-- <p id="deploy_status"></p> -->
            <p id="deploy_id"></p>
            <div style="display: flex;"><p id="deploy_finish_time"></p><p id="deploy_loading"></p></div>
            <p id="deploy_ssl_url"></p>
        </div>

        <div id="deploy_preview"></div>

        <hr>

        <h3><?php _e('Previous Builds', 'webhook-netlify-deploy');?></h3>
        <button id="previous_deploys" class="button button-primary" name="submit" type="submit" style="margin: 0 0 16px;">
          <?php _e('Get All Previous Deploys', 'webhook-netlify-deploy');?>
        </button>
        <ul id="previous_deploys_container" style="list-style: none;"></ul>
    	</div> <?php
    }

    /**
    * Schedule Builds (subpage) markup
    *
    * @since 1.1.2
    **/
    public function plugin_settings_schedule_content() {?>
    	<div class="wrap">
    		<h1><?php _e('Schedule Netlify Builds', 'webhook-netlify-deploy');?></h1>
    		<p><?php _e('This section allows regular Netlify builds to be scheduled.', 'webhook-netlify-deploy');?></p>
        <hr>

        <?php
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
              $this->admin_notice();
        } ?>

        <form method="POST" action="options.php">
                <?php
                    settings_fields( 'schedule_webhook_fields' );
                    do_settings_sections( 'schedule_webhook_fields' );
                    submit_button();
                ?>
        </form>
    	</div> <?php
    }

    /**
    * Developer Settings (subpage) markup
    *
    * @since 1.0.0
    **/
    public function plugin_settings_developer_content() {?>
    	<div class="wrap">
    		<h1><?php _e('Developer Settings', 'webhook-netlify-deploy');?></h1>
    		<p><?php _e('Do not change this if you dont know what you are doing.', 'webhook-netlify-deploy');?></p>
            <hr>

            <?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'developer_webhook_fields' );
                    do_settings_sections( 'developer_webhook_fields' );
                    submit_button();
                ?>
    		</form>

            <footer>
                <h3><?php _e('Extra Info', 'webhook-netlify-deploy');?></h3>
                <p><a href="https://github.com/lukethacoder/wp-webhook-netlify-deploy"><?php _e('Plugin Docs', 'webhook-netlify-deploy');?></a></p>
                <p><a href="https://github.com/lukethacoder/wp-webhook-netlify-deploy"><?php _e('Project Github', 'webhook-netlify-deploy');?></a></p>
            </footer>

    	</div> <?php
    }

    /**
    * The Mighty JavaScript
    *
    * @since 1.0.0
    **/
    public function run_the_mighty_javascript() {
        // TODO: split up javascript to allow to be dynamically imported as needed
        // $screen = get_current_screen();
        // if ( $screen && $screen->parent_base != 'developer_webhook_fields' && $screen->parent_base != 'deploy_webhook_fields_sub' ) {
        //     return;
        // }
        ?>
        <script type="text/javascript" >
        console.log('run_the_mighty_javascript');
        jQuery(document).ready(function($) {
            var _this = this;
            $( ".webhook-deploy_page_developer_webhook_fields td > input" ).css( "width", "100%");

            var webhook_url = '<?php echo(get_option('webhook_address')) ?>';
            var netlify_user_agent = '<?php echo(get_option('netlify_user_agent')) ?>';
            var netlify_api_key = '<?php echo(get_option('netlify_api_key'))?>'
            var netlify_site_id = '<?php echo(get_option('netlify_site_id')) ?>';

            var netlifySites = "https://api.netlify.com/api/v1/sites/";
            var req_url = netlifySites + netlify_site_id + '/deploys?access_token=' + netlify_api_key;

            function getDeployData() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    appendStatusData(data[0]);
                })
                .fail(function() {
                    console.error("error res => ", this)
                })
            }

            function getAllPreviousBuilds() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    var buildNo = 1;
                    data.forEach(function(item) {
                        var deploy_preview_url = '';
                        if (data.deploy_ssl_url) {
                            deploy_preview_url = data.deploy_ssl_url
                        } else {
                            deploy_preview_url = data.deploy_url
                        }
                        $('#previous_deploys_container').append(
                            '<li style="margin: 0 auto 16px"><hr><h3>No: ' + buildNo + ' - ' + item.name + '</h3><h4>Created at: ' +  new Date(item.created_at.toString()).toLocaleString() + '</h4><h4>' + item.title + '</h4><p>Id: ' + item.id + '</p><p>Deploy Time: ' + item.deploy_time + '</p><p>Branch: ' + item.branch + '</p><a href="' + item.deploy_preview_url + '">Preview Build</a></li>'
                        );
                        buildNo++;
                    })
                })
                .fail(function() {
                    console.error("error res => ", this)
                })
            }

            function runSecondFunc() {
                $.ajax({
                    type: "GET",
                    url: req_url
                }).done(function(data) {
                    $( "#build_img_link" ).attr("href", `${data.admin_url}`);
                    // $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                })
                .fail(function() {
                    console.error("error res => ", this)
                })

                // $( "#build_status" ).html('Deploy building');
            }

            function appendStatusData(data) {
                var d = new Date();
                var p = d.toLocaleString();
                var yo = new Date(data.created_at);
                var created = yo.toLocaleString();
                var current_state = data.state;

                if (data.state === 'ready') {
                    current_state = "Success"
                }

                if (data.state !== 'ready') {
                    $( "#deploy_finish_time" ).html( "Building Site" );
                    $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                    var dots = window.setInterval( function() {
                        var wait = document.getElementById('deploy_loading');
                        if ( wait.innerHTML.length >= 3 ) {
                            wait.innerHTML = "";
                        }
                        else {
                            wait.innerHTML += ".";
                        }
                    },
                    500);
                } else {
                    var deploy_preview_url = '';

                    if (data.deploy_ssl_url) {
                        deploy_preview_url = data.deploy_ssl_url
                    } else {
                        deploy_preview_url = data.deploy_url
                    }

                    $( "#deploy_id" ).html( "ID: " + data.id + "" );
                    $( "#deploy_finish_time" ).html( "Build Completed: " + created );
                    $( "#build_img" ).attr("src", `https://api.netlify.com/api/v1/badges/${ netlify_site_id }/deploy-status`);
                    $( "#deploy_ssl_url" ).html( "Deploy URL: <a href='" + deploy_preview_url + "'>" + data.deploy_ssl_url + "</a>");
                    $( "#deploy_preview" ).html( `<iframe style="width: 100%; min-height: 540px" id="frameLeft" src="${deploy_preview_url}"/>`)
                }


            }

            function netlifyDeploy() {
                return $.ajax({
                    type: "POST",
                    url: webhook_url,
                    dataType: "json",
                    header: {
                        "User-Agent": netlify_user_agent
                    }
                });
            }

            $("#status_button").on("click", function(e) {
                e.preventDefault();
                getDeployData();
            });

            $("#previous_deploys").on("click", function(e) {
                e.preventDefault();
                getAllPreviousBuilds();
            });

            $("#build_button").on("click", function(e) {

                // hide deploy
                $('#build_img_link').attr('href', '');
                $('#build_img').attr('src', '');
                $('#deploy_id').html('');
                $('#deploy_finish_time').html('');
                $('#deploy_ssl_url').html('');
                $('#deploy_preview').html('');

                e.preventDefault();

                netlifyDeploy().done(function() {
                    console.log("success")
                    getDeployData();
                    $( "#build_status" ).html('Deploy building');
                })
                .fail(function() {
                    console.error("error res => ", this)
                    $( "#build_status" ).html('There seems to be an error with the build', this);
                })
            });

            $(document).on('click', '#wp-admin-bar-netlify-deploy-button', function(e) {
                e.preventDefault();

                var $button = $(this),
                    $buttonContent = $button.find('.ab-item:first');

                if ($button.hasClass('deploying') || $button.hasClass('running')) {
                    return false;
                }

                $button.addClass('running').css('opacity', '0.5');

                netlifyDeploy().done(function() {
                    var $badge = $('#admin-bar-netlify-deploy-status-badge');

                    $button.removeClass('running');
                    $button.addClass('deploying');

                    $buttonContent.find('.ab-label').text('Deploying…');

                    if ($badge.length) {
                        if (!$badge.data('original-src')) {
                            $badge.data('original-src', $badge.attr('src'));
                        }

                        $badge.attr('src', $badge.data('original-src') + '?updated=' + Date.now());
                    }
                })
                .fail(function() {
                    $button.removeClass('running').css('opacity', '1');
                    $buttonContent.find('.dashicons-hammer')
                        .removeClass('dashicons-hammer').addClass('dashicons-warning');

                    console.error("error res => ", this)
                })
            });
        });
        </script> <?php
    }

    /**
    * Plugin Menu Items Setup
    *
    * @since 1.0.0
    **/
    public function create_plugin_settings_page() {
        $run_deploys = apply_filters( 'netlify_deploy_capability', 'manage_options' );
        $adjust_settings = apply_filters( 'netlify_adjust_settings_capability', 'manage_options' );

        if ( current_user_can( $run_deploys ) ) {
            $page_title = __('Deploy to Netlify', 'webhook-netlify-deploy');
            $menu_title = __('Webhook Deploy', 'webhook-netlify-deploy');
            $capability = $run_deploys;
            $slug = 'deploy_webhook_fields';
            $callback = array( $this, 'plugin_settings_page_content' );
            $icon = 'dashicons-admin-plugins';
            $position = 100;

            add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
        }

        if ( current_user_can( $adjust_settings ) ) {
            $sub_page_title = __('Schedule Builds', 'webhook-netlify-deploy');
            $sub_menu_title = __('Schedule Builds', 'webhook-netlify-deploy');
            $sub_capability = $adjust_settings;
            $sub_slug = 'schedule_webhook_fields';
            $sub_callback = array( $this, 'plugin_settings_schedule_content' );

            add_submenu_page( $slug, $sub_page_title, $sub_menu_title, $sub_capability, $sub_slug, $sub_callback );
        }

        if ( current_user_can( $adjust_settings ) ) {
            $sub_page_title = __('Developer Settings', 'webhook-netlify-deploy');
            $sub_menu_title = __('Developer Settings', 'webhook-netlify-deploy');
            $sub_capability = $adjust_settings;
            $sub_slug = 'developer_webhook_fields';
            $sub_callback = array( $this, 'plugin_settings_developer_content' );

            add_submenu_page( $slug, $sub_page_title, $sub_menu_title, $sub_capability, $sub_slug, $sub_callback );
        }


    }

    public function custom_cron_intervals($schedules) {
      // add a 'weekly' interval
      $schedules['weekly'] = array(
        'interval' => 604800,
        'display' => __('Once Weekly', 'webhook-netlify-deploy')
      );
      $schedules['monthly'] = array(
        'interval' => 2635200,
        'display' => __('Once a month', 'webhook-netlify-deploy')
      );

      return $schedules;
    }

    /**
    * Notify Admin on Successful Update
    *
    * @since 1.0.0
    **/
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Your settings have been updated!', 'webhook-netlify-deploy');?></p>
        </div>
    <?php
    }

    /**
    * Setup Sections
    *
    * @since 1.0.0
    **/
    public function setup_sections() {
        add_settings_section( 'schedule_section', __('Scheduling Settings', 'webhook-netlify-deploy'), array( $this, 'section_callback' ), 'schedule_webhook_fields' );
        add_settings_section( 'developer_section', __('Webhook Settings', 'webhook-netlify-deploy'), array( $this, 'section_callback' ), 'developer_webhook_fields' );
    }

    /**
    * Check it wont break on build and deploy
    *
    * @since 1.0.0
    **/
    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'developer_section':
    			echo __('The build and deploy status will not work without these fields entered corrently', 'webhook-netlify-deploy');
    			break;
    	}
    }

    /**
    * Fields used for schedule input data
    *
    * Based off this article:
    * @link https://www.smashingmagazine.com/2016/04/three-approaches-to-adding-configurable-fields-to-your-plugin/
    *
    * @since 1.1.0
    **/
    public function setup_schedule_fields() {
        $fields = array(
          array(
            'uid' => 'enable_scheduled_builds',
            'label' => __('Enable Scheduled Events', 'webhook-netlify-deploy'),
            'section' => 'schedule_section',
            'type' => 'checkbox',
            'options' => array(
              'enable' => __('Enable', 'webhook-netlify-deploy'),
              ),
            'default' =>  array()
          ),
          array(
            'uid' => 'select_time_build',
            'label' => __('Select Time to Build', 'webhook-netlify-deploy'),
            'section' => 'schedule_section',
            'type' => 'time',
            'default' => '00:00'
          ),
          array(
            'uid' => 'select_schedule_builds',
            'label' => __('Select Build Schedule', 'webhook-netlify-deploy'),
            'section' => 'schedule_section',
            'type' => 'select',
            'options' => array(
              'daily' => __('Daily', 'webhook-netlify-deploy'),
              'weekly' => __('Weekly', 'webhook-netlify-deploy'),
              'monthly' => __('Monthly', 'webhook-netlify-deploy'),
            ),
            'default' => array('week')
          )
        );
    	foreach( $fields as $field ){
        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'schedule_webhook_fields', $field['section'], $field );
            register_setting( 'schedule_webhook_fields', $field['uid'] );
    	}
    }

    /**
    * Fields used for developer input data
    *
    * @since 1.0.0
    **/
    public function setup_developer_fields() {
        $fields = array(
          array(
            'uid' => 'webhook_address',
            'label' => __('Webhook Build URL', 'webhook-netlify-deploy'),
            'section' => 'developer_section',
            'type' => 'text',
                'placeholder' => 'https://',
                'default' => '',
            ),
            array(
            'uid' => 'netlify_site_id',
            'label' => __('Netlify site_id', 'webhook-netlify-deploy'),
            'section' => 'developer_section',
            'type' => 'text',
                'placeholder' => 'e.g. 5b8e927e-82e1-4786-4770-a9a8321yes43',
                'default' => '',
            ),
            array(
            'uid' => 'netlify_api_key',
            'label' => __('Netlify API Key', 'webhook-netlify-deploy'),
            'section' => 'developer_section',
            'type' => 'text',
                'placeholder' => __('GET O-AUTH TOKEN', 'webhook-netlify-deploy'),
                'default' => '',
          ),
            array(
            'uid' => 'netlify_user_agent',
            'label' => __('User-Agent Site Value', 'webhook-netlify-deploy'),
            'section' => 'developer_section',
            'type' => 'text',
                'placeholder' => 'Website Name (and-website-url.netlify.com)',
                'default' => '',
          )
        );
      foreach( $fields as $field ){
          add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'developer_webhook_fields', $field['section'], $field );
            register_setting( 'developer_webhook_fields', $field['uid'] );
      }
    }

    /**
    * Field callback for handling multiple field types
    *
    * @since 1.0.0
    * @param $arguments
    **/
    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] );

        if ( !$value ) {
            $value = $arguments['default'];
        }

        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'time':
              printf( '<input name="%1$s" id="%1$s" type="time" value="%2$s" />', $arguments['uid'], $value );
              break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( count($value) > 0 ? $value[ array_search( $key, $value, true ) ] : false, $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }
    }

    /**
    * Add Deploy Button and Deployment Status to admin bar
    *
    * @since 1.1.0
    **/
    public function add_to_admin_bar( $admin_bar ) {

        $see_deploy_status = apply_filters( 'netlify_status_capability', 'manage_options' );
        $run_deploys = apply_filters( 'netlify_deploy_capability', 'manage_options' );

        if ( current_user_can( $run_deploys ) ) {
            $webhook_address = get_option( 'webhook_address' );

            if ( $webhook_address ) {
                $button = array(
                    'id' => 'netlify-deploy-button',
                    'title' => '<div style="cursor: pointer;"><span class="ab-icon dashicons dashicons-hammer"></span> <span class="ab-label">'. __('Deploy Site', 'webhook-netlify-deploy') .'</span></div>'
                );

                $admin_bar->add_node( $button );
            }
        }

        if ( current_user_can( $see_deploy_status ) ) {
            $netlify_site_id = get_option( 'netlify_site_id' );

            if ( $netlify_site_id ) {
                $badge = array(
                    'id' => 'netlify-deploy-status-badge',
                    'title' => sprintf( '<div style="display: flex; height: 100%%; align-items: center;">
                            <img id="admin-bar-netlify-deploy-status-badge" src="https://api.netlify.com/api/v1/badges/%s/deploy-status" alt="'. __('Netlify deply status', 'webhook-netlify-deploy') .'" style="width: auto; height: 16px;" />
                        </div>', $netlify_site_id )
                );

                $admin_bar->add_node( $badge );
            }
        }

    }

    /**
    *
    * Manage the cron jobs for triggering builds
    *
    * Check if scheduled builds have been enabled, and pass to
    * the enable function. Or disable.
    *
    * @since 1.1.2
    **/
    public function build_schedule_options_updated() {
      $enable_builds = get_option( 'enable_scheduled_builds' );
      if( $enable_builds ){
        // Clean any previous setting
        $this->deactivate_scheduled_cron();
        // Reset schedule
        $this->set_build_schedule_cron();
      } else {
        $this->deactivate_scheduled_cron();
      }
    }

    /**
    *
    * Activate cron job to trigger build
    *
    * @since 1.1.2
    **/
    public function set_build_schedule_cron() {
      $enable_builds = get_option( 'enable_scheduled_builds' );
      if( $enable_builds ){
        if( !wp_next_scheduled('scheduled_netlify_build') ) {
          $schedule = get_option( 'select_schedule_builds' );
          $set_time = get_option( 'select_time_build' );
          $timestamp = strtotime( $set_time );
          wp_schedule_event( $timestamp , $schedule[0], 'scheduled_netlify_build' );
        }
      } else {
        $this->deactivate_scheduled_cron();
      }
    }

    /**
    *
    * Remove cron jobs set by this plugin
    *
    * @since 1.1.2
    **/
    public function deactivate_scheduled_cron(){
      // find out when the last event was scheduled
    	$timestamp = wp_next_scheduled('scheduled_netlify_build');
    	// unschedule previous event if any
    	wp_unschedule_event($timestamp, 'scheduled_netlify_build');
    }

    /**
    *
    * Trigger Netlify Build
    *
    * @since 1.1.2
    **/
    public function fire_netlify_webhook(){
      $netlify_user_agent = get_option('netlify_user_agent');
      $webhook_url = get_option('webhook_address');
      if($netlify_user_agent && $webhook_url){
        $options = array(
          'method'  => 'POST',
          'header'  => array(
            "User-Agent" => $netlify_user_agent,
          )
        );
        return wp_remote_post($webhook_url, $options);
      }
      return false;
    }

}

new deployWebhook;
?>
