<?php
use Elementor\Controls_Manager;
// use ElementorAiContent\OpenAi\OpenAi;
if(!class_exists('AI_Controllers')){
	class AI_Controllers{
		private static $ai_controller_instance=null;
		private static $prompt_data;
		private static $modal_data;
		private static $prompt_arr=[];
		private static $modal_arr=[];
		public static function ai_controller_instance(){
			if(self::$ai_controller_instance == null){
				self::$ai_controller_instance=new self();
			}
			return self::$ai_controller_instance;
		}
		public function __construct()
		{
			self::$prompt_data=get_option('AACGFE_prompt_data');
			$get_settings = get_option('auto_content_generator');
            $open_ai_key = !empty($get_settings['aacgfe-secret-key'])?sanitize_text_field($get_settings['aacgfe-secret-key']):'';
			add_action( 'elementor/element/text-editor/section_editor/before_section_end',array($this,'register_ai_controller'), 10, 2 );
			if(!empty($open_ai_key)){
				add_action( 'elementor/preview/enqueue_scripts',array($this,'preview_editor_scripts') );
				add_action( 'elementor/preview/enqueue_styles',array($this,'preview_editor_style') );
				add_action( 'wp_ajax_aacgfe_generate_content', array($this, 'aacgfe_generate_content' ) );
			} 
		}
		public function aacgfe_generate_content() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			check_ajax_referer('aacgfe-assistant');
			require_once AACGFE_PATH . 'open-ai/OpenAi.php';
			$output = $prompt = $keywords = '';
            $get_settings = get_option('auto_content_generator');
			$open_ai_key = !empty($get_settings['aacgfe-secret-key'])?sanitize_text_field($get_settings['aacgfe-secret-key']):'';
			$content = !empty($_POST['content'])?sanitize_text_field( $_POST['content'] ):'';
			if (empty( $content ) ) {
				$output = __( 'You have not provided the topic for the content that you want to generate!', 'aacg' );
				$responsedata = array("error"=>$output,"success"=>null);
				echo json_encode($responsedata);
				wp_die();
			}
			$prompt_txt = !empty($_POST['prompt_txt'])?sanitize_text_field($_POST['prompt_txt']):'Write a paragraph on this';
			$dummy_prompt = $prompt_txt.": '".$content."' to '"."en'";
            $aacgfe_temp = isset($get_settings['aacgfe-temp'])?sanitize_text_field($get_settings['aacgfe-temp']):0.3;
            $aacgfe_max_tok = isset($get_settings['aacgfe-max-tok'])?sanitize_text_field($get_settings['aacgfe-max-tok']):100;
            $aacgfe_prepenalty = isset($get_settings['aacgfe-prepenalty'])?sanitize_text_field($get_settings['aacgfe-prepenalty']):0;
            $aacgfe_frequency = isset($get_settings['aacgfe-frequency'])?sanitize_text_field($get_settings['aacgfe-frequency']):0;
			// Set the OpenAI API endpoint and parameters
			$openai_endpoint = 'https://api.openai.com/v1/completions';
			$openai_params =   array(
				'model'             => 'text-davinci-003',
				'prompt'            => $dummy_prompt,
				'temperature'       => (float)$aacgfe_temp,
				'max_tokens'        => (float)$aacgfe_max_tok,
				"top_p"=> 1,
				'frequency_penalty' => (float)$aacgfe_prepenalty,
				'presence_penalty'  => (float)$aacgfe_frequency,
			);
 			$complete = new OpenAi();
 			$complete = $complete->sendRequest($open_ai_key,$openai_params);
			
			$tokens=array(
				'prompt'=> isset($complete->usage->prompt_tokens)?(int)$complete->usage->prompt_tokens:'',
				'compeletion'=>isset($complete->usage->completion_tokens)?(int)$complete->usage->completion_tokens:'',
				'total'=>isset($complete->usage->total_tokens)?(int)$complete->usage->total_tokens:'',
			);
			$success_data = isset($complete->choices[0]->text)?sanitize_text_field($complete->choices[0]->text):null;
			$error_msg = !empty($complete->error->message)?sanitize_text_field($complete->error->message):null;
			$responsedata = array("error"=>$error_msg, "success"=>$success_data,'tokens'=>$tokens);
			$tokens=array(
				 'prompt'=> isset($complete->usage->prompt_tokens)?(int)$complete->usage->prompt_tokens:'',
	 			'compeletion'=>isset($complete->usage->completion_tokens)?(int)$complete->usage->completion_tokens:'',
	 			'total'=>isset($complete->usage->total_tokens)?(int)$complete->usage->total_tokens:'',
 			);
 			$success_data = isset($complete->choices[0]->text)?sanitize_text_field($complete->choices[0]->text):null;
 			$error_msg = !empty($complete->error->message)?sanitize_text_field($complete->error->message):null;
 			$responsedata = array("error"=>$error_msg, "success"=>$success_data,'tokens'=>$tokens);
 			echo json_encode($responsedata);
 			wp_die();
		}
		public function preview_editor_scripts() {
			wp_enqueue_script( 'ai-editor-scripts', AACGFE_URL. 'assets/js/aacgfe-content-generator.js', array( 'jquery', 'wp-i18n' ),AACGFE_VERSION, true );
			wp_enqueue_script( 'ai-sweetalert-editor-script', AACGFE_URL. 'assets/js/sweetalert2/sweetalert2.all.min.js', array(), AACGFE_VERSION, false );
			$ajax_params = array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'aacgfe_nonce' => wp_create_nonce( 'aacgfe-assistant' ),
			);
			wp_localize_script( 'ai-editor-scripts', 'ai_ajax_object', $ajax_params );
		}
		public function preview_editor_style() {
			
			wp_enqueue_style( 'ai-popup-editor-css', AACGFE_URL . 'assets/css/aacgfe-modal.css', array(), AACGFE_VERSION, 'all' );
			wp_enqueue_style( 'ai-sweetalert-editor-css',AACGFE_URL .  'assets/js/sweetalert2/sweetalert2.min.css', array(), AACGFE_VERSION, 'all' );
			
		}
		public static function register_ai_controller($element, $args){
			$get_settings = get_option('auto_content_generator');
            $open_ai_key = !empty($get_settings['aacgfe-secret-key'])?sanitize_text_field($get_settings['aacgfe-secret-key']):'';
			if(empty($open_ai_key)){
				$admin_url = esc_url(get_admin_url() .'admin.php?page=auto_content_generator');
				$element->add_control(
				'accg_auto_conentff',
				[
					'label' => 'Please Enter<a href='.esc_url($admin_url).' target="_blank"> <strong>OpenAI API Secret Key</strong> </a> to use AI Content Generator. ', 
					'type' => Controls_Manager::HEADING,
					'separator' => 'before', 
				]
			);
			}else{
				self::$prompt_data=self::get_prompt_data(false,true);
				$element->add_control(
					'accg_auto_conent',
					[
						'label' => 'AI Content Generator', 
						'type' => Controls_Manager::HEADING,
						'separator' => 'before', 
					]
				);
				$element->add_control(
						'content-source',
						[
							'label' => esc_html__( 'Content Source', 'textdomain' ),
							'type' => \Elementor\Controls_Manager::SELECT,
							'label_block' => true,
							'multiple' => true,
							'separator' => 'before', 
							'options' => [
								'default'  => esc_html__( 'Text Editor Content', 'textdomain' ),
								'keyword' => esc_html__( 'Custom Content', 'textdomain' ),
							
							],
							'default' => [ 'default', 'Text Editor Content' ],
							'dynamic' => [
								'active' => true,
							],
							
						]
					);
					$element->add_control(
						'title',
						[
							'label' => __( 'Add Custom Content', 'aacg' ),
							'type' => Controls_Manager::TEXTAREA,
							'default' => 'Who is Elon Musk?',
							'rows' => 10,
							'condition' => [
								'content-source' => 'keyword',
							],
						]
					);
				$element->add_control(
					'keyword',
					[
						'type' => Controls_Manager::TEXT,
						'label' => __('Keywords', 'aacg'),
						'label_block' => true,
						'description' => __('Provide keywords separated by commas for SEO', 'aacg'),
						'dynamic' => [
							'active' => true,
						],
						'condition' => [
							'dependent-control-name' => [ 'value-1', 'value-2' ],
						],
					]
				);
				$element->add_control(
					'prompt-list',
					[
						'label' => esc_html__( 'Select Prompt', 'textdomain' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'label_block' => true,
						'multiple' => true,
						'options' =>self::$prompt_data, 
						'default' => [ 'paragraph', 'Write a paragraph on this' ],
						'dynamic' => [
							'active' => true,
						],
					]
				);
				$element->add_control(
					'generate',
					[
						'type' => Controls_Manager::BUTTON,
						'label' => '',
						'separator' => 'before',
						'show_label' => false,
						'text' => __('Generate', 'aacg'),
						'button_type' => 'default',
						'event' => 'ai:content:generate'
			
					]
				);
			}
		}
		public static function get_prompt_data($prompt=false,$arr=true){
			if($arr==true){
				foreach(self::$prompt_data as $key =>$value){
					self::$prompt_arr[$key]=$value['menuTitle'];
				}
				return self::$prompt_arr;
			}
			if($prompt){
				if(isset(self::$prompt_data[$prompt]) && !empty(self::$prompt_data[$prompt]))
				{
					return self::$prompt_data[$prompt];
				}
			}
		}
	}
}
AI_Controllers::ai_controller_instance();
