<template>
	<k-lab-examples>
		<k-lab-example label="default">
			<k-toolbar
				:buttons="[
					{
						icon: 'headline',
						dropdown: [
							{ icon: 'h1', label: 'Headline 1' },
							{ icon: 'h2', label: 'Headline 2' },
							{ icon: 'h3', label: 'Headline 3' },
							'-',
							{ icon: 'list-bullet', label: 'Unordered list' },
							{ icon: 'list-numbers', label: 'Ordered list' }
						]
					},
					'|',
					{ icon: 'bold' },
					{ icon: 'italic', current: true },
					{ icon: 'underline' },
					'|',
					{ icon: 'url' },
					{ icon: 'email' }
				]"
			/>
		</k-lab-example>

		<k-lab-example label="dark">
			<k-toolbar
				:buttons="[
					{
						icon: 'headline',
						current: true,
						dropdown: [
							{ icon: 'h1', label: 'Headline 1' },
							{ icon: 'h2', label: 'Headline 2' },
							{ icon: 'h3', label: 'Headline 3', current: true },
							'-',
							{ icon: 'list-bullet', label: 'Unordered list', disabled: true },
							{ icon: 'list-numbers', label: 'Ordered list', disabled: true }
						]
					},
					'|',
					{ icon: 'bold' },
					{ icon: 'italic' },
					{ icon: 'underline' },
					'|',
					{ icon: 'url' },
					{ icon: 'email' }
				]"
				theme="dark"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {};
</script>
