<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following file helpers in your Vue components through
				<code>this.$helper.file</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.file.extension()" script="filename">
			<k-text>
				<p>Extracts the extension:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.file.extension(filename): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="filename" @input="filename = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.file.extension(filename) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.file.name()" script="filename">
			<k-text>
				<p>Extracts the name without extension:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.file.name(filename): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="filename" @input="filename = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.file.name(filename) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.file.niceSize()" script="size">
			<k-text>
				<p>Creates a nice human-readable file size string with size unit:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.file.niceSize(size): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="number" :value="size" @input="size = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.file.niceSize(size) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: filename */
export const filename = {
	data() {
		return {
			filename: "my-file.txt"
		};
	}
};
/** @script-end */

/** @script: size */
export const size = {
	data() {
		return {
			size: 1000
		};
	}
};
/** @script-end */

export default {
	mixins: [filename, size]
};
</script>
