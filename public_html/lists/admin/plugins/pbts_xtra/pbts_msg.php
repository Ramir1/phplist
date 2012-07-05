<?php
/*
Plugin Name: PBTS Extra
Plugin URI:  http://pizzabytheslice.com/phplist/pbts_xtra/
Description: Enables Templates to have multiple content regions and conditional inclusion (and exclustion) of areas based on content availability.
Author:      Courts Carter
Version:     0.05 [beta]
Author URI:  http://pizzabytheslice.com/

-----------  ---------------------------------------------------
This file:   pbts_msg.php
Does:        Emits Message Editor, Preview, Source. If no URL parms
             are included this lists all Draft Emails

*/

// ==================================================================
// FUNCTION: pbts_pgTop
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_pgTop($p_id, $p_selected){
	$tabs = new WebblerTabs();
	$tabs->addTab('edit',PBTS_URL_MSG."&tab=edit&id=".$p_id);
	$tabs->addTab('edit raw',PBTS_URL_MSG."&tab=raw&id=".$p_id);
	$tabs->addTab('preview',PBTS_URL_MSG."&tab=preview&id=".$p_id);
	$tabs->addTab('view source',PBTS_URL_MSG."&tab=source&id=".$p_id);
	$tabs->setCurrent($p_selected);

	pbts_pluginLinks();
	print $tabs->display();
	echo( "<p><a href=\"?page=send&id=". $p_id . "\">Edit this Message with standard phpList Editor (scheduling, etc.) &raquo;</a></p>"	);


	} // pbts_pgTop


// ==================================================================
// FUNCTION: pbts_writeTemplateSelect
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_writeTemplateSelect( $p_selected_id ){
	echo( "\n<select name=\"junk\">\n<option value=0>-- ".$GLOBALS['I18N']->get('selectone').'</option>' );
	$req = Sql_Query( "select id, title from ".$GLOBALS["tables"]["template"]." order by listorder" );
	while ($row = Sql_Fetch_Array($req)) {
		echo( sprintf('<option value="%d" %s>%s</option>',$row["id"], $row["id"]==$p_selected_id?'SELECTED':'', $row["title"] ));
		}// while
	echo ('</select> (not enabled yet)');
	} // pbts_writeTemplateSelect



// ==================================================================
// FUNCTION: pbts_saveMsg
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_saveMsg(){
  global $g_subject;
  global $g_fromfield; 
	global $g_pbts_html_content;
  global $g_messageid;

  # $tables["message"]
	Sql_query( 
		sprintf( 'update %s set subject = "%s", fromfield = "%s", message = "%s", modified="%s" where (id = %d)',
      $GLOBALS["tables"]["message"], 
			addslashes($g_subject),
			addslashes($g_fromfield),
			addslashes($g_pbts_html_content),
			date("Y-m-d H:i:00"),
			$g_messageid  ));

	} // pbts_saveMsg


// ==================================================================
// FUNCTION: pbts_readMsg
// DOES:     sets global variables with contents of Msg
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_readMsg($p_id ){
  global $g_subject;
  global $g_fromfield; 
  global $g_message;
  global $g_templateid;

  $g_subject    ='';
  $g_fromfield  =''; 
  $g_message    ='';
  $g_templateid ='';

	$req = Sql_Query( 'select subject, fromfield, message, template from '.$GLOBALS["tables"]["message"].' WHERE (id='.$p_id.')' );
	while ($row = Sql_Fetch_Array($req)){
	  $g_subject    =stripslashes($row['subject']);
 	 	$g_fromfield  =stripslashes($row['fromfield']); 
  	$g_message    =stripslashes($row['message']);
  	$g_templateid =stripslashes($row['template']);
		}

	}// pbts_readMsg


// ==================================================================
// FUNCTION: pbts_listDrafts
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_listDrafts(){
	echo( pbts_writeCSS());

?>
<div class="pbts_xtra">
<?php	pbts_pluginLinks(); ?>
<h2>Draft Messages</h2>
<table cellspacing="0">
	<thead>
		<tr>
			<th>Subject</th>
			<th>From</th>
			<th>Entered</th>
			<th>Modified</th>
			<th>Action</th>
			</tr>
	</thead>
	<tbody>
<?php
	$req = Sql_Query( 'select id, subject, fromfield, entered, modified from '.$GLOBALS["tables"]["message"].' ORDER BY modified desc' );
	while ($row = Sql_Fetch_Array($req)){
		echo( "\n<tr><td>".
			stripslashes($row['subject'])."</td><td>".
			stripslashes($row['fromfield'])."</td><td>".
			stripslashes($row['entered'])."</td><td>".
			stripslashes($row['modified'])."</td><td>".
			"<a href=\"". PBTS_URL_MSG."&tab=edit&id=".stripslashes($row['id'])."\">edit</a><br >".
			"<a href=\"". PBTS_URL_MSG."&tab=preview&id=".stripslashes($row['id'])."\">preview</a></td>".
			"</tr>" );
		}
?>
	</tbody>
</table>
</div><!-- pbts_xtra --->
<?php

	}// pbts_listDrafts


// ==================================================================
// FUNCTION: pbts_editMsg
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_editMsg(){
	global  $g_pbts_errors;
	global  $g_pbts_html_content;
	global  $g_pbts_template;

	global	$g_messageid ;
	global	$g_subject   ;
	global	$g_fromfield ; 
	global	$g_message   ;
	global	$g_templateid;


	if (isset($_POST['doaction']))
		$f_doAction  = $_POST['doaction'];
	else
		$f_doAction  = 'read';

	if ($f_doAction == 'save'){
		$g_messageid  = $_POST['messageid'];
		$g_subject    = stripslashes($_POST['subject']);
		$g_fromfield  = stripslashes($_POST['fromfield']); 
		$g_templateid = stripslashes($_POST['templateid']);

		$g_pbts_template= pbts_readTemplate( $g_templateid );
		pbts_findTemplateTags();
		pbts_handlePost();
		pbts_saveMsg();

	} else {
		if (isset($_GET['id']))
			$g_messageid=  $_GET['id'];
		else
			$g_messageid=  0;
		$g_subject    = '';
		$g_fromfield  = ''; 
		$g_message    = '';
		$g_templateid = '';
	
		pbts_readMsg( $g_messageid );

		$g_pbts_template= pbts_readTemplate( $g_templateid );
		pbts_findTemplateTags();

		$g_pbts_html_content= $g_message;
		pbts_parseMsgBody();
	} // if


	// Time to emit the page...
	echo( pbts_writeCSS());

?>
<div class="pbts_xtra">
<?php 

	pbts_pgTop( $g_messageid, 'edit' ); 

	if ($f_doAction == 'save')
		echo( "\n<p class=\"pbtswarning\">Saved at ". date("h:i:s a")."</p>");
	
?>
<form action="<?php echo(PBTS_URL_MSG) ?>&tab=edit&id=<?php echo( $g_messageid ); ?>" method="post">
<input type="submit" name="submit"  value=" Save! " />
<input type="hidden" name="doaction" value="save" />
<input type="hidden" name="messageid" value="<?php echo( $g_messageid ); ?>" />
<input type="hidden" name="templateid" value="<?php echo( $g_templateid ); ?>" />
<table cellspacing="0" class="pbts_edit">
	<tr>
		<td>Subject:</td>
		<td><input type="text" name="subject" value="<?php echo( $g_subject ); ?>" size="60" class="pbts" /></td>
		</tr>
	<tr>
		<td>From Line:</td>
		<td><input type="text" name="fromfield" value="<?php echo( $g_fromfield ); ?>" size="60" class="pbts" /></td>
		</tr>
	<tr>
		<td>Template:</td>
		<td><?php pbts_writeTemplateSelect( $g_templateid ); ?></td>
		</tr>
	<tr><td colspan="2">
<?php
	if ($g_pbts_errors != '')
		echo( "\n<p class=\"pbtswarning\">You have left some required fields empty.<br />$g_pbts_errors</p>" ); 

	if (strlen($g_pbts_template) <1)
		echo( "\n<p class=\"pbtswarning\">The Template could not be loaded. Template ID is \"$g_templateid\".</p>" ); 

	echo( pbts_emitFields(true));

?>
	</td></tr>
</table>
	<h3>Combined Content </h3>
	<p>The information below is for amusement only; no gambling, please.</p>
	<p><textarea name="g_pbts_html_content" rows="50" class="pbtsoutput"><?php echo( htmlspecialchars(stripslashes( $g_pbts_html_content ))); ?></textarea></p>
</form>

</div><!-- pbts_xtra --->
<?php
	} // pbts_editMsg


// ==================================================================
// FUNCTION: pbts_view_source
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_view_source( $p_messageid ){
	global  $g_pbts_errors;
	global  $g_pbts_html_content;
	global  $g_pbts_template;

	global	$g_messageid ;
	global	$g_subject   ;
	global	$g_fromfield ; 
	global	$g_message   ;
	global	$g_templateid;

	echo( pbts_writeCSS());
	?>
<style type="text/css">
.pbts_source{
  width: 740px;
	height: 450px;
	border: 1px solid #000033;
	padding: 6px;
	background-color: #fff;
	overflow:scroll;
	}
.pbts_source pre,
.pbts_source{
  font-family:Arial, Helvetica, sans-serif;
	font-size: 11px;
	line-height: 1.2em;
	}
</style>
<div class="pbts_xtra">
<?php pbts_pgTop( $p_messageid, 'view source' ); ?>
<div class="pbts_source">
<pre>&lt;!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"&gt;
&lt;html&gt;
&lt;head&gt;
	&lt;meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"&gt;
	&lt;title&gt;Page Source&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
<?php

	$g_messageid= $p_messageid;
	pbts_readMsg( $g_messageid );

	$g_pbts_html_content= $g_message;

	$g_pbts_template= pbts_readTemplate( $g_templateid );
	if (!pbts_findTemplateTags())
		echo( "\n<h2>Warning!</h2>\n<h1>no PBTS tags found in this Template</h1>" );
	else {
		if (strlen($g_pbts_html_content) > 0){
		  $g_pbts_html_content= htmlspecialchars(stripslashes(pbts_merge_html_content()));
			$g_pbts_html_content= preg_replace( "/\t/", "  ", $g_pbts_html_content );
			echo( $g_pbts_html_content );
			}
	} // if template tags found

?>
&lt;/body&gt;
&lt;/html&gt;
</pre>
</div><!-- pbts_source -->
</div><!-- pbts_xtra --->
<?php
	} // pbts_view_source


// ==================================================================
// FUNCTION: pbts_preview_pg
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_preview_pg( $p_messageid ){
	echo( pbts_writeCSS());
	?>
<div class="pbts_xtra">
<?php pbts_pgTop( $p_messageid, 'preview' ); ?>
<iframe src="<?php echo( PBTS_URL_MSG .'&omitall=yes&tab=embed&id='.$p_messageid ); ?>" scrolling="auto" width=100% height="450" margin=0 frameborder=0></iframe>
</div><!-- pbts_xtra --->
<?php
	} // pbts_preview_pg


// ==================================================================
// FUNCTION: pbts_preview_embed
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_preview_embed( $p_messageid ){
	global  $g_pbts_errors;
	global  $g_pbts_html_content;
	global  $g_pbts_template;

	global	$g_messageid ;
	global	$g_subject   ;
	global	$g_fromfield ; 
	global	$g_message   ;
	global	$g_templateid;
	# need to suppress the phpList header, sidebar, etc..

	ob_end_clean();
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Sample</title>
</head>
<body>
<?php

	$g_messageid= $p_messageid;
	pbts_readMsg( $g_messageid );

	$g_pbts_html_content= $g_message;

	$g_pbts_template= pbts_readTemplate( $g_templateid );
	if (!pbts_findTemplateTags())
		echo( "\n<h2>Warning!</h2>\n<h1>no PBTS tags found in this Template</h1>" );
	else {
		if (strlen($g_pbts_html_content) > 0){
			echo( pbts_merge_html_content());
			}
	} // if template tags found

?>
</body>
</html>
	<?php
	} // pbts_preview_embed


// ==================================================================
// FUNCTION: pbts_editRaw
// DOES:     
// RETURNS:  
// PARAMS:   
// ==================================================================
function pbts_editRaw( $p_id ){
	echo( pbts_writeCSS());

	echo( "\n<div class=\"pbts_xtra\">" );
	pbts_pgTop($p_id, 'edit raw' );

	if ($_POST['doaction']=='save'){
		Sql_query(
			sprintf( 
				'update %s set subject = "%s", fromfield = "%s", message = "%s", modified="%s" where (id = %d)',
				$GLOBALS["tables"]["message"], 
				addslashes($_POST['subject']),
				addslashes($_POST['fromfield']),
				addslashes($_POST['message']),
				date("Y-m-d H:i:00"),
				$p_id  ));

		echo( "\n<p class=\"pbtswarning\">Saved at ". date("h:i:s a")."</p>");
		} // if save

	$req= Sql_query( sprintf( 'SELECT subject, fromfield, template, message FROM %s WHERE (id = %d)', $GLOBALS["tables"]["message"], $p_id ));
	while ($row= Sql_Fetch_Array($req)){
	  $f_subject    =stripslashes($row['subject']);
 	 	$f_fromfield  =stripslashes($row['fromfield']); 
  	$f_message    =stripslashes($row['message']);
  	$f_template   =stripslashes($row['template']);
		}

?>
<form action="<?php echo(PBTS_URL_MSG) ?>&tab=raw&id=<?php echo( $p_id ); ?>" method="post">
<p><input type="submit" name="submit"  value=" Save! " /></p>
<input type="hidden" name="doaction" value="save" />
<input type="hidden" name="messageid" value="<?php echo( $p_id ); ?>" />
<table cellspacing="0" class="pbts_edit">
	<tr>
		<td>Subject:</td>
		<td><input type="text" name="subject" value="<?php echo( $f_subject ); ?>" size="60" class="pbts" /></td>
		</tr>
	<tr>
		<td>From Line:</td>
		<td><input type="text" name="fromfield" value="<?php echo( $f_fromfield ); ?>" size="60" class="pbts" /></td>
		</tr>
	<tr>
		<td>Template:</td>
		<td><?php pbts_writeTemplateSelect( $f_template ); ?></td>
		</tr>
	<tr><td colspan="2">Message</td></tr>
	<tr><td colspan="2"><textarea name="message" class="pbts" rows="50" cols="100"><?php echo( htmlspecialchars(stripslashes( $f_message ))); ?></textarea></td></tr>
</table>
</form>
<?php

	echo( "\n</div><!-- pbts_xtra -->" );

	} // pbts_editRaw



// ====================================================================================================
//         M A I N
// ====================================================================================================
	include( "pbts_xtra.inc.php" );

	switch ($_GET['tab']){
		case 'embed':
		  pbts_preview_embed( $_GET['id']);
			break;
		case 'preview':
			pbts_preview_pg( $_GET['id']);
			break;
		case 'edit':
			pbts_editMsg();
			break;
		case 'raw':
			pbts_editRaw($_GET['id']);
			break;
		case 'source':
			pbts_view_source($_GET['id']);
			break;
		default:
			pbts_listDrafts();
		} // switch

?>