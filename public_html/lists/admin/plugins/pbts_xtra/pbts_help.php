<?php
/*
Plugin Name: PBTS Help
Plugin URI:  http://pizzabytheslice.com/phplist/pbts_xtra/
Description: Enables Templates to have multiple content regions and conditional inclusion (and exclustion) of areas based on content availability.
Author:      Courts Carter
Version:     0.05 [beta]
Author URI:  http://pizzabytheslice.com/

-----------  ---------------------------------------------------
This file:   pbts_help.php
Does:        Moved all of the tips, how-tos, and about info to this one file.

*/ 


// ====================================================================================================
//         M A I N
// ====================================================================================================
	include( "pbts_xtra.inc.php" );


	echo( pbts_writeCSS());

	echo( "\n<div class=\"pbts_xtra\">" );
	pbts_pluginLinks();

?>
<p>Topics: <a href="#overview">Overview</a> || <a href="#basic">Basic Usage</a> || <a href="#advanced">Advanced Options</a> || 
	<a href="#conditions">Conditional Regions</a> || <a href="#faq">faq</a></p>


<h2><a name="overview"></a>Overview</h2>
<p>This plugin extends phpList's Template functionality by allowing you to define multiple Content Regions within your Templates.</p>
<p>You are also able to include or exclude sections of your Template based on whether one of these Content Regions is empty or not.</p>
<p><a href="#">top</a></p>
<hr>


<h2><a name="basic"></a>Basic Usage</h2>
<h3>Your Template</h3>
<p>Begin by creating your template. Wherever you wish to insert a Content Region do so by entering the PBTS_XTRA tag:</p>
<pre>
[PBTS_XTRA name="MyNewColumn"]
</pre>
<p>There are more options and features to be covered shortly, but to just get started using this plugin that's all you need to do.</p>
<p>Frequently alternative methods are supported by the plugin. On such alternative (shorthand way) of defining a Custom Region is:</p>
<pre>
[PBTS_MyNewColumn]
</pre>
<p>Either way informs the plugin that the Template has a Custom Region named "MyNewColumn".</p>


<h3>Message Content</h3>
<p>To use your Template in a mailing all you need to is create a Message in the usual phpList way with the additional step of marking portions of the Content to match your custom Content Regions.</p>
<p>Backstory: phpList has exactly one Content "chunk" per email. Thus, we need to subdivide this Content by marking the beginning and ending of our custom Content Regions. We do this thusly:</p>
<p>Now, doing this by hand might be tedious and error prone. Also, you might overlook some of your Template's Content Regions. To mitigate this a bit pbts_xtra offers it's own editor. This editor reads your Template and creates an entry field for each Content Region. </p>
<p><a href="#">top</a></p>
<hr>


<h2><a name="advanced"></a>Advanced Topics: More Options</h2>
<p>pbts_Xtra tags have several parameters (straight from HTML form tags) that allow you to control the auto-generated Message Editor. For example, this would be in your Template:</p>
<pre>
[PBTS_XTRA name="MyNewColumn" displayname="Joke of the Day" </p>
	 type="textarea" rows="6" cols="45"]
</pre>

<p>This will cause the Editor to, you guessed it, emit a textarea for MyNewColumn with 6 rows and 45 columns. The label next to it will be Joke of the Day.</p>
<p>Other parameters allow you to set the order in which the fields are emitted and what kind of formatted pbts_xtra will automatically apply to the values entered.</p>
<p>Oh, curious about what happens to the values supplied for all of the fields, are you? Sure.</p>
<p>Well, no new tables or columns are added for pbts_xtra. Instead all of the values are tagged (begin and end markers added) and all stored together in the same single Content area used for normal phpList Messages. (yes, completely denormalized, but quite backwards compatibalbe).</p>
<p>Using the example begun above, the field "myNewColumn" would be marked-up thusly:</p>
<pre>
[PBTS_XTRA name="MyNewColumn"]
Here is the content for a block of stuff called MyNewColumn.
[/PBTS_XTR]
</pre>
<table cellspacing="0">
	<thead>
		<tr>
			<th>parameter</th>
			<th>description</th>
			</tr>
		</thead>
	<tbody>
		<tr>
			<td>Name</td>
			<td>Required (sort of). Any non-reserved word, made up of alphnumeric characters (a-z, 0-9), dashes, and underscores.</td>
			</tr>
		<tr>
			<td>DisplayName</td>
			<td>Name displayed to Content Creator within the Editor</td>
			</tr>
		<tr>
			<td>Type</td>
			<td>HTML Form Field Type. Allowed values: Text or Textarea. Default is Text.</td>
			</tr>
		<tr>
			<td>Paragraph</td>
			<td>Allowed values "always" or "never". Should the content for this Content Region be wrapped in &lt;p&gt; (paragraph) tags. For example, a large block of text edited in a Textarea typically has the double-newlines replaced with paragraph tags.
			  <br />Conversely, if one of the Content Regions is used as a URL in, say, an image tag or anchor tag, then you would want to set this to "never" to have all paragraph tags stripped.</td>
			</tr>
		<tr>
			<td>Description</td>
			<td>For Editor any help you might want to include. Please note, until I beef-up the Regular Expressions this needs to following the same rules as the Name for allowed characters.</td>
			</tr>
		<tr>
			<td>Rows</td>
			<td>Used by Textareas.</td>
			</tr>
		<tr>
			<td>Cols</td>
			<td>Used by Textareas.</td>
			</tr>
		<tr>
			<td>Size</td>
			<td>Used by inputs of type Text. Field size.</td>
			</tr>
		<tr>
			<td>Required</td>
			<td>For Editor. If set to yes and the field is left blank errors are produced. Allowed values "yes" or "no". Defaults to yes.</td>
			</tr>
		<tr>
			<td>TabIndex</td>
			<td>For Editor. Set the tabing order and the order in which fields are emitted in the Editor. Default is the order in which they appear within the Template source.</td>
			</tr>
	</tbody>
</table>
<p>Note, that all of the arguments added for Editor generation are completely optional: all you need to do is provide the Enhancement with a means of differentiating the blocks, i.e. just provide each Custom Area with a unique name. All else is gravy.</p>

<p>I’m also supporting alternate syntaxes (easier than it sounds since I worked from the code I initially had and being lazy found it just as easy to leave some features in).</p>

<p>For example, if you don’t like:</p>

<pre>
[PBTS_XTRA name="MyNewColumn"]
</pre>


<p>An alternate, and valid, way to define or mark blocks is:</p>
<pre>
[PBTS_MyNewColumn]
</pre>

<p>Behind the scenes it parses the template, no, it doesn’t parse it, the Template is scanned for tags that begin "PBTS_".</p>

<p><a href="#">top</a></p>
<hr>


<h2><a name="conditions"></a>Advanced Topics: Conditional Regions</h2>
<p>PBTS_Xtra supports conditionally including or excluding sections of the email, based on the presence of a particular Content Region.</p>

<p>For example, say your email Template has  an Urgent_Action block that usually has some call to action for your list members. Usually, but not always. Well, rather than creating two templates, instead, just have one that’s smart enough to recognize that if the Urgent_Action Content Region is empty don’t emit that section's block:</p>
<pre>
[pbts_if condition="not_blank" name="Urgent_Action"]
  &lt;h5&gt;Urgent Action&lt;/h5&gt;
  [pbts_xtra name="Urgent_Action"]
  &lt;p&gt;For how you can get involved contact Bob …&lt;/p&gt;
[/pbts_if]

&lt;p>Just a blank line between ifs&lt;/p&gt;

[pbts_if condition="empty" name="Urgent_Action"]
  &lt;h5&gt;No Urgent Actions This Month&lt;/h5&gt;
[/pbts_if]
</pre>

<p>The syntax is hopefully vaguely familiar. You have two tags that mark the block. The block begins with the pbts_if and ends with the [/pbts_if]. Please, NO NESTING OF IFs.  </p>
<p>The two arguments allowed in the if statement are the desired condition and the name of the Content Region to be checked. The conditons supported at this time are only whether the named Content Region is blank or not. </p>
<p>ELSE statements are not offered. </p>
<p>As with the other stuff variants are supported. The following means "the section is not blank":</p>

<pre>
Condition="defined"
</pre>

<p>These are alternate means of saying the same thing:</p>
<ul>
	<li>"defined"</li>
	<li>"is set"</li>
	<li>"not blank"</li>
	<li>"not empty"</li>
</ul>

<p>Also note that the two word values, such as "is defined" might include (optionally, of course) a dash or underscore between "is" or "not. That is, these are synonymous:</p>
<ul>
	<li>"defined"</li>
	<li>"is defined"</li>
	<li>"is_defined"</li>
	<li>"is-defined"</li>
	<li>"isdefined"</li>
</ul>

<p>It is important to note that <strong>NESTED IFs are NOT SUPPORTED</strong>.</p>
<p>HHH</p>

<p><a href="#">top</a></p>
<hr>

<h2><a name="faq"></a>faq</h2>
<p class="faq_q">What is PBTS?</p>
<p class="faq_a">PBTS? That'd be "Pizza By The Slice". It's a habit. I always prefix my mods with my website name to make it easier to find my edits. Oh, sometimes I'll use my initials, too.</p>
<p class="faq_a">If you wish to contact me (Courts|Buz) Carter just mosey over to <a href="http://www.pizzabytheslice.com" target="_blank">pizzabytheslice.com</a>.</p>
<p class="faq_q">Why do the [PBTS_XTRA] tags get wrapped in Paragraph Tags?</p>
<p class="faq_a">This is perhaps an unneeded bit of legacy code, but the phpList Message Editor liked to see things all neatly wrapped in &lt;p&gt; tags, so figured "hey, I'll do it myself and maintain some control". Regardless of whether or not they are present they are stripped before Emails are sent.</p>
<p class="faq_q">Within a Template may I repeat Tag names?</p>
<p class="faq_a">Yes. However, the editor uses the arguments set with the first occurene of a specific tag name. But yes, sprinkly a given tag throughout your Template is allowed, and any Message using this Template will have the same value plugged into every occurence of this tag.</p>
<p><a href="#">top</a></p>
<hr>

</div><!-- pbts_xtra -->
