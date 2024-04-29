<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="theme: none" script="themes">
			<k-button v-for="theme in themes" :key="theme" :theme="theme" icon="edit">
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example
			:flex="true"
			label="theme: none | variant: dimmed"
			script="themes"
		>
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="dimmed"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example
			:flex="true"
			label="theme: ${color}-icon | variant: dimmed "
			script="themes"
		>
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme ? theme + '-icon' : null"
				variant="dimmed"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example
			:flex="true"
			label="theme: ${color} | variant: filled"
			script="themes"
		>
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="filled"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example
			:flex="true"
			label="theme: ${color}-icon | variant: filled"
			script="themes"
		>
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme ? theme + '-icon' : null"
				variant="filled"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="theme: empty">
			<k-button theme="empty" variant="filled" icon="edit"> Button </k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: themes */
export const themes = {
	computed: {
		themes() {
			return [
				null,
				"red",
				"orange",
				"yellow",
				"green",
				"aqua",
				"blue",
				"purple",
				"pink"
			];
		}
	}
};
/** @script-end */

export default {
	mixins: [themes]
};
</script>
