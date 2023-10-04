<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			label="Default"
			type="text"
			value="Lorem ipsum dolor sit amet"
		/>
		<k-ui-field-preview-example
			:column="{ before: '€' }"
			label="Before"
			type="text"
			value="10"
		/>
		<k-ui-field-preview-example
			:column="{ after: '€' }"
			label="After"
			type="text"
			value="10"
		/>
		<k-ui-field-preview-example
			label="Overflow"
			type="text"
			value="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus."
		/>
		<k-ui-field-preview-example label="Empty" type="text" />
	</k-ui-examples>
</template>
