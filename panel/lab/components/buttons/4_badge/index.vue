<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button :badge="badge">This is a very long text</k-button>
			<k-button :badge="badge" icon="edit">Button</k-button>
			<k-button :badge="badge" icon="edit" />
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: filled">
			<k-button :badge="badge" variant="filled">Button</k-button>
			<k-button :badge="badge" variant="filled" icon="edit">Button</k-button>
			<k-button :badge="badge" variant="filled" icon="edit" />
		</k-lab-example>
		<k-lab-example :flex="true" label="dropdown">
			<k-button :badge="badge" variant="filled" :dropdown="true">
				Button
			</k-button>
			<k-button :badge="badge" variant="filled" :dropdown="true" icon="edit">
				Button
			</k-button>
			<k-button :badge="badge" variant="filled" :dropdown="true" icon="edit" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			badge: {
				theme: "positive",
				text: 5
			}
		};
	}
};
</script>
