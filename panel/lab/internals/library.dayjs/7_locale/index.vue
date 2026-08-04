<template>
	<k-lab-examples>
		<k-lab-example label="dayjs.load()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.locale("{{ code }}"): string</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Kirby translation code</k-label>
					<k-input
						type="select"
						:options="codes"
						:required="true"
						:value="code"
						@input="load($event)"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Activated dayjs locale</k-label>
					<k-box theme="code">{{ locale }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Parsing" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.parse(input, { pattern: "{{ pattern }}" }): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Input</k-label>
					<k-input
						type="text"
						:value="input"
						placeholder="Type date …"
						style="min-width: 12rem"
						@input="input = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Result</k-label>
					<k-box theme="code">{{ parsed ?? "null" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="Formatting" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.pattern("{{ pattern }}").format(parsed): string|null</k-code>

			<k-box theme="code">{{ formatted ?? "null" }}</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		codes: Array
	},
	data() {
		return {
			code: this.$panel.translation.code,
			formatted: null,
			input: "",
			locale: null,
			parsed: null,
			pattern: "D MMMM YYYY"
		};
	},
	watch: {
		input() {
			this.update();
		}
	},
	created() {
		this.load(this.code);
	},
	methods: {
		load(code) {
			this.code = code;
			this.locale = this.$library.dayjs.locale(code);
			this.update();
		},
		update() {
			const dayjs = this.$library.dayjs;
			const dt = dayjs.parse(this.input, { pattern: this.pattern });

			this.parsed = dt?.toISO("date") ?? null;

			this.formatted = dayjs
				.pattern(this.pattern)
				.format(this.input === "" ? dayjs() : dt);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
