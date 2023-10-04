<template>
	<k-ui-examples>
		<k-ui-example
			:flex="true"
			label="$library.colors.parse()"
			style="--color-frame-size: var(--input-height)"
		>
			<div>
				<k-color-frame :color="value" />
			</div>
			<k-input type="text" placeholder="Type color …" @input="parse" />

			<div>&rarr;</div>

			<k-box theme="code">{{ parsed || "{}" }}</k-box>
			<div>
				<k-color-frame
					:color="parsed !== null ? $library.colors.toString(parsed) : value"
				/>
			</div>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			parsed: null,
			value: null,
		};
	},
	methods: {
		parse(value) {
			this.value = value;
			this.parsed = this.$library.colors.parse(value);
		},
	},
};
</script>
