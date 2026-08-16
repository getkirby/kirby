<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					Rounds a <code>dayjs</code> object to the nearest step. All sub-units
					of the step unit are cleared. The step size has to divide the ceiling
					of the unit evenly, e.g. <code>15</code> of 60 minutes or
					<code>4</code> of 24 hours. Calendar units (<code>date</code>,
					<code>month</code>, <code>year</code>) have no divisible ceiling and
					only support a size of <code>1</code>. Anything else throws an error.
				</p>

				<p>
					Rounding cascades stepwise from the smallest sub-unit upwards, which
					is not always the absolutely nearest step: <code>13:45</code> rounded
					to a 4 hour step first carries the minutes over to
					<code>14:00</code> and then rounds up to <code>16:00</code>, instead
					of down to the nearer <code>12:00</code>. This is intended, as it
					keeps the behavior predictable while typing.
				</p>
			</k-text>
		</k-box>

		<k-lab-example label="dayjs().round()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">myDayjsObject.round("minute", 15): dayjs</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Datetime</k-label>
					<k-input
						type="text"
						placeholder="2023-09-12 13:45:32.600"
						:value="datetime"
						style="min-width: 12rem"
						@input="datetime = $event"
					/>
				</k-column>
				<k-column width="1/4">
					<k-label>Unit</k-label>
					<k-input
						type="select"
						:options="units"
						:required="true"
						:empty="false"
						:value="unit"
						@input="unit = $event"
					/>
				</k-column>
				<k-column width="1/4">
					<k-label>Size</k-label>
					<k-input type="number" min="1" :value="size" @input="size = $event" />
				</k-column>
				<k-column>
					<k-label>Result</k-label>
					<k-box :theme="result.error ? 'negative' : 'code'">
						{{ result.error ?? result.value }}
					</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			datetime: "2023-09-12 13:45:32.600",
			size: 15,
			unit: "minute",
			units: [
				{ text: "millisecond", value: "millisecond" },
				{ text: "second", value: "second" },
				{ text: "minute", value: "minute" },
				{ text: "hour", value: "hour" },
				{ text: "date", value: "date" },
				{ text: "month", value: "month" },
				{ text: "year", value: "year" }
			]
		};
	},
	computed: {
		result() {
			// parsed here rather than with `dayjs.iso()`, which has no
			// millisecond format and the milliseconds are the point here
			const dt = this.$library.dayjs(this.datetime);

			if (dt.isValid() === false) {
				return { error: "Not a valid datetime" };
			}

			try {
				return {
					// milliseconds are shown, since clearing and carrying
					// them over is part of what rounding does, and `toISO()`
					// would hide it
					value: dt
						.round(this.unit, Number(this.size))
						.format("YYYY-MM-DD HH:mm:ss.SSS")
				};
			} catch (error) {
				return { error: error.message };
			}
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
