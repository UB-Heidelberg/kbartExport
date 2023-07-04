{**
 * templates/settings.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Copyright (c) 2023 Heidelberg University Library
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

        {/fbvFormSection}

        {fbvFormSection label="plugins.generic.kbartExport.settings.downloadUrl"}
            {capture assign="kbartDownloadUrl"}{$kbartDownloadUrl}{/capture}
            {translate key="plugins.generic.kbartExport.settings.downloadUrl.description" kbartDownloadUrl=$kbartDownloadUrl}
        {/fbvFormSection}

        {fbvFormButtons submitText="common.save"}
    {/fbvFormArea}

</form>
