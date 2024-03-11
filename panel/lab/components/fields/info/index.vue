<template>
	<k-lab-examples class="k-lab-field-examples">
		<k-lab-example label="Default">
			<k-info-field label="Info" />
		</k-lab-example>

		<k-lab-example label="Text">
			<k-info-field label="Info" text="This is some info text" />
		</k-lab-example>

		<k-lab-example label="Theme">
			<k-info-field
				label="Info"
				text="This is some info text"
				theme="positive"
			/>
		</k-lab-example>

		<k-lab-example label="Help">
			<k-info-field
				help="This is some help text"
				label="Info"
				text="This is some info text"
				theme="text"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
