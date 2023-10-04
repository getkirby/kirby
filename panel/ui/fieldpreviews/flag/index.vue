<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			:value="{ status: 'listed' }"
			label="Listed"
			type="flag"
		/>
		<k-ui-field-preview-example
			:value="{ status: 'unlisted' }"
			label="Unlisted"
			type="flag"
		/>
		<k-ui-field-preview-example
			:value="{ status: 'draft' }"
			label="Draft"
			type="flag"
		/>
		<k-ui-field-preview-example label="Empty" type="flag" />
	</k-ui-examples>
</template>
