<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				Tries to parse the free-text input as a <code>dayjs</code>
				date or time object.
			</k-text>
		</k-box>

		<k-lab-example label="dayjs.interpret(input, 'date')" :code="false">
			<k-code language="javascript">this.$library.dayjs.interpret("23-09-12", "date"): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-input
						type="text"
						placeholder="Type date …"
						style="min-width: 16rem"
						@input="interpret($event, 'date')"
					/>
				</k-column>
				<k-column width="1/2">
					<k-box theme="code">{{ date ?? "-" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.interpret(input, 'time')" :code="false">
			<k-code language="javascript">this.$library.dayjs.interpret("9:12", "time"): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-input
						type="text"
						placeholder="Type time …"
						style="min-width: 16rem"
						@input="interpret($event, 'time')"
					/>
				</k-column>
				<k-column width="1/2">
					<k-box theme="code">{{ time ?? "-" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			date: null,
			time: null
		};
	},
	methods: {
		interpret(input, format = "date") {
			this[format] = this.$library.dayjs
				.interpret(input, format)
				?.toISO(format);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
