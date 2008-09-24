{if ($conf->mce|default:true)}
	{$javascript->link("tiny_mce/tiny_mce")}
{literal}
<script language="javascript" type="text/javascript">

tinyMCE.init({
	// General options
	mode : "textareas",
	theme : "advanced",
	editor_selector : "mce",
	plugins : "safari,pagebreak,paste,fullscreen,template",

	// Theme options
	theme_advanced_buttons1 : "template,|,bold,italic,underline,strikethrough, | ,formatselect,bullist,numlist, hr, | ,link,unlink,pastetext,pasteword, | ,removeformat,charmap,code,fullscreen",
	theme_advanced_buttons2 : "sub,sup,fontsizeselect,forecolor,styleselect,justifyleft,justifycenter,justifyright,justifyfull",
	theme_advanced_buttons3 : "", 
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	//theme_advanced_resizing : true,
	theme_advanced_blockformats : "p,h1,h2,h3,h4,blockquote,address",
	width : "450",
	//http://wiki.moxiecode.com/index.php/TinyMCE:Control_reference
	
	// Example content CSS (should be your site CSS)
	content_css : "/css/htmleditor.css",
    relative_urls : false,
	convert_urls : false,
    remove_script_host : false,
	document_base_url : "/"
	
/*
<a href="#" onclick="tinyMCE.execCommand('Bold');return false;">[Bold]</a>
<a href="#" onclick="tinyMCE.execCommand('Italic');return false;">[Italic]</a>
<a href="#" onclick="tinyMCE.execCommand('mceInsertContent',false,'<b>Hello world!!</b>');return false;">[Insert some HTML]</a>
<a href="#" onclick="tinyMCE.execCommand('mceReplaceContent',false,'<b>{$selection}</b>');return false;">[Replace selection]</a>
 */

});





	</script>
{/literal}
{/if}


<div class="tab"><h2>{t}Compile{/t}</h2></div>

<fieldset id="contents">


	<label>{t}Title{/t}: </label>
	{assign_concat var="default" 0="Newsletter | " 1=$smarty.now|date_format:"%B %Y"}
	<input type="text" id="title" name="data[title]" 
	value="{$object.title|default:$default|escape:'html'|escape:'quotes'}" id="titleBEObject"/>
	
	<hr />

	<label>template :</label>
	<select>
				<option value="">--</option>
				<option>list of all templates</option>
				<option>grouped by publishing</option>
			</select>
	
	&nbsp;&nbsp;
		<input class="modalbutton" type="button" value="{t}Get contents{/t}" rel="{$html->url('/areas/showObjects/')}{$rel}" style="width:200px" />

	<hr />


	<ul class="htab">
		<li rel="html">HTML version</li>
		<li rel="txt">PLAIN TEXT version</li>
	</ul>
	
	<div class="htabcontainer" id="templatebody">
		
		<div class="htabcontent" id="html">
			<textarea id="htmltextarea" style="height:300px" class="mce"></textarea>
		</div>
		
		<div class="htabcontent" id="txt">
			<textarea style="height:300px; border:1px solid silver; width:450px" class="autogrowarea"></textarea>
		</div>
		
	</div>

		<br />
	<a href="#" onclick="tinyMCE.execCommand('mceInsertContent',false,'<b>Hello world!!<br /><hr /></b>');return false;">[TEST 4 Insert some HTML]</a>
	
	
	
</fieldset>
