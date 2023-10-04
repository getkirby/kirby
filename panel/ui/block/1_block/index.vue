<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-block />
		</k-ui-example>
		<k-ui-example label="Icon">
			<k-block :fieldset="{ icon: 'image' }" />
		</k-ui-example>
		<k-ui-example label="Icon & Label">
			<k-block :fieldset="{ icon: 'image', label: 'Image' }" />
		</k-ui-example>
		<k-ui-example label="Selected">
			<k-block :is-selected="true" />
		</k-ui-example>
		<k-ui-example label="Selected with Options">
			<k-block
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
			/>
		</k-ui-example>
		<k-ui-example label="Batched">
			<k-block :is-batched="true" :is-selected="true" />
		</k-ui-example>
		<k-ui-example label="Batched with options">
			<k-block
				:is-batched="true"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>
