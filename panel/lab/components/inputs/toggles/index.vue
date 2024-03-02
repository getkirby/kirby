<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-toggles-input
					name="toggles"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-toggles-input
					:autofocus="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-toggles-input
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-toggles-input
					ref="input"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-toggles-input
					:disabled="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Reset: true">
				<k-toggles-input
					:reset="true"
					:options="options"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Options with icons">
				<k-toggles-input
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Labels: false">
				<k-toggles-input
					:labels="false"
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Columns: 3">
				<k-toggles-input
					:columns="3"
					:labels="false"
					:options="optionsForColumns"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Grow: true">
				<k-toggles-input
					:grow="true"
					:options="optionsWithIcons"
					:value="value"
					name="toggles"
					@input="value = $event"
				/>
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			value: null
		};
	},
	computed: {
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		},
		optionsWithIcons() {
			return [
				{ text: "Left", value: "left", icon: "text-left" },
				{ text: "Center", value: "center", icon: "text-center" },
				{ text: "Right", value: "right", icon: "text-right" }
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
				"grid-bottom-right"
			].map((item) => {
				return {
					text: item,
					icon: item,
					value: item
				};
			});
		}
	}
};
</script>
