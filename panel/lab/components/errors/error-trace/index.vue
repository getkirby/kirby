<template>
	<k-lab-examples>
		<k-lab-example label="Trace">
			<k-error-trace
				:trace="[
					{
						file: '/some/test/file.php'
					},
					{
						file: '/some/test/file.php',
						line: 22
					},
					{
						file: '/some/test/file.php',
						line: 22,
						function: 'foo'
					},
					{
						file: '/some/test/file.php',
						line: 22,
						class: 'SomeClass',
						function: 'foo'
					},
					{
						file: '/some/test/file.php',
						url: 'vscode://',
						line: 22,
						class: 'SomeClass',
						type: '->',
						function: 'foo'
					},
					{
						class: 'SomeClass',
						type: '->',
						function: 'foo'
					}
				]"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
