<template>
	<k-lab-examples>
		<k-lab-example label="demo">
			<k-grid style="--columns: 2">
				<div>
					<k-button
						:dropdown="true"
						text="Dropdown"
						variant="filled"
						@click="$refs.dropdown.toggle()"
					/>
					<k-dropdown
						ref="dropdown"
						:options="options"
						:align-x="x"
						:align-y="y"
					/>
				</div>
				<k-grid style="--columns: 1; gap: 1.5rem">
					<k-radio-field
						:options="xOptions"
						name="x"
						label="align-x"
						:value="x"
						@input="x = $event"
					/>
					<k-radio-field
						:options="yOptions"
						name="y"
						label="align-y"
						:value="y"
						@input="y = $event"
					/>
				</k-grid>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			x: "start",
			y: "start"
		};
	},
	computed: {
		xOptions() {
			return [
				{
					text: "start (default)",
					value: "start"
				},
				{
					text: "center",
					value: "center"
				},
				{
					text: "end",
					value: "end"
				}
			];
		},
		yOptions() {
			return [
				{
					text: "top",
					value: "top"
				},
				{
					text: "bottom",
					value: "bottom"
				}
			];
		},
		options() {
			return [
				{
					text: "Edit",
					icon: "edit"
				},
				{
					text: "Duplicate",
					icon: "copy"
				},
				"-",
				{
					text: "Delete",
					icon: "trash"
				}
			];
		}
	}
};
</script>
