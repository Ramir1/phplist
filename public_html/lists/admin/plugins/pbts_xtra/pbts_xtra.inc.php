<?php
/*
Plugin Name: PBTS Extra
Plugin URI:  http://pizzabytheslice.com/phplist/pbts_xtra/
Description: Enables Templates to have multiple content regions and conditional inclusion (and exclustion) of areas based on content availability.
Author:      Courts Carter
Version:     0.05 [beta]
Author URI:  http://pizzabytheslice.com/


-----------  ---------------------------------------------------
This file:   pbts_xtra.inc.php
Does:        Include library of common routines, performs actual manipulations. No UI.

*/ 

// ==================================================================
// FUNCTION: pbts_writeCSS
// DOES:     just emits some harmless CSS for admin pages
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_writeCSS(){

	return <<<EOT
<style type="text/css">
div.pbts_xtra{
  font-family:Verdana, Arial, Helvetica, sans-serif;
  }

div.pbts_xtra h2{ font-size: 16px; }
div.pbts_xtra h3{ font-size: 14px; letter-spacing: 1px; color: #939393; }
div.pbts_xtra h4{ font-size: 12px; letter-spacing: 1px; }

div.pbts_xtra p,
div.pbts_xtra td,
div.pbts_xtra input,
div.pbts_xtra textarea,
div.pbts_xtra select{
  font-family:Verdana, Arial, Helvetica, sans-serif;
  font-size: 10px;
	}
div.pbts_xtra select{
	background-color: #DCE7E7;
	}
div.pbts_xtra table{
  border-left: 1px solid #333333;
	}
div.pbts_xtra td{
  border-right: 1px solid #333333;
  border-bottom: 1px solid #333333;
  padding: 2px 4px;
	}
table.pbts_quiet,
table.pbts_quiet td {
	border: 0 none white !important;
	background-color: transparent !important;
	}
table.pbts_edit thead,
table.pbts_edit thead td,
div.pbts_xtra thead,
div.pbts_xtra thead td{ 
	background-color: #333333; 
	color:#FFFFFF; 
	font-weight:bold; 
	}

/* -------------------- */
table.pbts_edit,
table.pbts_edit td {
  border: 0 none white !important;
	}
table.pbts_edit{
	background-color: #EEF2F2;
	}

/*   width: 400px;
*/
input.pbts,
input.pbtsro,
textarea.pbts {
  font-family:Verdana, Arial, Helvetica, sans-serif;
  font-size: 10px;
	}
input.pbts{
	border: 1px solid #ccc;
	background-color: #DCE7E7;
	padding: 2px 4px ;
  }
input.pbtsro{
	border: 1px solid #996633;
	background-color: #FFFFCC;
	color:#663300;
	padding: 2px 4px ;
	}
textarea.pbts{
	border: 1px solid #ccc;
	background-color: #DCE7E7;
	padding: 4px 0 0 6px ;
  }
textarea.pbtsoutput{ 
	color: #660000;
	border: 1px solid  #990000;
	background-color: #FF99FF;
  width: 98%;
  }
.pbtswarning{
  color: #990000;
	font-size:12px;
  }
/*
	font-weight:bold;
*/
/* FAQ */
p.faq_a{
	padding: 0 15px 1em 15px;
	margin: 0 0;
	color:#333300;
	}
p.faq_q{
  color:#003366;
	}
</style>
EOT;

	} // pbts_writeCSS

// ==================================================================
// FUNCTION: pbts_pluginLinks
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_pluginLinks(){
	echo( "\n<h4>". PBTS_EXTRAS_VERSION ."</h4>".  
	      "\n<p>View : <a href=\"". PBTS_URL_MSG. "\">Draft Messages</a> || <a href=\"". PBTS_URL_TEMPLATE. "\">Templates</a> || <a href=\"". PBTS_URL_HELP. "\">Help</a> </p>" );
	}//pbts_pluginLinks


// ==================================================================
// FUNCTION: pbts_PlainText
// DOES:     strip some of the character-set specific punctuation that
//           really uglies-up Text email
// RETURNS:  
// PARAMS:   cleand input string
// ==================================================================
function pbts_PlainText( $p_in ){
  $p_in= eregi_replace( "&rsquo;", "'", $p_in );
  $p_in= eregi_replace( "&ndash;", "-", $p_in );
  $p_in= eregi_replace( "(&quot;|&ldquo;|&rdquo;)",  "\"", $p_in );
  $p_in= eregi_replace( "&nbsp;", " ", $p_in );
  $p_in= eregi_replace( "&amp;", "&", $p_in );

  return $p_in;
	} // pbts_PlainText


// ==================================================================
// FUNCTION: pbts_emitFields
// DOES:     Want to respect "tabindex" for the ordering of the fields.
// RETURNS:  
// PARAMS:   if isPostHandler need to emit warnings about blank fields
//           otherwise, this is the first viewing so don't penalize 
//           the poor folks
// ==================================================================
function pbts_emitFields( $p_isPostHandler ){
	global $g_pbts_tmpl_tags;
	
	$f_rtn='';
	
	$f_tags = array(); // used to prevent emitting duplicate fields
	$f_dupes= '';
	
	usort( $g_pbts_tmpl_tags, "pbts_sortTags");
	$f_rtn .= "\n<table cellspacing=\"0\" class=\"pbts_edit\">\n<thead>\n<tr><td>field name</td><td>value</td><td>description</td></tr>\n</thead>\n<tbody>" ;
	foreach( $g_pbts_tmpl_tags as $f_one){
		if (in_array($f_one['name'], $f_tags)){
		  $f_dupes.= '<br />'.$f_one['name'];
		} else {
		  $f_tags[]= $f_one['name'];

			if (isset($_POST[ $f_one['name']]))
				$f_value= $_POST[$f_one['name']];
			else
				$f_value='';
			$f_class= (($p_isPostHandler) && ($f_one['required'] == 'yes') && (strlen($f_value) < 1))?' class="pbtswarning"':'';
			$f_rtn .= "\n<tr><td valign=\"top\"".$f_class.">".$f_one['displayname'].": </td>" ;
			if ($f_one['type'] == 'textarea'){
				if ($f_one['paragraph'] == 'always')
					$f_value= pbts_stripPs( $f_value, $f_one['style'] );
				$f_rtn .= "\n<td><textarea class=\"pbts\" name=\"".$f_one['name']."\" rows=\"".$f_one['rows']."\" cols=\"".$f_one['cols']."\">". htmlspecialchars(stripslashes($f_value)) ."</textarea></td>" ;
			} else {
				$f_rtn .= "\n<td valign=\"top\"><input class=\"pbts\" type=\"text\" name=\"".$f_one['name']."\" value=\"". htmlspecialchars(stripslashes($f_value))."\" size=\"".$f_one['size']."\" ></td>" ;
			}
			$f_rtn .= "\n<td valign=\"top\">".$f_one['description']."</td></tr>" ;
			}
		} // foreach
	$f_rtn .= "\n</tbody>\n</table>" ;
	
	if ($f_dupes != '')
		$f_rtn .= '<p><strong>Duplicate Names Found</strong><br />This is not an error, duplicate names are allowed in Templates. The editor is using the setup information supplied in the first occurence for this Content Region(s):'. $f_dupes ;
	
	return $f_rtn;
	
	} // pbts_emitFields


// ==================================================================
// FUNCTION: pbts_getOption
// DOES:     Examines input string looking for: name = "value"
// RETURNS:  the value
// PARAMS:   in string, the option's name, a default value that will be
//           returned if the option is not found.
// ==================================================================
function pbts_getOption( $p_in, $p_opt, $p_default ){
	if (!preg_match( "/[ \t]+".$p_opt."[ \t]*=[ \t]*\"([^\"]+)\"/i", $p_in, $f_options ))
		return $p_default;
	else
		return $f_options[1];
	} // pbts_getOption



// ==================================================================
// FUNCTION: pbts_findTemplateTags
// DOES:     Now excludes dupes; uses only first occurence of each name
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_findTemplateTags(){
	global $g_pbts_template;
	global $g_pbts_tmpl_tags;
	global $g_pbts_tmpl_ifs;
	global $g_pbts_tmpl_warnings;

	#$g_pbts_tmpl_warnings='';
	$f_tags= array();

	#	"/\[PBTS_".$f_one['name']."([ \t]+[A-Z0-9_\- \t\"=]*)?\]/i"
  # improve. old:  if (!preg_match_all( "/(\[PBTS_[A-Z0-9\" \t_=\-]+\])/i", $g_pbts_template, $f_matches, PREG_PATTERN_ORDER )){
  if (!preg_match_all( "/(\[PBTS_.+\])/isU", $g_pbts_template, $f_matches, PREG_PATTERN_ORDER )){
		return false;
	} else {
		foreach( $f_matches[0] as $f_one){
			// check for reserved words
			if (preg_match("/^\[PBTS_IF[ \t]+/i",$f_one)){
				$g_pbts_tmpl_ifs[]= array(
					'condition'  => trim( strtolower( pbts_getOption( $f_one, 'condition', 'is_defined' ))), 
					'name'       => trim( strtolower( pbts_getOption( $f_one, 'name', '' )))
					);
			} else {
				// "regular" template tag
				$f_name= pbts_getOption( $f_one, 'name', '' );
				if ($f_name ==''){
					preg_match( "/\[PBTS_([A-Z0-9_\-]+)/i", $f_one, $f_names );  // counts on "greedy"
					$f_name= $f_names[1];
					}
				if (in_array($f_name, $f_tags)){
					$g_pbts_tmpl_warnings.= '<br />'.$f_name;
				} else {
					$f_tags[]= $f_name;
					$g_pbts_tmpl_tags[]= array(
						'name'       => strtolower( trim( $f_name )), 
						'displayname'=> trim( pbts_getOption( $f_one, 'displayname', $f_name )), 
						'type'       => strtolower( trim( pbts_getOption( $f_one, 'type', 'text' ))),
						'size'       => trim( pbts_getOption( $f_one, 'size', '60' )),
						'rows'       => trim( pbts_getOption( $f_one, 'rows', '1' )),
						'cols'       => trim( pbts_getOption( $f_one, 'cols', '60' )),
						'required'   => strtolower( trim( pbts_getOption( $f_one, 'required', 'yes' ))),
						'paragraph'  => strtolower( trim( pbts_getOption( $f_one, 'paragraph', 'always' ))),
						'tabindex'   => trim( pbts_getOption( $f_one, 'tabindex', '99' )),
						'description'=> pbts_getOption( $f_one, 'description', '' ),
						'style'      => pbts_getOption( $f_one, 'style', '' )
						);
				}// if: dupes
			} // if reserved words
			} // foreach
		return true;
	} // if

	} // pbts_findTemplateTags



// ==================================================================
// FUNCTION: pbts_sortTags
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_sortTags($a, $b){
	#echo( "\n<br />a. ".$a['tabindex']." and b: ".$b['tabindex'] );
	if ($a['tabindex'] == $b['tabindex']) {
		return 0;
	}
	return ($a['tabindex'] > $b['tabindex']) ? +1 : -1;
	} // pbts_sortTags


// ==================================================================
// FUNCTION: pbts_getSnippet
// DOES:     Returns contents between the [tag]blah blah [end_tag] 
//           uses the global $g_pbts_html_content
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_getSnippet( $p_in, $p_tag_name ){
	global $g_pbts_tmpl_warnings;

	if (preg_match( "/\[PBTS_". $p_tag_name ."[^] \t]*](.*)\[\/PBTS_/isU", $p_in, $f_matches )){
	  #echo( "\n<hr>\n<p>$p_tag_name OK matched!!!</p><p>$f_matches[1]" );
		return $f_matches[1];

	} else if (preg_match( "/\[PBTS_[A-Z0-9_\-]+[A-Z0-9_\- \t\"=]* name[ \t]*=[ \t]*\"".$p_tag_name."\"[A-Z0-9_\- \t\"=]*](.*)\[\/PBTS_/isU", $p_in, $f_matches )){
	  // look for something that begins [PBTS_ 
		// followed by any character, number, dash, tab, space, underscore, quote, and equals which for simplicity we'll call Opt_Chars
		// possibly followed by space or tab
		// followed by name
		// possibly followed by space or tab
		// followed by equals
		// possibly followed by space or tab
		// followed by quote name quote
		// possibly followed by Opt_Chars
		// followed by right-bracket
		// followed by anything
		// followed by [/PBTS_
	  #echo( "\n<hr>\n<p>$p_tag_name, FORCED using name option !!!</p><p>$f_matches[1]" );
		return $f_matches[1];

	} else{
	  $g_pbts_tmpl_warnings.= "\n<p style=\"color: red;\">failed to find content for tag \"$p_tag_name\"</p>";
		return "";

	}
	} // pbts_getSnippet



// ==================================================================
// FUNCTION: pbts_merge_html_content
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_merge_html_content(){
	global $g_pbts_html_content;
	global $g_pbts_tmpl_tags;
	global $g_pbts_tmpl_ifs;
	global $g_pbts_template;

	$f_return = $g_pbts_template;

	foreach( $g_pbts_tmpl_ifs as $f_one){
		// handle IF conditional stuff
	  if ($f_one['name'] != ''){
		  # the next line is one of the saddest I've ever written, but straight assignment without the casts leaves isBlank empty!!! really!
			$f_isBlank= (int)(bool)(strlen(pbts_getSnippet( $g_pbts_html_content, $f_one['name'] )) < 1);
			$f_ShowIfNotBlank = (int)(bool)(preg_match("/^((is)?[ \t_-]*defined|(is)?[ \t_-]*set|not[ \t_-]*blank|not[ \t_-]*empty)$/i",$f_one['condition']) > 0);

			if ($f_ShowIfNotBlank)
				$f_pattern= "(is)?[ \t_-]*(defined|set|not[ \t_-]*blank|not[ \t_-]*empty)";
			else
				$f_pattern= "(is)?[ \t_-]*(not[ \t_-]*defined|not[ \t_-]*set|blank|empty)";

			#echo( "\n<p>IF name= \"".$f_one['name']."\": isBlank= $f_isBlank and ShowIfNotBlank= $f_ShowIfNotBlank, condition is \"".$f_one['condition'] ."\"");
			if (($f_isBlank and $f_ShowIfNotBlank) OR 
			    (!$f_isBlank and !$f_ShowIfNotBlank)){
				# Show if NOT BLANK but the Content IS Blank, OR
				# Show if IS Blank But the Content is NOT Blank
				# ... then nuke the stuff
				#echo( "\n<br /> Action: NUKE" );
				$f_replacement_default='';
				$f_replacement= '';
			} else {
				# garbage collection, that is, strip out the if/endif tags
				#echo( "\n<br /> Action: Clean" );
				$f_replacement_default='$1';
				$f_replacement='$3';
			} // if: what gets nuked
			$f_return = preg_replace( "/\[PBTS_IF[ \t]+name[ \t]*=[ \t]*\"".$f_one['name']."\"](.*)\[\/PBTS_IF]/isU", $f_replacement_default, $f_return );
			$f_return = preg_replace( "/\[PBTS_IF[ \t]+condition[ \t]*=[ \t]*\"".$f_pattern."\"[ \t]+name[ \t]*=[ \t]*\"".$f_one['name']."\"](.*)\[\/PBTS_IF]/isU", $f_replacement, $f_return );
			$f_return = preg_replace( "/\[PBTS_IF[ \t]+name[ \t]*=[ \t]*\"".$f_one['name']."condition[ \t]*=[ \t]*\"".$f_pattern."\"[ \t]+\"](.*)\[\/PBTS_IF]/isU", $f_replacement, $f_return );
			} // if: name is define
		} // foreach: g_template_ifs

	// next: the standard Custom Tag stuff
	foreach( $g_pbts_tmpl_tags as $f_one){
		$my_temp  = pbts_getSnippet( $g_pbts_html_content, $f_one['name'] );

		if ($f_one['paragraph']== 'always'){
			$my_temp= pbts_addPs( $my_temp, $f_one['style'] );
			#  $my_temp= pbts_stripPs( $my_temp, $f_one['style'] );
			} // paragraph processing?
		$f_return = preg_replace( "/\[PBTS_".$f_one['name']."([ \t]+[^\]]*)?\]/iU", $my_temp, $f_return );
		$f_return = preg_replace( "/\[PBTS_[^\]]*[ \t]+name[ \t]*=[ \t]*\"".$f_one['name']."\"[^\]]*]/iU", $my_temp, $f_return );
		} // foreach

	// lastly, strip the required [CONTENT] tag, muhahahahaha!
	$f_return = preg_replace( "/\[CONTENT]/isU", '', $f_return );
	return $f_return;

	} // pbts_merge_html_content


// ==================================================================
// FUNCTION: pbts_handlePost
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_handlePost(){
	global $g_pbts_html_content;
	global $g_pbts_tmpl_tags;
	global $g_pbts_errors;
	
	$g_pbts_html_content = '';

	foreach( $g_pbts_tmpl_tags as $f_one){
		if (isset($_POST[ $f_one['name']])){
			$f_temp= trim($_POST[$f_one['name']]);
			#if ($f_one['paragraph']== 'always'){
			#	$f_temp= pbts_addPs( $f_temp, $f_one['style'] );
			#	} // paragraph processing?
			$g_pbts_html_content .= "\n<p>[PBTS_EXTRA NAME=\"".$f_one['name']."\"]". $f_temp."[/PBTS_EXTRA]</p>";
			if (($f_one['required'] == 'yes') && (strlen($f_temp) < 1)){
				$g_pbts_errors.= $f_one['displayname']. ', ';
				} // if
			} // if set
		} // foreach

	} // pbts_handlePost

// ==================================================================
// FUNCTION: pbts_addPs
// DOES:     Returns contents with double newlines replaced with <P> tags
//           This, clearly, is insanity-- adding P's, then removing them
//           
// RETURNS:  modified string
// PARAMS:   input string
// ==================================================================
function pbts_addPs( $p_in, $p_style ){
  if (strlen($p_in) < 1)
	  return $p_in;

  $p_in="<p>".$p_in."</p>";
	$p_in= eregi_replace( "(.)\n", "</p>\\1\n<p>", $p_in );
	$p_in= eregi_replace( "<p>[ \t]*</p>", "\n", $p_in );
	$p_in= eregi_replace( "</p>[ \t]*</p>", "</p>", $p_in );
	$p_in= eregi_replace( "<p>[ \t]*<p([ \t>])", "<p\\1", $p_in );
	$p_in= eregi_replace( "\n\n", "\n", $p_in );
	$p_in= preg_replace( "/^<p>([ \t]*<[\/]?(li|ol|ul))/ism", "$1", $p_in );
	$p_in= preg_replace( "/((li|ol|ul)>)[ \t]*<\/p>/ism", "$1", $p_in );

	if (strlen($p_style) > 0)
		$p_in= eregi_replace( "<p[ \t]*>", '<p style="'.$p_style.'">', $p_in );

  return $p_in;
	} // pbts_addPs


// ==================================================================
// FUNCTION: pbts_stripPs
// DOES:     Strips <p> tags (empty or those with assigned style) for
//           easiery editing
//           
// RETURNS:  modified string
// PARAMS:   input string
// ==================================================================
function pbts_stripPs( $p_in, $p_style ){
	$p_in= preg_replace( "/[ \t]*<p[ \t]*>(.*)<\/p>/ismU", "$1", $p_in );
	if (strlen($p_style) > 0)
		$p_in= preg_replace( "/[ \t]*<p[ \t]*style=\"".$p_style."\"[ \t]*>(.*)<\/p>/ismU", "$1", $p_in );
	return $p_in;
	} // pbts_stripPs


// ==================================================================
// FUNCTION: pbts_parseMsgBody
// DOES:     using global template tags (g_pbts_tmpl_tags) reads from 
//           global Content (g_pbts_html_content), grabbing appropriate 
//           marked-up regions and creating POST variables, with appropriate
//           name (given by tags)
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_parseMsgBody(){
	global $g_pbts_html_content;
	global $g_pbts_tmpl_tags;
	foreach( $g_pbts_tmpl_tags as $f_one){
		$_POST[$f_one['name']] = pbts_getSnippet( $g_pbts_html_content, $f_one['name'] );
		} // foreach
	} // pbts_parseMsgBody



// ==================================================================
// FUNCTION: pbts_read
// DOES:     
// RETURNS:  empty string if problem reading, otherwise file contents
// PARAMS:   
// ==================================================================
function pbts_readTemplate($p_id ){
	$f_rtn='';
	#$tables["template"]
	if (!is_numeric($p_id))
		return '';

	$req = Sql_Query( 'select template from '.$GLOBALS["tables"]["template"].' WHERE (id='.$p_id.')');
	while ($row = Sql_Fetch_Array($req)){
		$f_rtn= stripslashes($row['template']);
		}
	return $f_rtn;
	}// pbts_readTemplate



// ==================================================================
// FUNCTION: pbts_send_core_save
// SCOPE:    send_core.php
// DOES:     returns string containing marked up regions, as per template instructions
// RETURNS:  
// PARAMS:   none, relies upon POST vars
// ==================================================================
function pbts_send_core_save(){
	global  $g_pbts_template;
	global  $g_pbts_html_content;

	$g_pbts_template= pbts_readTemplate( $_POST["template"] );
	pbts_findTemplateTags();
	pbts_handlePost();

	return $g_pbts_html_content;

	} // pbts_send_core_save


// ==================================================================
// FUNCTION: pbts_send_core_editor
// SCOPE:    send_core.php (public)
// DOES:     returns the form elements designated by the template (the one in POST vars)
// RETURNS:  
// PARAMS:   none, relies upon POST vars
// ==================================================================
function pbts_send_core_editor(){
	global $g_pbts_html_content;
	global $g_pbts_template;
	global $g_pbts_tmpl_tags;
	global $g_pbts_errors;

#		pbts_init();
	if (strlen($g_pbts_template)<1){
	$g_pbts_template= pbts_readTemplate( $_POST["template"] );
	pbts_findTemplateTags();
		}// if
	if (count($g_pbts_tmpl_tags) < 1){
		return '<textarea name="message" cols="65" rows="20">'.htmlspecialchars($_POST["message"]).'</textarea>';

	} else {
		$g_pbts_html_content= $_POST["message"];
		pbts_parseMsgBody();
	
		# asap link to this: <a href="'. PBTS_URL_MSG. '&tab=embed&id='.$id.'" target="_blank">preview</a>

		if ($g_pbts_errors != '')
			$g_pbts_errors= "\n<p class=\"pbtswarning\">You have left some required fields empty.<br />$g_pbts_errors</p>"; 

		return '<input type="hidden" name="pbts_xtra" value="1">'. 
			pbts_writeCSS(). 
			$g_pbts_errors.
			pbts_emitFields( true );

	} // if has PBTS tags
	} // pbts_send_core_editor 



// ==================================================================
// FUNCTION: pbts_init
// DOES:     clears all variables, readies PBTS to process a Template
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_init(){
	global $g_pbts_tmpl_tags;
	global $g_pbts_tmpl_ifs;
	global $g_pbts_tmpl_warnings;
	global $g_pbts_errors;
	global $g_pbts_template;
	global $g_pbts_html_content;

	$g_pbts_tmpl_tags      = array();
	$g_pbts_tmpl_ifs       = array();
	#unset($g_pbts_tmpl_tags);
	#unset($g_pbts_tmpl_ifs);
	$g_pbts_tmpl_warnings  ='';
	$g_pbts_errors         ='';
	
	$g_pbts_template      = '';
  $g_pbts_html_content  = '';

	} // pbts_init


// ==================================================================
// FUNCTION: pbts_replace_content
// SCOPE:    sendmaillib.php (Public)
// DOES:     formating for sendmaillib.php
// RETURNS:  substituted string
// PARAMS:   
// ==================================================================
function pbts_replace_content( $p_htmlcontent, $p_template ){
	global $g_pbts_html_content;
	global $g_pbts_template;

	// pbts_init();

	$g_pbts_html_content = $p_htmlcontent;
	$g_pbts_template     = $p_template;

	if (pbts_findTemplateTags())
		$g_pbts_html_content= pbts_merge_html_content();

	return stripslashes($g_pbts_html_content);
	
	};

// ====================================================================================================
//         M A I N
// ====================================================================================================
	define( 'PBTS_EXTRAS_VERSION', 'PBTS Extra Content v0.05 [beta]' );
	define( 'PBTS_URL_MSG',        '?page=pbts_msg&pi=pbts_xtra' );
	define( 'PBTS_URL_TEMPLATE',   '?page=pbts_xtra&pi=pbts_xtra' );
	define( 'PBTS_URL_HELP',       '?page=pbts_help&pi=pbts_xtra' );

	$g_pbts_tmpl_tags      = array();  // array of all tags in the template
	$g_pbts_tmpl_ifs       = array();  // array of all conditional "if" tags in the template, need better name
	$g_pbts_tmpl_warnings  ='';        // set when reading Template or Merging Content
	$g_pbts_errors         ='';        // 
	
	$g_pbts_template      = '';       // testing version of $cached[$messageid]["template"]
  $g_pbts_html_content  = '';       //

?>