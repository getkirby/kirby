<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					<code>dayjs.parse()</code> turns what a user typed into a
					<code>dayjs</code> object. It first matches the input against the
					source pattern exactly, and unless <code>strict</code> is set, falls
					back to guessing: the input is tried against a list of known unit
					orders, the first one that reads all of it wins. Separators and number
					widths never have to match, so <code>6/26/2025</code> is read by a
					<code>MM-DD-YYYY</code> pattern just as well.
				</p>
			</k-text>
		</k-box>

		<div class="k-lab-example-meta" data-theme="blue">
			<k-grid variant="fields">
				<k-column width="1/2">
					<k-text-field
						label="Format pattern"
						:value="format"
						@input="format = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-select-field
						label="Type"
						:options="types"
						:value="type"
						@input="type = $event"
					/>
				</k-column>
			</k-grid>
		</div>

		<k-lab-example label="dayjs.parse()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.parse(input, {{ options }}): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/3">
					<k-label>Input</k-label>
					<k-input
						type="text"
						:value="input"
						placeholder="Type a date …"
						style="min-width: 12rem"
						@input="input = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<k-label>Result</k-label>
					<k-box theme="code">{{ parsed?.toISO() ?? "null" }}</k-box>
				</k-column>
				<k-column width="1/3">
					<k-label>Formatted</k-label>
					<k-box theme="code">{{ formatted ?? "null" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.parse(): strict mode" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.parse(input, { pattern: "{{ format }}", strict: true }): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/3">
					<k-label>Input</k-label>
					<k-input
						type="text"
						:value="input"
						placeholder="Type a date …"
						style="min-width: 12rem"
						@input="input = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<k-label>Strict</k-label>
					<k-box theme="code">{{ strict?.toISO() ?? "null" }}</k-box>
				</k-column>
				<k-column width="1/3">
					<k-label>Not strict</k-label>
					<k-box theme="code">{{ parsed?.toISO() ?? "null" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			input: "",
			format: "DD.MM.YYYY",
			type: "",
			types: [
				{ text: "date", value: "date" },
				{ text: "time", value: "time" }
			]
		};
	},
	computed: {
		formatted() {
			return this.pattern.format(this.parsed);
		},
		options() {
			const options = { pattern: this.format };

			if (this.type !== "") {
				options.type = this.type;
			}

			return JSON.stringify(options);
		},
		parsed() {
			return this.$library.dayjs.parse(this.input, {
				pattern: this.format,
				type: this.type === "" ? undefined : this.type
			});
		},
		pattern() {
			return this.$library.dayjs.pattern(this.format);
		},
		strict() {
			return this.$library.dayjs.parse(this.input, {
				pattern: this.format,
				strict: true
			});
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
.k-lab-example .k-text {
	margin-bottom: var(--spacing-6);
}
</style>
