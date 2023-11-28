<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button>Button</k-button>
			<k-button icon="edit">Button</k-button>
			<k-button icon="edit" />
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: dimmed">
			<k-button variant="dimmed">Button</k-button>
			<k-button variant="dimmed" icon="edit">Button</k-button>
			<k-button variant="dimmed" icon="edit" />
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: filled">
			<k-button variant="filled">Button</k-button>
			<k-button variant="filled" icon="edit">Button</k-button>
			<k-button variant="filled" icon="edit" />
		</k-lab-example>
		<k-lab-example :flex="true" label="dropdown">
			<k-button variant="filled" :dropdown="true">Button</k-button>
			<k-button variant="filled" :dropdown="true" icon="edit">Button</k-button>
			<k-button variant="filled" :dropdown="true" icon="edit" />
		</k-lab-example>
	</k-lab-examples>
</template>
