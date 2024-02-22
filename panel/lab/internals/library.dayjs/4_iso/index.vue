<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				Parse an ISO datetime, date or time string as a
				<code>dayjs</code> object. Or convert a <code>dayjs</code> object to an
				ISO string.
			</k-text>
		</k-box>

		<k-lab-example label="dayjs.iso()" :code="false">
			<k-code language="javascript">this.$library.dayjs.iso("2023-09-12", "date"): dayjs</k-code>

			<k-grid variant="fields">
				<k-column width="1/4">
					<k-input
						type="text"
						:placeholder="stringPlaceholder"
						style="min-width: 12rem"
						@input="parse"
					/>
				</k-column>
				<k-column width="1/4">
					<k-input
						type="select"
						:options="[
							{ text: 'datetime', value: 'full' },
							{ text: 'date', value: 'date' },
							{ text: 'time', value: 'time' }
						]"
						:empty="false"
						:value="mode"
						@input="mode = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-box theme="code">{{ string ?? "-" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.toISO()" :code="false">
			<k-code language="javascript">myDayjsObject.toIso("date"): string</k-code>

			<k-input
				type="number"
				placeholder="Year"
				:value="year"
				@input="
					year = $event;
					generate();
				"
			/>
			<k-input
				type="number"
				placeholder="Month"
				:value="month"
				@input="
					month = $event;
					generate();
				"
			/>
			<k-input
				type="number"
				placeholder="Day"
				:value="day"
				@input="
					day = $event;
					generate();
				"
			/>
			<k-input
				type="number"
				placeholder="Hour"
				:value="hour"
				@input="
					hour = $event;
					generate();
				"
			/>
			<k-input
				type="number"
				placeholder="Minute"
				:value="minute"
				@input="
					minute = $event;
					generate();
				"
			/>
			<k-input
				type="number"
				placeholder="Second"
				:value="second"
				@input="
					second = $event;
					generate();
				"
			/>

			<k-input
				type="select"
				:options="[
					{ text: 'datetime', value: 'full' },
					{ text: 'date', value: 'date' },
					{ text: 'time', value: 'time' }
				]"
				:empty="false"
				:value="mode"
				@input="
					mode = $event;
					generate();
				"
			/>

			<k-box theme="code">{{ iso ?? "-" }}</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			mode: "full",
			string: null,
			year: 2023,
			month: 1,
			day: 1,
			hour: 0,
			minute: 0,
			second: 0,
			iso: null
		};
	},
	computed: {
		stringPlaceholder() {
			if (this.mode === "full") {
				return "2023-01-01 00:00:00";
			}

			if (this.mode === "date") {
				return "2023-01-01";
			}

			return "00:00:00";
		}
	},
	mounted() {
		this.generate();
	},
	methods: {
		parse(input) {
			this.string = this.$library.dayjs.iso(input, this.mode);
		},
		generate() {
			this.iso = this.$library
				.dayjs(
					new Date(
						this.year,
						this.month,
						this.day,
						this.hour,
						this.minute,
						this.second
					)
				)
				.toISO(this.mode);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
</style>
