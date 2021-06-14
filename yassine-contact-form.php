<?php
/*
Plugin Name: yassine-contact-form
Plugin URI: https://yassine-contact-form.com/
Description: yassine-contact-form for babylon
Version: 1.0.0
Author: yassine bouzae
Author URI: https://yassine-contact-form.com/
Text Domain: yassine-contact-form
*/

use MailPoetVendor\Symfony\Component\Validator\Constraints\Callback;

if (!defined('ABSPATH')) {
    exit;
}
class YcontactForm
{
    public function __construct()
    {
        add_action('init',array($this,'create_custom_post_type'));

        add_action('wp_enqueue_scripts',array($this,'load_assets'));

        add_shortcode('yassine_contact_form',array($this,'load_shortcode'));

        add_action('wp-footer',array($this,'load_scripts'));

        add_action('rest_api_init',array($this,'register_rest_api'));

    }
    public function create_custom_post_type()
    {
        
        $args = array(

            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'YContact Form',
                'singular_name'=> 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-buddicons-pm',

        );
        register_post_type('yassine-contact-form', $args);

    }
    public function load_assets()
    {
        wp_enqueue_style(
            'yassine-contact-form',
            plugin_dir_url( __FILE__ ) .'css/style.css',
            array(),
            1,
            'all'
        );
        wp_enqueue_script(
            'yassine-contact-form',
            plugin_dir_url( __FILE__ ) .'js/yassine-contact-form.js',
            array('jquery'),
            1,
            true
        );
    }
    public function load_shortcode()
    {
        include_once('forms.php')
        ;?>
        <script  type="text/javascript">
            var nonce = '<?php echo wp_create_nonce("wp_rest"); ?>'
            (function($){
                $("#yassine-contact-form").submit(function(event){
                    event.preventDefault();
                    alert('submet')
                    var form = $(this).serialize();
                    console.log(form);
                    $.ajax({
                        method:'POST',
                        url:"<?php echo get_rest_url(null,'yassine-contact-form/v1/send-email'); ?>",
                        Headers:{ 'X-XP-Nonce' : nonce },
                        data:form
                    })
                });
            })(jQuery)
        </script>
       <?php
    }
    public function load_scripts()
    {?>
    
    <?php
    }
    public function register_rest_api()
    {
        register_rest_route(
            'yassine-contact-form/v1','send-email',array(
                'methods' =>'POST',
                'callback' => array($this, 'handle_contact_form')
            )
            );
    }

    public function handle_contact_form($data)
    {
        $headers = $data->get_headers();
        $params = $data->get_params();
        $nonce = $headers['x_wp_nonce'][0];
        if (!wp_verify_nonce($nonce, 'wp-rest')) {
            return new WP_REST_Response('message not sent' , 422) ;  
        }
        $post_id = wp_insert_post([
            
            'post_type' => 'yassine-contact-form',
            'post_title' => 'Contact enquiry',
            'post_dtatus' => 'publish'

        ]);
        if ($post_id) {
            return new WP_REST_Request('thanks for your mesaage',200);
        }
    }

    
};

new YcontactForm;