<?php
/*
Plugin Name: PBTS Extra
Plugin URI:  http://pizzabytheslice.com/phplist/pbts_xtra/
Description: Enables Templates to have multiple content regions and conditional inclusion (and exclustion) of areas based on content availability.
Author:      Courts Carter
Version:     0.05 [beta]
Author URI:  http://pizzabytheslice.com/

-----------  ---------------------------------------------------
This file:   pbts_xtra.php
Does:        Exposes interface elements useful in adding plug-in's 
             features to a Template (tools, editors, etc)

*/ 

// ==================================================================
// FUNCTION: pbts_pgTop
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_pgTop($p_id, $p_selected){
	$tabs = new WebblerTabs();
	$tabs->addTab('view tags',PBTS_URL_TEMPLATE."&tab=tags&id=".$p_id);
	$tabs->addTab('edit tags',PBTS_URL_TEMPLATE."&tab=tagger&id=".$p_id);
	$tabs->addTab('preview form',PBTS_URL_TEMPLATE."&tab=form&id=".$p_id);
	$tabs->addTab('highlight tags',PBTS_URL_TEMPLATE."&tab=highlight&id=".$p_id);
	$tabs->addTab('edit',PBTS_URL_TEMPLATE."&tab=edit&id=".$p_id);
	$tabs->setCurrent($p_selected);

	pbts_pluginLinks();
	print $tabs->display();
	echo( "<p><a href=\"?page=template&id=". $p_id . "\">Edit this Template with standard phpList Editor &raquo;</a></p>"	);

	} // pbts_pgTop


// ==================================================================
// FUNCTION: pbts_writeTemplateName
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_writeTemplateName( $p_selected_id ){
	$req = Sql_Query( "select title from ".$GLOBALS["tables"]["template"]." WHERE (id = ".$p_selected_id.")" );
	#$tables["template"]
	while ($row = Sql_Fetch_Array($req)) {
		echo( sprintf('<p>Template: <input type="text" name="title" readonly class="pbtsro" value="%s" size="70" />', stripslashes($row["title"]) ));
		}// while
	} // pbts_writeTemplateName



// ==================================================================
// FUNCTION: pbts_listTemplates
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_listTemplates(){
	echo( pbts_writeCSS());

?>
<div class="pbts_xtra">
<?php	pbts_pluginLinks(); ?>
<h2>Templates</h2>
<table cellspacing="0">
	<thead>
		<tr>
			<th>Title</th>
			<th>Action</th>
			</tr>
	</thead>
	<tbody>
<?php
	$req = Sql_Query( 'SELECT id, title FROM '.$GLOBALS["tables"]["template"].' ORDER BY listorder ASC' );
	while ($row = Sql_Fetch_Array($req)){
		echo( "\n<tr><td>".
			stripslashes($row['title'])."</td><td>".
			"<a href=\"". PBTS_URL_TEMPLATE."&tab=edit&id=".stripslashes($row['id'])."\">edit</a><br ></td>".
			"</tr>" );
		}
?>
	</tbody>
</table>
</div><!-- pbts_xtra --->
<?php

	}// pbts_listTemplates



// ==================================================================
// FUNCTION: pbts_highlight
// DOES:     writes a page showing the Template with tags highlighted
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_highlight($p_template){
	$f_preview= pbts_readTemplate($p_template );
	$f_preview = htmlspecialchars(stripslashes( $f_preview ));
	// forwardform","forward","subscribe","preferences","unsubscribe","signature
	// forwardurl","subscribeurl","preferencesurl","unsubscribeurl
	$f_preview= preg_replace( "/(\[(CONTENT|FOOTER|SIGNATURE|USERID|USERTRACK|LISTOWNER|LISTS|PREFERENCES)\])/isU", "<span class=\"highplst\">\\1</span>", $f_preview );
	$f_preview= preg_replace( "/(\[PBTS_IF[ \t]+.*\[\/PBTS_IF[ \t]*\])/isU", "<span class=\"highifinc\">\\1</span>", $f_preview );
	$f_preview= preg_replace( "/(\[PBTS_.*\])/isU", "<span class=\"hightag\">\\1</span>", $f_preview );
	$f_preview= preg_replace( "/(\[PBTS_IF[ \t]+.*\])/isU", "<span class=\"highif\">\\1</span>", $f_preview );
	$f_preview= preg_replace( "/(\[\/PBTS_IF[ \t]*\])/isU", "<span class=\"highif\">\\1</span>", $f_preview );
	# 
	# next two disabled lines: at one point this would find a specific tag for you, highlighting it differently, but... progress cuts both ways. tsk tsk.
	#$f_preview= preg_replace( "/(\[PBTS_". $f_tag_name ."[ \t]*.*\])/isU", "<span class=\"found\">\\1</span>", $f_preview );
	#$f_preview= preg_replace( "/(\[PBTS_[A-Z0-9_\-]+[ \t]+name[ \t]*=[ \t]*&quot;".$f_tag_name."&quot;.*\])/isU", "<span class=\"found\">\\1</span>", $f_preview );
	#
	$f_preview= preg_replace( "/(\[PBTS_[A-Z0-9_\-]+[ \t]+name[ \t]*=[ \t]*&quot;)(.*)(&quot;.*\])/isU", "\\1<strong>\\2</strong>\\3", $f_preview );
	$f_preview= preg_replace( "/(\[PBTS_)([A-Z0-9_\-]+)/is", "\\1<strong>\\2</strong>", $f_preview );
	$f_preview= preg_replace( "/(\[PBTS_)<strong>(if|xtra)<\/strong>[ \t]+/is", "\\1\\2 ", $f_preview );
	$f_preview= preg_replace( "/\t/", "  ", $f_preview );
  echo( pbts_writeCSS());
  echo( "\n<div class=\"pbts_xtra\">" );
  pbts_pgTop($p_template, 'highlight tags' );
  pbts_writeTemplateName( $p_template );

?>
<style type="text/css">
div.sneakpeek{
  width: 740px;
	height: 450px;
	border: 1px solid #000033;
	padding: 6px;
	background-color: #fff;
	overflow:scroll;
	}
div.sneakpeek,
div.sneakpeek pre{
  font-family:Arial, Helvetica, sans-serif;
	font-size: 11px;
	line-height: 1.2em;
	}
.found{ 
	background-color:#FFFFCC;
  color:#990000;
	}
.highif{ 
	background-color: #D9E7E8;
  color: #003366;
	}
.highifinc{ 
	background-color: #f0f8f9;
  color: #3a4c4d;
	}
.hightag{ 
	background-color: #E1ECDD;
  color: #003333;
	}
.highplst{ 
	background-color: #FDE0FE;
  color: #660033;
	}
</style>
<h3>Highlight Placeholders &amp; Your Custom Tags Inside Template HTML</h3>
<p>Key:
	<br /><span class="highplst">standard phpList placeholder</span>
	<br /><span class="highif">PBTS IF (conditional statement)</span>
	<br /><span class="hightag">PBTS custom tag</span>
	</p>
<div class="sneakpeek">
<pre>
<?php echo( $f_preview ); ?>
</pre>
</div><!-- sneakpeek -->
</div><!-- pbts_xtra -->
<?php

	} // pbts_highlight


// ==================================================================
// FUNCTION: pbts_showTags
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_showTags( $p_template ){
	global $g_pbts_template;
	global $g_pbts_tmpl_tags;
	global $g_pbts_tmpl_ifs;
	global $g_pbts_tmpl_warnings;

	$f_tags = array();

	echo( pbts_writeCSS());
	echo( "\n<div class=\"pbts_xtra\">" );

	pbts_pgTop($p_template, 'view tags' );
	pbts_writeTemplateName( $p_template );
	$g_pbts_template= pbts_readTemplate( $p_template );

	if (!pbts_findTemplateTags())
		echo( "<p>no PBTS tags found</p>" );
	else
		echo( "<p>PBTS tags found</p>" );

?>
<p>Tags</p>
<table cellspacing="0">
	<thead>
	<tr>
		<td>#</td>
		<td>name</td>
		<td>display name</td>
		<td>type</td>
		<td>size</td>
		<td>rows</td>
		<td>cols</td>
		<td>required</td>
		<td>paragraphs</td>
		<td>tabindex</td>
		<td>style</td>
		<td>description</td>
		</tr>
	</thead>
	<tbody>
<?php 

	$i=1;

	foreach( $g_pbts_tmpl_tags as $f_one){
		echo( "\n<tr><td>$i</td><td>".
			"<a href=\"".PBTS_URL_TEMPLATE."&tab=tagger&id=".$p_template."&tag_name=".$f_one['name']."\">". $f_one['name']."</a></td><td>".
			$f_one['displayname']."</td><td>".
			$f_one['type']."</td><td>".
			$f_one['size']."</td><td>".
			$f_one['rows']."</td><td>".
			$f_one['cols']."</td><td>".
			$f_one['required']."</td><td>". 
			$f_one['paragraph']."</td><td>".
			$f_one['tabindex']."</td><td>". 
			$f_one['style']."</td><td>".
			$f_one['description']."</td></tr>" );
		$i++;
		if (in_array($f_one['name'], $f_tags))
		  $g_pbts_tmpl_warnings.= '<br />'.$f_one['name'];
		else
			$f_tags[]= $f_one['name'];
		} // foreach

	?>
	</tbody>
</table>

<p>Conditional Regions</p>
<table cellspacing="0">
	<thead>
	<tr>
		<td>#</td>
		<td>condition</td>
		<td>name</td>
		</tr>
		</thead>
		<tbody>	
<?php 

	$i=1;
	foreach( $g_pbts_tmpl_ifs as $f_one){
		echo( "\n<tr><td>$i</td><td>".
			$f_one['condition']."</td><td>".
			$f_one['name']."</td></tr>" );
		$i++;
		} // foreach

?>
	</tbody>
</table>
<?php 
	
	if ($g_pbts_tmpl_warnings != '')
		echo( '<p><strong>Duplicate Names Found</strong><br />This is not an error, duplicate names are allowed in Templates. Dupe Content Region(s):'. $g_pbts_tmpl_warnings );

?>
</div><!-- pbts_xtra -->
<?php 
	} // pbts_showTags



// ==================================================================
// FUNCTION: pbts_scrubber
// DOES:     get rid of newlines and double-quotes
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_scrubber( $p_in ){
	return preg_replace( "/\"/", "'", preg_replace( "/\n/", "", $p_in ));
	} // pbts_scrubber


// ==================================================================
// FUNCTION: pbts_editTags
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_editTags($p_template){
  global $g_pbts_template;
  global $g_pbts_tmpl_tags;

	// --------------------------------
	// Save Template Tag?
	// --------------------------------
	if (isset($_POST['doaction']) && $_POST['doaction'] == 'save'){
	  $f_tag_name= $_POST[ 'tag_name' ];
	  # echo( "<h1>saving... $f_tag_name</h1>" );
		$f_combined_tag= 
		  "[PBTS_XTRA name=\"" .$_POST[ 'tag_name' ]. 
			"\" type=\""         .$_POST[ 'tag_type' ]. 
			"\" displayname=\""  .pbts_scrubber($_POST[ 'tag_displayname' ]). 
			"\" required=\""     .$_POST[ 'tag_required' ]. 
			"\" size=\""         .pbts_scrubber($_POST[ 'tag_size' ]). 
			"\" rows=\""         .pbts_scrubber($_POST[ 'tag_rows' ]). 
			"\" cols=\""         .pbts_scrubber($_POST[ 'tag_cols' ]). 
			"\" tabindex=\""     .$_POST[ 'tag_tabindex' ]. 
			"\" paragraph=\""    .$_POST[ 'tag_paragraph' ]. 
			"\" description=\""  .pbts_scrubber($_POST[ 'tag_description' ]). 
			"\" style=\""        .pbts_scrubber($_POST[ 'tag_style' ]). "\"]";

		$g_pbts_template= pbts_readTemplate($_POST['templateid'] );

		$g_pbts_template= preg_replace( "/\[PBTS_". $f_tag_name .".*\]/isU", $f_combined_tag, $g_pbts_template );
		$g_pbts_template= preg_replace( "/\[PBTS_[A-Z0-9_\-]+[ \t]+name[ \t]*=[ \t]*\"".$f_tag_name."\".*\]/isU", $f_combined_tag, $g_pbts_template );

		Sql_query( 
			sprintf( 'UPDATE %s SET template= "%s" WHERE (id = %d)',
				$GLOBALS["tables"]["template"], 
				addslashes($g_pbts_template),
				$_POST['templateid']  ));
		echo( "\n<p class=\"pbtswarning\">Saved at ". date("h:i:s a")."</p>");
		} //


	// --------------------------------
	// OK, now read and display stuff...
	// --------------------------------
  $g_pbts_template= pbts_readTemplate( $p_template );
  if (!pbts_findTemplateTags())
    echo( "<p>no PBTS tags found</p>" );

  if (isset( $_POST['tag_name']))
    $f_tag_name= $_POST['tag_name'];
  else if (isset( $_GET['tag_name']))
    $f_tag_name= $_GET['tag_name'];
  else if (count($g_pbts_tmpl_tags) > 0)
    $f_tag_name= $g_pbts_tmpl_tags[0]['name'];
  else
    $f_tag_name= '';

  $f_tag_displayname  = '';
  $f_tag_required     = '';
  $f_tag_type         = '';
  $f_tag_rows         = '';
  $f_tag_cols         = '';
  $f_tag_size         = '';
  $f_tag_paragraph    = '';
  $f_tag_description  = '';
  $f_tag_style        = '';
  $f_tag_tabindex     = '';

  $f_options= '';
	$f_tabidx_options= '';
  foreach($g_pbts_tmpl_tags as $f_one){
    if ($f_tag_name != $f_one['name'])
      $f_temp_selected= '';
    else {
      $f_temp_selected= 'selected';
      $f_tag_displayname  = $f_one['displayname'];
      $f_tag_required     = $f_one['required'];
      $f_tag_type         = $f_one['type'];
      $f_tag_rows         = $f_one['rows'];
      $f_tag_cols         = $f_one['cols'];
      $f_tag_size         = $f_one['size'];
      $f_tag_paragraph    = $f_one['paragraph'];
      $f_tag_description  = $f_one['description'];
      $f_tag_style        = $f_one['style'];
		  $f_tag_tabindex     = $f_one['tabindex'];
      } // this is it
    $f_options.= "\n<option value=\"". $f_one['name']."\" ".$f_temp_selected.">".$f_one['displayname']."</option>";
    } // foreach

	for	($f_i=0; $f_i < count($g_pbts_tmpl_tags); $f_i++){
		$f_temp_selected= ($f_tag_tabindex == ($f_i+1) )? 'selected': '';
		$f_tabidx_options.= "\n<option value=\"". ($f_i+1)."\" ".$f_temp_selected.">".($f_i+1)."</option>";
		}// for

	// --------------------------------
	// now, just emit the page
	// --------------------------------
  echo( pbts_writeCSS());
  echo( "\n<div class=\"pbts_xtra\">" );
  pbts_pgTop($p_template, 'edit tags' );
  pbts_writeTemplateName( $p_template );

?>
<form action="<?php echo(PBTS_URL_TEMPLATE) ?>&tab=tagger&id=<?php echo( $p_template ); ?>" method="post">
<input type="hidden" name="doaction" value="switch" />
<input type="hidden" name="templateid" value="<?php echo( $p_template ); ?>" />
<p>Choose the Tag you wish to edit and click the "Edit Tag" button to reload this page using the Tag's properties. Only Tags already defined in this Template are listed.</p>
<table cellspacing="0" class="pbts_quiet">
	<tr>
		<td valign="top">Tag:</td>
		<td valign="top"><select name="tag_name" class="pbts" style="width: 200px;"><?php echo( $f_options ); ?></select></td>
		<td><input type="submit" name="submit"  value=" Edit Tag " /></td>
		</tr>
</table>
</form>
<hr>
<form action="<?php echo(PBTS_URL_TEMPLATE) ?>&tab=tagger&id=<?php echo( $p_template ); ?>" method="post">
<p><input type="submit" name="submit"  value=" Save! " /></p>
<input type="hidden" name="doaction" value="save" />
<input type="hidden" name="templateid" value="<?php echo( $p_template ); ?>" />
<input type="hidden" name="tag_name" value="<?php echo( $f_tag_name ); ?>" />
<table cellspacing="0" class="pbts_edit">
	<tr>
		<td nowrap valign="top">Name:</td>
		<td valign="top"><input class="pbtsro" readonly="yes" name="tag_name_ro" value="<?php echo( $f_tag_name ); ?>" style="width: 200px;"></td>
		<td valign="top">The <em>Name</em> is not editable from this page. To change a Tag's name Edit the Template. To edit a different tag select desired tag from list above. </td>
		</tr>
	<tr>
		<td nowrap valign="top">Display Name:</td>
		<td valign="top"><input class="pbts" name="tag_displayname" value="<?php echo( $f_tag_displayname ); ?>" style="width: 200px;"></td>
		<td valign="top">Name shown in the Editor</td>
		</tr>
	<tr>
		<td valign="top">Type</td>
		<td valign="top"><select name="tag_type" style="width: 200px;">
			<option value="text" <?php if ($f_tag_type == 'text' ) echo( 'selected'); ?>>Text</option>
			<option value="textarea" <?php if ($f_tag_type != 'text' ) echo( 'selected'); ?>>Textarea</option>
			</select></td>
		<td valign="top">What kind of input should be used on the editor? A single line of <strong>text</strong>, or a larger, multiline <strong>Textarea</strong>?</td>
		</tr>
	<tr>
		<td valign="top">Size:</td>
		<td valign="top"><input class="pbts" name="tag_size" size="3" value="<?php echo( $f_tag_size ); ?>"></td>
		<td valign="top">If you choose a Text type this is how the field's width (users can type as much as they want, this is just the input areas width) </td>
		</tr>
	<tr>
		<td valign="top">Rows:</td>
		<td valign="top"><input class="pbts" name="tag_rows" size="3" value="<?php echo( $f_tag_rows ); ?>"></td>
		<td valign="top" rowspan="2">For Textarea Types how many rows tall and columns wide should the Textarea be?</td>
		</tr>
	<tr>
		<td valign="top">Columns:</td>
		<td valign="top"><input class="pbts" name="tag_cols" size="3" value="<?php echo( $f_tag_cols ); ?>"></td>
		</tr>
	<tr>
		<td valign="top">Required?</td>
		<td valign="top"><select name="tag_required" style="width: 200px;">
			<option value="yes" <?php if ($f_tag_required == 'yes' ) echo( 'selected'); ?>>Yes</option>
			<option value="no" <?php if ($f_tag_required != 'yes' ) echo( 'selected'); ?>>No</option>
			</select></td>
		<td valign="top">If the field is required the Message Editor issues a warning if it's left blank</td>
		</tr>
	<tr>
		<td valign="top">Paragraph:</td>
		<td valign="top" colspan="2">
		<table class="pbts_quiet">
			<tr>
				<td valign="top"><input type="radio" <?php if ($f_tag_paragraph == 'always' ) echo( 'checked'); ?> value="always" name="tag_paragraph"></td>
				<td valign="top"><strong>Always</strong> wrap text in paragraph tags. In Textareas carriage returns will be translated into Paragraph tags.</td>
				</tr>
			<tr>
				<td valign="top"><input type="radio" <?php if ($f_tag_paragraph !== 'always' ) echo( 'checked'); ?> value="never" name="tag_paragraph"></td>
				<td valign="top"><strong>Never</strong> add Paragraphs. Paragraphs tags are removed.</td>
				</tr>
		</table>
		</tr>
	<tr>
		<td valign="top" nowrap>Tab index:</td>
		<td valign="top"><select name="tag_tabindex" class="pbts" style="width: 200px;"><?php echo( $f_tabidx_options ); ?></select></td>
		<td valign="top"></td>
		</tr>
	<tr>
		<td valign="top">Description:</td>
		<td valign="top"><textarea name="tag_description" rows=3 class="pbts"  style="width: 200px;"><?php echo( htmlspecialchars(stripslashes($f_tag_description ))); ?></textarea></td>
		<td valign="top">Displayed as a helpful prompt within the Message Editor</td>
		</tr>
	<tr>
		<td valign="top">Style:</td>
		<td valign="top"><textarea name="tag_style" rows=3 class="pbts"  style="width: 200px;"><?php echo( htmlspecialchars(stripslashes($f_tag_style ))); ?></textarea></td>
		<td valign="top">If Paragraph is "always" you may add a inline CSS style that will be applied to all Paragraph tags.</td>
		</tr>
</table>
</form>
</div><!-- pbts_xtra -->
<?php
	} // pbts_editTags


// ==================================================================
// FUNCTION: pbts_editTemplate
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_editTemplate( $p_template ){
	echo( pbts_writeCSS());
	echo( "\n<div class=\"pbts_xtra\">" );
	pbts_pgTop($p_template, 'edit' );
	if ($_POST['doaction']=='save'){
		Sql_query( 
			sprintf( 'UPDATE %s SET title="%s", template= "%s" WHERE (id = %d)',
				$GLOBALS["tables"]["template"], 
				addslashes($_POST['title']),
				addslashes($_POST['template']),
				$p_template  ));
		echo( "\n<p class=\"pbtswarning\">Saved at ". date("h:i:s a")."</p>");
		} // if save

	$req= Sql_query( sprintf( 'SELECT title, template FROM %s WHERE (id = %d)', $GLOBALS["tables"]["template"], $p_template ));
	while ($row= Sql_Fetch_Array($req)){
	  $f_title    =stripslashes($row['title']);
 	 	$f_template =stripslashes($row['template']); 
		}

?>
<form action="<?php echo(PBTS_URL_TEMPLATE) ?>&tab=edit&id=<?php echo( $p_template ); ?>" method="post">
<p><input type="submit" name="submit"  value=" Save! " /></p>
<input type="hidden" name="doaction" value="save" />
<input type="hidden" name="templateid" value="<?php echo( $p_template ); ?>" />
<table cellspacing="0" class="pbts_edit">
	<tr>
		<td>Title:</td>
		<td><input type="text" name="title" value="<?php echo( $f_title ); ?>" size="60" class="pbts" /></td>
		</tr>
	<tr><td colspan="2">Template</td></tr>
	<tr><td colspan="2"><textarea name="template" class="pbts" rows="50" cols="100"><?php echo( htmlspecialchars(stripslashes( $f_template ))); ?></textarea></td></tr>
</table>
</form>
<?php

	echo( "\n</div><!-- pbts_xtra -->" );

	} // pbts_editTemplate


// ==================================================================
// FUNCTION: pbts_previewForm
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_previewForm( $p_template ){
	global $g_pbts_html_content;
	global $g_pbts_template;
	global $g_pbts_errors;
	
	echo( pbts_writeCSS());
	echo( "\n<div class=\"pbts_xtra\">" );
	pbts_pgTop($p_template, 'preview form' );
	pbts_writeTemplateName( $p_template );

	$g_pbts_template= pbts_readTemplate( $p_template );
	if (strlen( $g_pbts_template) < 1)
		echo( "<p>Failed to read Template</p>" );

	if (!pbts_findTemplateTags())
		echo( "<p>no PBTS tags found in Template</p>" );

	if ($g_pbts_errors != '')
		echo( "\n<p class=\"pbtswarning\">You have left some required fields empty.<br />$g_pbts_errors" ); 

	echo(pbts_emitFields(false));

	echo( "\n</div><!-- pbts_xtra -->" );

	} // pbts_previewForm


// ====================================================================================================
//         M A I N
// ====================================================================================================
	include( "pbts_xtra.inc.php" );
	$g_templateId= $_GET['id'];

	switch ($_GET['tab']){
		case 'edit':
			pbts_editTemplate( $g_templateId );
			break;
		case 'form':
			pbts_previewForm( $g_templateId );
			break;
		case 'tags':
			pbts_showTags( $g_templateId );
			break;
		case 'highlight':
			pbts_highlight( $g_templateId );
			break;
		case 'tagger':
			pbts_editTags( $g_templateId );
			break;
		default:
			pbts_listTemplates();
		} // switch
	
?>