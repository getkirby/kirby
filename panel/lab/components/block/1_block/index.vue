<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block />
		</k-lab-example>
		<k-lab-example label="Icon">
			<k-block :fieldset="{ icon: 'image' }" />
		</k-lab-example>
		<k-lab-example label="Icon & Label">
			<k-block :fieldset="{ icon: 'image', label: 'Image' }" />
		</k-lab-example>
		<k-lab-example label="Selected">
			<k-block :is-selected="true" />
		</k-lab-example>
		<k-lab-example label="Selected with Options">
			<k-block
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
			/>
		</k-lab-example>
		<k-lab-example label="Batched">
			<k-block :is-batched="true" :is-selected="true" />
		</k-lab-example>
		<k-lab-example label="Batched with options">
			<k-block
				:is-batched="true"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
			/>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block :disabled="true" :fieldset="{ icon: 'image', label: 'Image' }" />
		</k-lab-example>
	</k-lab-examples>
</template>
