<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-url-field-preview value="https://getkirby.com" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow">
			<k-lab-table-cell>
				<!-- @code -->
				<k-url-field-preview
					value="https://getkirby.com/this-is-a-super-long-link-lahsdjkahskdjahskjdhaksjdhkajshdkashdjashdkahjsdkjahsdkjashdkjahskdjhaksjhdkashjdkahjsdajshkdhjahjkd"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-url-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
