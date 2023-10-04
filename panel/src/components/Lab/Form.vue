<template>
	<form class="k-ui-form" @submit.prevent="submit($event.target)">
		<slot />
		<footer>
			<k-button type="submit" icon="check" theme="positive" variant="filled">
				Submit
			</k-button>
		</footer>
	</form>
</template>

<script>
export default {
	methods: {
		submit(form) {
			const entries = Object.fromEntries(new FormData(form));

			this.$panel.dialog.open({
				component: "k-ui-output-dialog",
				props: {
					code: JSON.stringify(entries, null, 2),
				},
			});
		},
	},
};
</script>

<style>
.k-ui-form > footer {
	border-top: 1px dashed var(--color-border);
	padding-top: var(--spacing-6);
}
</style>
