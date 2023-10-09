<template>
	<k-lab-examples>
		<k-lab-example
			label="$library.colors.parse()"
			:code="false"
			:flex="true"
			style="--color-frame-size: var(--input-height)"
		>
			<div>
				<k-color-frame :color="value" />
			</div>
			<k-input type="text" placeholder="Type color …" @input="parse" />

			<div>&rarr;</div>

			<k-box theme="code">{{ parsed ?? "{}" }}</k-box>
			<div>
				<k-color-frame
					:color="parsed ? $library.colors.toString(parsed) : value"
				/>
			</div>
		</k-lab-example>

		<k-lab-example
			label="$library.colors.parseAs()"
			:code="false"
			:flex="true"
			style="--color-frame-size: var(--input-height)"
		>
			<div>
				<k-color-frame :color="valueAs" />
			</div>
			<k-input type="text" placeholder="Type color …" @input="parseAs" />

			<div>&rarr;</div>

			<k-input
				v-model="parseAsFormat"
				type="select"
				:options="[
					{ text: 'hex', value: 'hex' },
					{ text: 'rgb', value: 'rgb' },
					{ text: 'hsl', value: 'hsl' }
				]"
				:empty="false"
				@input="parseAs(valueAs)"
			/>

			<div>&rarr;</div>

			<k-box theme="code">{{ parsedAs || "{}" }}</k-box>
			<div>
				<k-color-frame
					:color="parsedAs ? $library.colors.toString(parsedAs) : value"
				/>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			parsed: null,
			value: null,
			parsedAs: null,
			valueAs: null,
			parseAsFormat: "hex"
		};
	},
	methods: {
		parse(value) {
			this.value = value;
			this.parsed = this.$library.colors.parse(value);
		},
		parseAs(value) {
			this.valueAs = value;
			this.parsedAs = this.$library.colors.parseAs(value, this.parseAsFormat);
		}
	}
};
</script>
