<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="theme: none">
			<k-button v-for="theme in themes" :key="theme" :theme="theme" icon="edit">
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="theme: none | variant: dimmed">
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
		<k-lab-example :flex="true" label="theme: ${color}-icon | variant: dimmed ">
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
		<k-lab-example :flex="true" label="theme: ${color} | variant: filled">
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
		<k-lab-example :flex="true" label="theme: ${color}-icon | variant: filled">
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
export default {
	props: {
		themes: Array
	}
};
</script>
