<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-choice-input name="choice" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-choice-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-choice-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-choice-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-choice-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Label">
				<k-choice-input
					:checked="value"
					label="Option"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Label & Info">
				<k-choice-input
					:checked="value"
					info="This is some info text"
					label="Option"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Label & Info & Icon">
				<k-choice-input
					:checked="value"
					info="This is some info text"
					icon="heart"
					label="Option"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Type: radio">
				<k-choice-input
					:checked="value"
					info="This is some info text"
					label="Option"
					type="radio"
					@input="value = $event"
				/>
			</k-lab-example>
			<k-lab-example label="Variant: toggle">
				<k-choice-input
					:checked="value"
					info="This is some info text"
					label="Option"
					variant="toggle"
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
	}
};
</script>
