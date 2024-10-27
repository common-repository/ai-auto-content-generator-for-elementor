<?php
// Exit if accessed direCSFy.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( class_exists( 'CSF' ) ) {
    $prefix = 'auto_content_generator';
    CSF::createOptions( $prefix, array(    
      'framework_title' =>'Auto Content Generator Settings',
      'menu_title' => 'Auto Content Generator Settings',
      'menu_slug'  => 'auto_content_generator',
      'menu_type' =>'submenu',
      'menu_parent' => 'auto_content_generator',
      'menu_capability' => 'manage_options', // The capability needed to view the page 
      'menu_icon'=>'assets/images/cool-timeline-icon.svg',
      'menu_position' => 7,
      'nav'=>'inline',
      'show_reset_section'=>false,
      'show_reset_all'=>false,		
      'show_bar_menu'=>false,
      
    ) );
    CSF::createSection( $prefix, array(
      'title'  => 'General Settings',
      'fields' => array(
         array(
          'id'      => 'aacgfe-secret-key',
          'type'    => 'text',
          'title'   => 'OpenAI API Secret Key',
          'default' => '',
          'desc' => 'Generate your secret key by visiting:-<a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI API Secret Key</a>',

        ),
        array(
          'id'      => 'aacgfe-temp',
          'type'    => 'text',
          'title'   => 'OpenAI API Temperature',
          'default' => 0.3,
          'desc' => 'The sampling temperature to use. Higher values means the model will take more risks .Try  0.9 for more creative applications, and 0 for ones with a well defined answer',
        ),
        array(
          'id'      => 'aacgfe-max-tok',
          'type'    => 'text',
          'title'   => 'OpenAI API Max Tokens',
          'desc' => 'The maximum number of tokens to generate in the completion,Default is 100.',
          'default' => 100
        ),
        array(
          'id'      => 'aacgfe-prepenalty',
          'type'    => 'text',
          'title'   => 'OpenAI API Presense Penalty',
          'desc' => 'Number betweeen -2.0 and 2.0 Default is 0. Positive values penalize new tokens based on whether they appear in the next so far, increasing the models likelihood to talk about new topics',
          'default' => 0
        ),
        array(
          'id'      => 'aacgfe-frequency',
          'type'    => 'text',
          'title'   => 'OpenAI API Frequency Penalty',
          'desc' => 'Number betweeen -2.0 and 2.0 Default is 0. Positive values penalize new tokens based on their existing frequency in the text so far , decreasing the models likelihood to repeat the same line verbatim',
          'default' => 0
        ),
      ),
     ) );
  }