{*
Template incluso.
Menu a SX valido per tutte le pagine del controller.
*}


<div class="secondacolonna {if !empty($fixed)}fixed{/if}">
	
	<div class="modules">
	   <label class="{$moduleName}" rel="{$html->url('/')}{$session->read("backFromView")}">{t}{$currentModule.label}{/t}</label>
	</div> 
	

	
	{assign var="user" value=$session->read('BEAuthUser')}
	
	{if $method == "view" && $module_modify eq '1'}
	
	<div class="insidecol">
		{if ($perms->isWritable($user.userid,$user.groups,$object.Permissions))}
		<input class="bemaincommands" type="button" value=" {t}save{/t} " name="save" />
		<input class="bemaincommands" type="button" value=" {t}clone{/t} " name="clone" />
		{/if}
		{if ($perms->isDeletable($user.userid,$user.groups,$object.Permissions))}
		<input class="bemaincommands" type="button" value="{t}delete{/t}" name="delete" id="delBEObject" />
		{/if}
	</div>
	
	{if $session->read("prevNext") && !empty($object.id)}
		{assign var="prevNext" value=$session->read("prevNext")}
		<div class="listobjnav">
			{if $prevNext[$object.id].prev}
			<a title="prev" href="{$html->url('/')}{$currentModule.path}/view/{$prevNext[$object.id].prev}">
				‹
			</a>
			{/if}
	
			{if $prevNext[$object.id].next}
			<a title="next" href="{$html->url('/')}{$currentModule.path}/view/{$prevNext[$object.id].next}">
				›
			</a> 
			{/if}
		</div>
	{/if}
	
	{elseif $method == "index"}
	

		{if !empty($categories)}
		<ul class="menuleft insidecol">
			<li><a href="javascript:void(0)" onClick="$('#groups').slideToggle();">{t}Select by category{/t}</a></li>
				
				<ul id="groups" {if (empty($categorySearched))}style="display:none"{/if}>
					{foreach key=val item=cat from=$categories}
					<li {if (($categorySearched|default:'')==$cat.id)}class="on"{/if}><a href="{$html->url('/addressbook/index/category:')}{$cat.id}">{$cat.label}</a></li>
					{/foreach}
				</ul>
		</ul>
		{/if}
	{/if}



</div>

