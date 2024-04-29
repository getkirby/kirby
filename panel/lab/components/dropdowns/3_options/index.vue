<template>
	<k-lab-examples>
		<k-lab-example
			:flex="true"
			label="multiple options"
			script="multipleOptions"
		>
			<k-options-dropdown :options="multipleOptions" variant="filled" />
		</k-lab-example>
		<k-lab-example :flex="true" label="single options" script="singleOption">
			<k-options-dropdown :options="singleOption" variant="filled" />
		</k-lab-example>
		<k-lab-example
			:flex="true"
			label="click string handler"
			script="clickStringHandler"
		>
			<k-options-dropdown
				:options="optionsWithStringHandler"
				variant="filled"
				@action="onAction"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: multipleOptions */
export const multipleOptions = {
	computed: {
		multipleOptions() {
			return [
				{
					text: "Edit",
					icon: "edit",
					click: () => console.log("Edit")
				},
				{
					text: "Duplicate",
					icon: "copy",
					click: () => console.log("Duplicate")
				},
				"-",
				{
					text: "Delete",
					icon: "trash",
					click: () => console.log("Delete")
				}
			];
		}
	}
};
/** @script-end */

/** @script: singleOption */
export const singleOption = {
	computed: {
		singleOption() {
			return [
				{
					text: "Edit",
					icon: "edit",
					click: () => console.log("Edit")
				}
			];
		}
	}
};
/** @script-end */

/** @script: clickStringHandler */
export const clickStringHandler = {
	computed: {
		optionsWithStringHandler() {
			return [
				{
					text: "Edit",
					icon: "edit",
					click: "Edit"
				},
				{
					text: "Duplicate",
					icon: "copy",
					click: "Duplicate"
				},
				"-",
				{
					text: "Delete",
					icon: "trash",
					click: "Delete"
				}
			];
		}
	},
	methods: {
		onAction(action) {
			console.log(action);
		}
	}
};
/** @script-end */

export default {
	mixins: [multipleOptions, singleOption, clickStringHandler]
};
</script>
