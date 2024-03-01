<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.debounce()" :code="false">
			<k-text>
				<p>Debounces the callback function:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">const original = (a, b) => {};
const debounced = this.$helper.debounce(original, 500);</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
