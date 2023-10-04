<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="Default">
			<k-block-options />
		</k-ui-example>
		<k-ui-example :flex="true" label="Hidden">
			<k-block-options :is-hidden="true" />
		</k-ui-example>
		<k-ui-example :flex="true" label="Editable">
			<k-block-options :is-editable="true" />
		</k-ui-example>
		<k-ui-example :flex="true" label="Full">
			<k-block-options :is-full="true" />
		</k-ui-example>
		<k-ui-example :flex="true" label="Splitable">
			<k-block-options :is-splitable="true" />
		</k-ui-example>
		<k-ui-example :flex="true" label="Batched">
			<k-block-options :is-batched="true" />
		</k-ui-example>
		<k-ui-example :flex="true" label="Batched & mergable">
			<k-block-options :is-batched="true" :is-mergable="true" />
		</k-ui-example>
	</k-ui-examples>
</template>
