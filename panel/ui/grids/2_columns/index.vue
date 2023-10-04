<template>
	<k-ui-examples>
		<k-ui-example label="widths">
			<k-grid variant="columns">
				<k-box theme="passive" style="--width: 1/1">1/1</k-box>
				<k-box theme="passive" style="--width: 1/3">1/3</k-box>
				<k-box theme="passive" style="--width: 2/3">2/3</k-box>
				<k-box theme="passive" style="--width: 1/2">1/2</k-box>
				<k-box theme="passive" style="--width: 1/2">1/2</k-box>
				<k-box theme="passive" style="--width: 1/3">1/3</k-box>
				<k-box theme="passive" style="--width: 1/3">1/3</k-box>
				<k-box theme="passive" style="--width: 1/3">1/3</k-box>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="nested fields">
			<k-grid variant="columns">
				<div theme="passive" style="--width: 2/3">
					<k-grid variant="fields">
						<k-box theme="white" style="--width: 1/2">1/2</k-box>
						<k-box theme="white" style="--width: 1/2">1/2</k-box>
						<k-box theme="white" style="--width: 1/4">1/4</k-box>
						<k-box theme="white" style="--width: 3/4">3/4</k-box>
					</k-grid>
				</div>
				<div theme="passive" style="--width: 1/3">
					<k-grid variant="fields">
						<k-box theme="white" style="--width: 1/2">1/2</k-box>
						<k-box theme="white" style="--width: 1/2">1/2</k-box>
						<k-box theme="white" style="--width: 1/1">1/1</k-box>
					</k-grid>
				</div>
			</k-grid>
		</k-ui-example>
	</k-ui-examples>
</template>
