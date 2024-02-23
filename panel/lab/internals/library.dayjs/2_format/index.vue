<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				Formats a <code>dayjs</code> object based on a chosen format pattern.
				Check out
				<a href="https://day.js.org/docs/en/display/format">available formats</a>.
			</k-text>
		</k-box>

		<k-lab-example label="dayjs.pattern().format()" :code="false">
			<k-code language="javascript">this.$library.dayjs.pattern("Y/M/D").format(myDate): string|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/3">
					<k-box theme="code">{{ dt }}</k-box>
				</k-column>
				<k-column width="1/3">
					<k-input
						:value="pattern"
						type="text"
						style="min-width: 12rem"
						@input="pattern = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<k-box theme="code">{{ formatted ?? "-" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			dt: this.$library.dayjs(),
			pattern: "YYYY-MM-DD",
			formatted: null
		};
	},
	watch: {
		pattern: {
			handler() {
				this.format();
			},
			immediate: true
		}
	},
	methods: {
		format() {
			this.formatted = this.$library.dayjs
				.pattern(this.pattern)
				.format(this.dt);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
