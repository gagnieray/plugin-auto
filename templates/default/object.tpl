{assign var='name' value=$obj->name}
{assign var='pk' value=$obj->pk}
{assign var='field' value=$obj->field}

		<form action="" method="post" id="modifform">
{if $error_detected|@count != 0}
		<div id="errorbox">
			<h1>{_T string="- ERROR -"}</h1>
			<ul>
{foreach from=$error_detected item=error}
				<li>{$error}</li>
{/foreach}
			</ul>
		</div>
{/if}
		<div class="bigtable">
			<fieldset class="cssform">
				<p>
					<label for="{$field}" class="bline">{$field_name}</label>
					<input type="text" name="{$field}" id="{$field}" value="{$obj->value}" maxlength="20" required autofocus/>
				</p>
			</fieldset>
		</div>
		<div class="button-container">
			<input type="submit" id="btnsave" name="valid" value="{_T string="Save"}"/>
			<input type="reset" id="btncancel" name="cancel" value="{_T string="Cancel"}"/>
			<input type="hidden" name="set" value="{$set}"/>
			<input type="hidden" name="{$mode}" value="1"/>
			<input type="hidden" name="{$pk}" value="{$obj->$pk}"/>
		</div>
		<p>{_T string="NB : The mandatory fields are in"} <span class="required">{_T string="red"}</span></p>
		</form>