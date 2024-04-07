
{$view->element('publications')}

<ul class="menuleft insidecol bordered">

{if !empty($BEAuthUser.userid)}
	<li>
		<a href="{$html->url('/home/profile/')}">{t}User{/t}: <span class="on">{$BEAuthUser.realname|escape}</span></a>
	</li>
{/if}

	<li><a href="{$html->url('/')}">{t}Home{/t}</a></li>
	{if !empty($BEAuthUser.userid)}
		<li><a href="{$html->url('/home/profile/')}">{t}Profile{/t}</a></li>
		<li><a href="{$html->url('/authentications/logout')}">{t}Exit{/t}</a></li>
	{/if}

</ul>

<ul class="menuleft insidecol bordered">
	{foreach key=key item=item name=l from=$conf->langsSystem}	
	<li><a {if $session->read('Config.language') == $key}class="on"{/if} href="{$html->base}/lang/{$key}">{$item}</a></li>
	{/foreach}
</ul>


{$view->element('colophon')}

{$conf->colophon|default:''}




