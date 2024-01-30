<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block type="table" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block :disabled="true" type="table" />
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-examples {
	padding-bottom: 100rem;
}
</style>
