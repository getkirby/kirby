<template>
	<k-ui-examples>
		<k-ui-example label="Unstyled">
			<k-box icon="mastodon" text="Unstyled box" />
		</k-ui-example>
		<k-ui-example label="Text">
			<k-grid style="--columns: 1; gap: 0.5rem">
				<k-box
					v-for="theme in themes"
					:key="theme"
					:theme="theme"
					:text="theme"
				/>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="Text & Icon">
			<k-grid style="--columns: 1; gap: 0.5rem">
				<k-box
					v-for="theme in themes"
					:key="theme"
					:theme="theme"
					:text="theme"
					icon="box"
				/>
			</k-grid>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		themes: Array,
	},
};
</script>
