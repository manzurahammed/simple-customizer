<?php 
/*
Plugin Name: Simple Customizer Plugin
Plugin URI: https://codepassenger.com/wp/plugins
Description: Simple to add option in customizer
Author: manzur
Text Domain: simple
Domain Path: /languages/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Version: 1.0
*/
class Simple_customizer {
	
	public $customizeObj;
	public $data;
	public $panelId;
	public function __construct($data){
		$this->data = $data;
		add_action('customize_register',array($this,'movieshow_customize_register'));
		add_action('admin_enqueue_scripts', array($this,'custom_admin_style'));
	}
	
	function custom_admin_style($hook) {
		if ( 'customize.php' == $hook || 'widgets.php' == $hook ) {
			wp_enqueue_style( 'simple-css', get_template_directory_uri() . '/inc/css/simple-customizer.css',array());
			wp_enqueue_script( 'simple-js', get_template_directory_uri() . '/inc/js/simple-customizer.js', array( 'jquery' ), '20150611', true );
		}
	}	
	public function movieshow_customize_register($customizeObj){
		
		$this->customizeObj = $customizeObj;
		$this->create_panal();
		$this->create_section();
	}
	
	public function create_panal(){
		if(is_array($this->data) && !empty($this->data)){
			$this->panelId = $this->data['panel']['id'];
			$args = array(
				'priority'       => $this->data['panel']['priority'],
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          =>$this->data['panel']['name'] 
			);
			$this->customizeObj->add_panel($this->panelId,$args);
		}
	}
	
	public function create_section(){
		if(isset($this->data['panel']['section']) && !empty($this->data['panel']['section'])){
			$section = $this->data['panel']['section'];
			foreach($section as $key => $value){
				$args = array(
					'title'      => $value['name'],
					'priority'   => (isset($value['priority']))?$value['priority']:10,
					'panel'   => $this->panelId,
				);
				$this->customizeObj->add_section( $key , $args );
				if(!empty($value['fields'])){
					$this->create_field($value['fields'],$key);
				}
			}
		}
	}
	
	public function create_field($field,$id){
		if(is_array($field)){
			foreach($field as $key =>$item){
				$this->create_setting($item);
				$this->create_control($item,$id);
			}
		}
		
	}
	
	public function create_setting($args){
		$ar = array(
			'default'     => (isset($args['default']))?$args['default']:'',
			'transport'   => (isset($args['transport']))?$args['transport']:'postMessage'
		);
		if($args['type']=='switch'){
			$ar['sanitize_callback'] = array($this,'sanitize_integer');
		}
		$this->customizeObj->add_setting( $args['id'],$ar);
	}
	
	public function create_control($value,$key){
		$id = $value['id'];
		$args = array(
			'label'          => $value['name'],
			'section'        => $key,
			'settings'       => $id,
			
		);
		$inst = '';
		switch($value['type']){
			case "checkbox":
			case "select":
			case "radio":
				
				if(isset($value['choices'])){
					$args['type'] = $value['type'];
					$args['choices'] = (is_array($value['choices']))?$value['choices']:array('Select Option');
					$inst = new WP_Customize_Control($this->customizeObj,$id,$args);
				}
				break;
			case "switch":
				$args['type'] = $value['type'];
				$inst = new simple_Customize_Switch_Control($this->customizeObj,$id,$args);
				break;
			case "image":
				$inst = new WP_Customize_Image_Control($this->customizeObj,$id,$args);
				break;
			default:
				$args['type'] = $value['type'];
				$inst = new WP_Customize_Control($this->customizeObj,$id,$args);
		}
		$this->customizeObj->add_control($inst);
	}
	
	public function sanitize_integer($input) {
		return intval( $input );
	}
	public static function simple_customizer_hook(){
		$data = apply_filters( 'simple_customizer_data', array());
		if(!empty($data) && is_array($data)){
			new self($data);
		}
	}
}
add_action('init','Simple_customizer::simple_customizer_hook');


if(class_exists( 'WP_Customize_control')):
	class simple_Customize_Switch_Control extends WP_Customize_Control {
		public $type = 'switch';
		public function render_content() {
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<div class="switch_options">
					<span class="switch_enable"><?php esc_html_e('Yes','simple'); ?></span>
					<span class="switch_disable"><?php esc_html_e('No','simple'); ?></span>  
					<input type="hidden" id="switch_yes_no" <?php $this->link(); ?> value="<?php print $this->value(); ?>" />
				</div>
			</label>
			<?php
		}
	}
endif;

