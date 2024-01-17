<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block-title />
		</k-lab-example>
		<k-lab-example label="Icon">
			<k-block-title :fieldset="{ icon: 'image' }" />
		</k-lab-example>
		<k-lab-example label="Icon & Name">
			<k-block-title :fieldset="{ icon: 'image', name: 'image' }" />
		</k-lab-example>
		<k-lab-example label="Icon & Name & Label">
			<k-block-title
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This is an image block'
				}"
			/>
		</k-lab-example>
		<k-lab-example label="Label template">
			<k-block-title
				:content="{
					title: 'Test'
				}"
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This is a {{title}}'
				}"
			/>
		</k-lab-example>
		<k-lab-example label="Label with HTML">
			<k-block-title
				:fieldset="{
					icon: 'image',
					name: 'image',
					label: 'This <b>has</b> some HTML'
				}"
			/>
		</k-lab-example>
		<k-lab-example label="No name, but label (fallback)">
			<k-block-title
				:fieldset="{
					icon: 'image',
					label: 'image'
				}"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
