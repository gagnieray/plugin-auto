{assign var='name' value=$obj->name}
{assign var='pk' value=$obj->pk}
{assign var='field' value=$obj->field}
		<h1 id="titre">{$title}</h1>

		<div class="bigtable">
{if $name eq 'brands'}
	{if $models|@count gt 0}
			<p>{php}global $obj;echo preg_replace("/%s/", $obj->value, _T("Registered models for the brand '%s':"));{/php}</p>
			<ul>
		{foreach item=model from=$models}
				<li><a href="models.php?id={$model->id_model}">{$model->model}</a></li>
		{/foreach}
			</ul>
			<p><a href="models.php?donew=1&amp;brand={$obj->id}">{php}global $obj;echo preg_replace("/%s/", $obj->value, _T("Create a new model for brand '%s'"));{/php}</a></p>
	{else}
			<p>{php}global $obj;echo preg_replace("/%s/", $obj->value, _T("The brand '%s' does not have any registered model at this time."));{/php}<br/><a href="models.php?donew=1&amp;brand={$obj->id}">{_T string="Do you want to create a new one?"}</a></p>
	{/if}
{/if}
		</div>