<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-info-field label="Info" />
		</k-ui-example>
		<k-ui-example label="Text">
			<k-info-field label="Info" text="This is some info text" />
		</k-ui-example>
		<k-ui-example label="Theme">
			<k-info-field
				label="Info"
				text="This is some info text"
				theme="positive"
			/>
		</k-ui-example>
		<k-ui-example label="Help">
			<k-info-field
				help="This is some help text"
				label="Info"
				text="This is some info text"
				theme="text"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>
