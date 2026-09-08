<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The examples below are rendered from static props. There is no model
				behind them, so everything that talks to the field endpoint (search,
				pagination, sorting and batch deletion) answers with a 404 here and only
				works in a real model view.
			</k-box>

			<k-lab-example label="Default">
				<k-pagelist-field :initial="state(pages)" label="Pages" />
			</k-lab-example>

			<k-lab-example label="Help">
				<k-pagelist-field
					:initial="state(pages)"
					help="Every child of this page"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-pagelist-field
					:initial="state([], { pagination: empty })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-pagelist-field
					:initial="state([], { pagination: empty })"
					empty="No pages have been added yet"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Invalid: fewer than min">
				<k-pagelist-field
					:initial="state(pages.slice(0, 1), { pagination: single })"
					:min="2"
					help="The label marks the field as invalid until a second page is added"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-pagelist-field
					:initial="state(tableRows, { columns })"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-pagelist-field
					:initial="state(tableRows, { columns: customColumns })"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Link to another parent">
				<k-pagelist-field
					:initial="state(pages)"
					label="Pages"
					link="/pages/photography"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages.slice(0, 2), { pagination: paginated })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages)"
					:searchable="true"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-pagelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="state(pages)"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Add">
				<k-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages, { add: true })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-pagelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="
						state(pages.slice(0, 2), {
							add: true,
							pagination: paginated,
							sortable: true
						})
					"
					:searchable="true"
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
		single() {
			return { limit: 20, offset: 0, page: 1, total: 1 };
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
	},
	methods: {
		/**
		 * `PageListField::state()` sends the entries together with
		 * the columns, pagination, sorting and the add button
		 */
		state(models, state = {}) {
			return {
				add: false,
				columns: {},
				models,
				pagination: this.pagination,
				sortable: false,
				...state
			};
		}
	}
};
</script>
