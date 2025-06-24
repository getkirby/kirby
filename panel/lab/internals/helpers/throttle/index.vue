<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.throttle()" :code="false">
			<k-text>
				<p>Throttles the callback function:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">const original = (a, b) => {};
const throttled = this.$helper.throttle(original, 500);</k-code>
			</k-text>

			<k-text-field @input="throttledLog1($event)" />

			<k-code v-if="output1">{{ output1 }}</k-code>
		</k-lab-example>

		<k-lab-example label="Options: leading/trailing" :code="false">
			<k-text>
				<p>
					With the <code>options</code> argument you can modify the behavior:
				</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">const throttled = this.$helper.throttle(original, 500, { leading: {{ leading }}, trailing: {{ trailing }} });</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-toggle-field
						:text="['leading: false', 'leading: true']"
						:value="leading"
						@input="leading = $event"
					/>
				</k-column>

				<k-column width="1/2">
					<k-toggle-field
						:text="['trailing: false', 'trailing: true']"
						:value="trailing"
						@input="trailing = $event"
					/>
				</k-column>
			</k-grid>

			<k-text-field @input="throttledLog2($event)" />

			<k-code v-if="output2">{{ output2 }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			output1: "",
			output2: "",
			leading: true,
			trailing: false
		};
	},
	computed: {
		throttledLog1() {
			return this.$helper.throttle(this.log1, 500);
		},
		throttledLog2() {
			return this.$helper.throttle(this.log2, 500, {
				leading: this.leading,
				trailing: this.trailing
			});
		}
	},
	methods: {
		log1(input) {
			this.output1 += "Called: " + input + "\n";
		},
		log2(input) {
			this.output2 += "Called: " + input + "\n";
		}
	}
};
</script>

<style>
.k-lab-example-canvas > .k-text-field {
	margin-bottom: var(--spacing-6);
}
</style>
