<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The examples below are rendered from static props. There is no model
				behind them, so everything that talks to the field endpoint – search,
				pagination, sorting and batch deletion – answers with a 404 here and
				only works in a real model view.
			</k-box>

			<k-lab-example label="Default">
				<k-pagelist-field
					:pages="pages"
					:pagination="pagination"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-pagelist-field
					:pages="pages"
					:pagination="pagination"
					help="Every child of this page"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-pagelist-field :pages="[]" :pagination="empty" label="Pages" />
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-pagelist-field
					:pages="[]"
					:pagination="empty"
					empty="No pages have been added yet"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-pagelist-field
					:pages="pages"
					:pagination="pagination"
					label="Pages"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-pagelist-field
					:pages="pages"
					:pagination="pagination"
					label="Pages"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-pagelist-field
					:pages="pages"
					:pagination="pagination"
					label="Pages"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-pagelist-field
					:columns="columns"
					:pages="tableRows"
					:pagination="pagination"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-pagelist-field
					:columns="customColumns"
					:pages="tableRows"
					:pagination="pagination"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-pagelist-field
					:endpoints="endpoints"
					:pages="pages.slice(0, 2)"
					:pagination="paginated"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-pagelist-field
					:endpoints="endpoints"
					:pages="pages"
					:pagination="pagination"
					:searchable="true"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-pagelist-field
					:batch="true"
					:endpoints="endpoints"
					:pages="pages"
					:pagination="pagination"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Add">
				<k-pagelist-field
					:add="true"
					:endpoints="endpoints"
					:pages="pages"
					:pagination="pagination"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-pagelist-field
					:add="true"
					:batch="true"
					:endpoints="endpoints"
					:pages="pages.slice(0, 2)"
					:pagination="paginated"
					:searchable="true"
					:sortable="true"
					help="Search, batch select, sorting and adding at once"
					label="Pages"
				/>
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	props: {
		columns: Object,
		customColumns: Object,
		endpoints: Object,
		pages: Array,
		paginated: Object,
		pagination: Object
	},
	computed: {
		empty() {
			return { limit: 20, offset: 0, page: 1, total: 0 };
		},
		/**
		 * `ModelListField::columnsValues()` adds the cell values
		 * for the table layout to every entry
		 */
		tableRows() {
			return this.pages.map((page) => ({
				...page,
				title: {
					text: page.text,
					href: page.link
				}
			}));
		}
	}
};
</script>
