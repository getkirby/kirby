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
			<k-code language="javascript"
				>this.$library.dayjs.iso("2023-09-12", "date"): dayjs</k-code
			>

			<div>
				<k-input
					type="text"
					:placeholder="stringPlaceholder"
					style="min-width: 12rem"
					@input="parse"
				/>

				<k-input
					v-model="mode"
					type="select"
					:options="[
						{ text: 'datetime', value: 'full' },
						{ text: 'date', value: 'date' },
						{ text: 'time', value: 'time' }
					]"
					:empty="false"
				/>

				<div>&rarr;</div>

				<k-box theme="code">{{ string ?? "-" }}</k-box>
			</div>
		</k-lab-example>

		<k-lab-example label="dayjs.toISO()" :code="false">
			<k-code language="javascript">myDayjsObject.toIso("date"): string</k-code>

			<div>
				<div>
					<k-input
						v-model="year"
						type="number"
						placeholder="Year"
						@input="generate"
					/>
					<k-input
						v-model="month"
						type="number"
						placeholder="Month"
						@input="generate"
					/>
					<k-input
						v-model="day"
						type="number"
						placeholder="Day"
						@input="generate"
					/>
					<k-input
						v-model="hour"
						type="number"
						placeholder="Hour"
						@input="generate"
					/>
					<k-input
						v-model="minute"
						type="number"
						placeholder="Minute"
						@input="generate"
					/>
					<k-input
						v-model="second"
						type="number"
						placeholder="Second"
						@input="generate"
					/>
				</div>

				<k-input
					v-model="mode"
					type="select"
					:options="[
						{ text: 'datetime', value: 'full' },
						{ text: 'date', value: 'date' },
						{ text: 'time', value: 'time' }
					]"
					:empty="false"
					@input="generate"
				/>

				<div>&rarr;</div>

				<k-box theme="code">{{ iso ?? "-" }}</k-box>
			</div>
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
.k-lab-example-canvas > .k-code {
	margin-bottom: var(--spacing-6);
}

.k-lab-example-canvas > div {
	display: flex;
	align-items: center;
	gap: var(--spacing-6);
}
</style>
