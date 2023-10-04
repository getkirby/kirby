<template>
	<k-ui-options-input-examples
		:info="false"
		:value="value"
		type="toggles"
		@input="value = $event"
	>
		<template v-slot="{ options }">
			<k-ui-example label="Reset: false">
				<k-toggles-input
					:reset="false"
					:options="options"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Options with icons">
				<k-toggles-input
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Labels: false">
				<k-toggles-input
					:labels="false"
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Columns: 3">
				<k-toggles-input
					:columns="3"
					:labels="false"
					:options="optionsForColumns"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-ui-example>
			<k-ui-example label="Grow: true">
				<k-toggles-input
					:grow="true"
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-ui-example>
		</template>
	</k-ui-options-input-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
	computed: {
		optionsWithIcons() {
			return [
				{ text: "Left", value: "left", icon: "text-left" },
				{ text: "Center", value: "center", icon: "text-center" },
				{ text: "Right", value: "right", icon: "text-right" },
			];
		},
		optionsForColumns() {
			return [
				"grid-top-left",
				"grid-top",
				"grid-top-right",
				"grid-left",
				"grid",
				"grid-right",
				"grid-bottom-left",
				"grid-bottom",
				"grid-bottom-right",
			].map((item) => {
				return {
					text: item,
					icon: item,
					value: item,
				};
			});
		},
	},
};
</script>
