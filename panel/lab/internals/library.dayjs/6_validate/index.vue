<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					Validates a <code>dayjs</code> object against an upper or lower
					(<code>max</code>/<code>min</code>) boundary (ISO strings).
				</p>
			</k-text>
		</k-box>

		<k-lab-example label="dayjs().validate()" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">myDayjsObject.validate("{{ boundary }}", "{{ type }}"): bool</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Datetime (ISO string)</k-label>
					<k-input
						type="text"
						placeholder="2027-01-05 09:00:00"
						:value="datetime"
						style="min-width: 12rem"
						@input="datetime = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Boundary (ISO string)</k-label>
					<k-input
						type="text"
						placeholder="No boundary"
						:value="boundary"
						style="min-width: 12rem"
						@input="boundary = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Type</k-label>
					<k-input
						type="select"
						:options="types"
						:required="true"
						:empty="false"
						:value="type"
						@input="type = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>Result</k-label>
					<k-box :theme="result === true ? 'positive' : 'negative'">
						{{ result === null ? "Not a valid ISO datetime" : result }}
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
			boundary: "2027-01-05 09:00:00",
			datetime: "2027-01-05 23:59:59",
			type: "max",
			types: [
				{ text: "min", value: "min" },
				{ text: "max", value: "max" }
			]
		};
	},
	computed: {
		result() {
			const dt = this.$library.dayjs.iso(this.datetime);

			if (dt === null) {
				return null;
			}

			return dt.validate(this.boundary, this.type);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
.k-lab-examples .k-text .k-link {
	text-decoration: underline;
}
</style>
