<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				<p>
					Kirby provides at <code>$library.colors</code> several methods to parse, format and convert colors in HEX, RGB and HSL formats.
				</p>
			</k-text>
		</k-box>

		<k-lab-example
			label="$library.colors.parse()"
			:code="false"
			style="--color-frame-size: var(--input-height)"
		>
			<k-text>
				<p>Tries to parse a string as HEX, RGB or HSL color</p>
			</k-text>

			<k-code language="javascript">this.$library.colors.parse(string)</k-code>

			<k-grid variant="fields">
				<k-column width="1/2" class="flex">
					<k-color-frame :color="value" />
					<k-input
						:value="value"
						type="text"
						placeholder="Type color …"
						@input="parse"
					/>
				</k-column>
				<k-column width="1/2" class="flex">
					<k-box theme="code">{{ parsed || "–" }}</k-box>
					<k-color-frame
						:color="parsed ? $library.colors.toString(parsed) : value"
					/>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example
			label="$library.colors.parseAs()"
			:code="false"
			style="--color-frame-size: var(--input-height)"
		>
			<k-text>
				<p>
					Parses the input string and coverts it (if necessary) to the target color space
				</p>
			</k-text>

			<k-code language="javascript">this.$library.colors.parseAs(string, format)</k-code>

			<k-grid variant="fields">
				<k-column width="1/2" class="flex">
					<k-color-frame :color="valueAs" />
					<k-input
						:value="valueAs"
						type="text"
						placeholder="Type color …"
						@input="parseAs"
					/>
					<k-input
						type="select"
						:options="[
							{ text: 'hex', value: 'hex' },
							{ text: 'rgb', value: 'rgb' },
							{ text: 'hsl', value: 'hsl' }
						]"
						:empty="false"
						:value="parseAsFormat"
						@input="
							parseAsFormat = $event;
							parseAs(valueAs);
						"
					/>
				</k-column>
				<k-column width="1/2" class="flex">
					<k-box theme="code">{{ parsedAs || "–" }}</k-box>
					<k-color-frame
						:color="parsedAs ? $library.colors.toString(parsedAs) : value"
					/>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example
		<k-lab-example label="$library.colors.toString()" :code="false">
			<k-text>
				<p>Formats color as CSS string.</p>
			</k-text>

			<k-code language="javascript">this.$library.colors.toString(color, format, alpha)</k-code>

			<k-grid variant="fields">
				<k-column width="1/3">
					<h2>Input</h2>
					<k-code language="javascript">{{ color }}</k-code>
				</k-column>
				<k-column width="1/3">
					<h2>Format</h2>
					<k-input
						type="select"
						:options="[
							{ text: 'hex', value: 'hex' },
							{ text: 'rgb', value: 'rgb' },
							{ text: 'hsl', value: 'hsl' }
						]"
						:empty="false"
						:value="stringFormat"
						@input="stringFormat = $event"
					/>
					<k-input
						type="toggle"
						:text="['Alpha: false', 'Alpha: true']"
						:value="stringAlpha"
						@input="stringAlpha = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<h2>Result</h2>
					<k-code language="css">{{
						$library.colors.toString(color, stringFormat, stringAlpha)
					}}</k-code>
				</k-column>
			</k-grid>
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
			parseAsFormat: "hex",
			stringFormat: "hex",
			stringAlpha: true
		};
	},
	computed: {
		color() {
			return {
				r: 100,
				g: 200,
				b: 255,
				a: 0.5
			};
		}
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

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
.k-lab-example .k-column.flex {
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
}
.k-lab-example .k-color-frame {
	flex-shrink: 0;
}
.k-lab-example .k-input {
	width: 100%;
}
</style>
