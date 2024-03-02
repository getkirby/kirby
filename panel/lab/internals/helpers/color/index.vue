<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="info" icon="info">
			For parsing, formating and converting color values, you can use
			<k-link to="/lab/internals/library.colors">$library.colors</k-link>
		</k-box>

		<k-lab-example label="$helper.color()" :code="false">
			<k-text>
				<p>Resolves color alias to proper CSS values:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.color(alias): string</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">this.$helper.color("Pattern")</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="css">{{ $helper.color("Pattern") }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">this.$helper.color("purple")</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="css">{{ $helper.color("purple") }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript"
						>this.$helper.color("purple-500")</k-code
					>
				</k-column>
				<k-column width="1/2">
					<k-code language="css">{{ $helper.color("purple-500") }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">this.$helper.color("#ff00ff")</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="css">{{ $helper.color("#ff00ff") }}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-helpers-examples .k-link {
	text-decoration: underline;
}
</style>