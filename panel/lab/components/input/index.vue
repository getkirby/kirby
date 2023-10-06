<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-input type="text" />
		</k-lab-example>
		<k-lab-example label="Before & After">
			<k-input after="After" before="Before" type="text" />
		</k-lab-example>
		<k-lab-example label="Icon">
			<k-input icon="edit" type="text" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-input
				:disabled="true"
				after="After"
				before="Before"
				icon="edit"
				type="text"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
