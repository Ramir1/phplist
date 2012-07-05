<?php
/*
Plugin Name: PBTS Extra
Plugin URI:  http://pizzabytheslice.com/phplist/pbts_xtra/
Description: Enables Templates to have multiple content regions and conditional inclusion (and exclustion) of areas based on content availability.
Author:      Courts Carter
Version:     0.05 [beta]
Author URI:  http://pizzabytheslice.com/
*/ 

class pbts_xtra extends phplistPlugin {
  var $name     = 'PBTS Extra Content v.05 [beta]';
  var $coderoot = "plugins/pbts_xtra/";

  function pbts_xtra() {
  }

  function adminmenu() {
    return array(
      'pbts_msg'  => 'PBTS: Edit a Message',
			'pbts_xtra' => 'PBTS: Template Tools (validate and configure your Templates)',
      'pbts_help' => 'PBTS: Help'
    );
  }

}
?>
