<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="HTML">
			<k-box theme="info" text="Without <strong>HTML support</strong>" />
			<k-box
				:html="true"
				theme="info"
				text="With <strong>HTML support</strong>"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>
