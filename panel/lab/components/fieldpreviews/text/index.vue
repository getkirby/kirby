<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			label="Default"
			type="text"
			value="Lorem ipsum dolor sit amet"
		/>
		<k-lab-field-preview-example
			:column="{ before: '€' }"
			label="Before"
			type="text"
			value="10"
		/>
		<k-lab-field-preview-example
			:column="{ after: '€' }"
			label="After"
			type="text"
			value="10"
		/>
		<k-lab-field-preview-example
			label="Overflow"
			type="text"
			value="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus."
		/>
		<k-lab-field-preview-example label="Empty" type="text" />
	</k-lab-examples>
</template>
