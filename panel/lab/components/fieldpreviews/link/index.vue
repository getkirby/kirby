<template>
	<k-lab-examples>
		<k-lab-example label="URL">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="https://getkirby.com" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Email">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="mailto:mail@getkirby.com" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Tel">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="tel:+4912345678" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Page">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="page://q6Ja1wxCXavVF1cL" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="File">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="file://IIQpSczd4Cabewmx" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Anchor">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview value="#anchor" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="URL with overflow">
			<k-lab-table-cell>
				<!-- @code -->
				<k-link-field-preview
					value="https://getkirby.com/this-is-a-super-long-link-lahsdjkahskdjahskjdhaksjdhkajshdkashdjashdkahjsdkjahsdkjashdkjahskdjhaksjhdkashjdkahjsdajshkdhjahjkd"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
