<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			:value="{ status: 'listed' }"
			label="Listed"
			type="flag"
		/>
		<k-lab-field-preview-example
			:value="{ status: 'unlisted' }"
			label="Unlisted"
			type="flag"
		/>
		<k-lab-field-preview-example
			:value="{ status: 'draft' }"
			label="Draft"
			type="flag"
		/>
		<k-lab-field-preview-example label="Empty" type="flag" />
	</k-lab-examples>
</template>
