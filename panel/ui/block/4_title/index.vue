<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-block-title />
		</k-ui-example>
		<k-ui-example label="Icon">
			<k-block-title :fieldset="{ icon: 'image' }" />
		</k-ui-example>
		<k-ui-example label="Icon & Name">
			<k-block-title :fieldset="{ icon: 'image', name: 'image' }" />
		</k-ui-example>
		<k-ui-example label="Icon & Name & Label">
			<k-block-title
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This is an image block',
				}"
			/>
		</k-ui-example>
		<k-ui-example label="Label template">
			<k-block-title
				:content="{
					title: 'Test',
				}"
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This is a {{title}}',
				}"
			/>
		</k-ui-example>
		<k-ui-example label="Label with HTML">
			<k-block-title
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This <b>has</b> some HTML',
				}"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>
