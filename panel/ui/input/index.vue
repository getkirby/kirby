<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-input type="text" />
		</k-ui-example>
		<k-ui-example label="Before & After">
			<k-input after="After" before="Before" type="text" />
		</k-ui-example>
		<k-ui-example label="Icon">
			<k-input icon="edit" type="text" />
		</k-ui-example>
		<k-ui-example label="Disabled">
			<k-input
				:disabled="true"
				after="After"
				before="Before"
				icon="edit"
				type="text"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>
