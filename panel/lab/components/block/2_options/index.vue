<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Default">
			<k-block-options />
		</k-lab-example>
		<k-lab-example :flex="true" label="Hidden">
			<k-block-options :is-hidden="true" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Editable">
			<k-block-options :is-editable="true" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Full">
			<k-block-options :is-full="true" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Splitable">
			<k-block-options :is-splitable="true" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Batched">
			<k-block-options :is-batched="true" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Batched & mergable">
			<k-block-options :is-batched="true" :is-mergable="true" />
		</k-lab-example>
	</k-lab-examples>
</template>
