<template>
	<k-lab-examples>
		<k-lab-example label="Unstyled">
			<k-box icon="mastodon" text="Unstyled box" />
		</k-lab-example>
		<k-lab-example label="Text">
			<k-grid style="--columns: 1; gap: 0.5rem">
				<k-box
					v-for="theme in themes"
					:key="theme"
					:theme="theme"
					:text="theme"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="Text & Icon">
			<k-grid style="--columns: 1; gap: 0.5rem">
				<k-box
					v-for="theme in themes"
					:key="theme"
					:theme="theme"
					:text="theme"
					icon="box"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="Text, Icon & Link">
			<k-grid style="--columns: 1; gap: 0.5rem">
				<k-box
					v-for="theme in themes"
					:key="theme"
					:html="true"
					:theme="theme"
					text='Text with <a href="/">URL</a>'
					icon="box"
				/>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		themes: Array
	}
};
</script>
