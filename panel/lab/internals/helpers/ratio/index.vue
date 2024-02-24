<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.ratio()" script="fraction">
			<k-text>
				<p>Returns a percentage string for the provided fraction:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.ratio(fraction, fallback, vertical): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="fraction" @input="fraction = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<div class="result" :style="{ 'padding-bottom': ratio }">
						<span>{{ ratio }}</span>
					</div>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: fraction */
export const fraction = {
	data() {
		return {
			fraction: "3/2"
		};
	},
	computed: {
		ratio() {
			return this.$helper.ratio(this.fraction);
		}
	}
};
/** @script-end */

export default {
	mixins: [fraction]
};
</script>

<style>
.result {
	background-color: var(--color-border);
	border-radius: var(--rounded);
}

.result > span {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}
</style>
