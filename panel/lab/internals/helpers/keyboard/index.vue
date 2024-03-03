<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following keyboard helpers in your Vue components through
				<code>this.$helper.keyboard</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.keyboard.metaKey()" :code="false">
			<k-text>
				<p>Returns name of meta key for current OS:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.keyboard.metaKey(): string</k-code>
			</k-text>

			<h2>Result</h2>
			<k-code>{{ $helper.keyboard.metaKey() }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>
