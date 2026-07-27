<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					<code>dayjs.iso()</code> parses an ISO datetime, date or time string
					as a <code>dayjs</code> object, <code>dayjs().toISO()</code> converts
					a <code>dayjs</code> object back to an ISO string. The three supported
					formats are datetime (<code>YYYY-MM-DD HH:mm:ss</code>), date
					(<code>YYYY-MM-DD</code>) and time (<code>HH:mm:ss</code>).
				</p>
			</k-text>
		</k-box>

		<k-lab-example label="dayjs.iso()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.iso(input{{ format ? ', "' + format + '"' : null }}): dayjs|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/3">
					<k-label>Input</k-label>
					<k-input
						type="text"
						:placeholder="placeholder"
						:value="string"
						style="min-width: 12rem"
						@input="string = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<k-label>Format</k-label>
					<k-input
						type="select"
						:options="inputFormats"
						:required="true"
						:empty="false"
						:value="format"
						@input="format = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<k-label>Result</k-label>
					<k-box theme="code">{{ parsed?.toISO(selected) ?? "null" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs().toISO()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">myDayjsObject.toISO("date"): string</k-code>

			<k-grid variant="fields">
				<k-column v-for="unit in units" :key="unit.name" width="1/6">
					<k-label>{{ unit.label }}</k-label>
					<k-input
						type="number"
						:min="unit.min"
						:max="unit.max"
						:value="datetime[unit.name]"
						@input="datetime[unit.name] = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Format</k-label>
					<k-input
						type="select"
						:options="formats"
						:required="true"
						:empty="false"
						:value="output"
						@input="output = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Result</k-label>
					<k-box theme="code">{{ iso }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			datetime: {
				year: 2023,
				month: 1,
				day: 1,
				hour: 0,
				minute: 0,
				second: 0
			},
			format: "datetime",
			formats: [
				{ text: "datetime", value: "datetime" },
				{ text: "date", value: "date" },
				{ text: "time", value: "time" }
			],
			output: "datetime",
			string: "",
			units: [
				{ name: "year", label: "Year", min: 1000, max: 9999 },
				{ name: "month", label: "Month", min: 1, max: 12 },
				{ name: "day", label: "Day", min: 1, max: 31 },
				{ name: "hour", label: "Hour", min: 0, max: 23 },
				{ name: "minute", label: "Minute", min: 0, max: 59 },
				{ name: "second", label: "Second", min: 0, max: 59 }
			]
		};
	},
	computed: {
		inputFormats() {
			return [{ text: "any", value: "" }, ...this.formats];
		},
		iso() {
			const { year, month, day, hour, minute, second } = this.datetime;

			return this.$library
				.dayjs(
					// the month of `Date` is zero-based
					new Date(
						Number(year),
						Number(month) - 1,
						Number(day),
						Number(hour),
						Number(minute),
						Number(second)
					)
				)
				.toISO(this.output);
		},
		parsed() {
			return this.$library.dayjs.iso(this.string, this.selected);
		},
		selected() {
			return this.format === "" ? undefined : this.format;
		},
		placeholder() {
			if (this.format === "date") {
				return "2027-01-01";
			}

			if (this.format === "time") {
				return "00:00:00";
			}

			return "2027-01-01 00:00:00";
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
