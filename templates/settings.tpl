{**
 * templates/settings.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the KBART Export plugin.
 *}
<script>
	$(function() {ldelim}
		$('#kbartExportPluginSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<script>
function myFunction() {
    alert("Hello world!");
}
</script>

<form
	class="pkp_form"
	id="kbartExportPluginSettings"
	method="POST"
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>

	<!-- Always add the csrf token to secure your form -->
	{csrf}

	{fbvFormArea}

		{fbvFormSection
            label="plugins.generic.kbartExport.settings.title"
            description="plugins.generic.kbartExport.settings.description"
            }

            <p>
			{fbvElement
                label="plugins.generic.kbartExport.settings.providerName"
				type="text"
				id="providerName"
				value=$providerName
                required="true"
			}
            </p>

            <p>
            {fbvElement
                label="plugins.generic.kbartExport.settings.regionConsortium"
				type="text"
				id="regionConsortium"
				value=$regionConsortium
                required="true"
			}
            </p>

            {fbvElement
                label="plugins.generic.kbartExport.settings.packageName"
				type="text"
				id="packageName"
				value=$packageName
                required="true"
			}

        {fbvFormButtons submitText="common.save"}

        {/fbvFormSection}

    {/fbvFormArea}

</form>

{$kbartDownloadUrl}
