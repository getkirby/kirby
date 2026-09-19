<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The field loads its entries from its own endpoint. There is no model
				behind the lab, so the examples answer that request with static state
				instead. Everything that writes (sorting and batch deletion) still talks
				to the endpoint and answers with a 404 here.
			</k-box>

			<k-lab-example label="Default">
				<k-lab-pagelist-field :initial="state(pages)" label="Pages" />
			</k-lab-example>

			<k-lab-example label="Loading">
				<k-lab-pagelist-field label="Pages" />
			</k-lab-example>

			<k-lab-example label="Loading: table">
				<k-lab-pagelist-field :columns="columns" label="Pages" layout="table" />
			</k-lab-example>

			<k-lab-example label="Help">
				<k-lab-pagelist-field
					:initial="state(pages)"
					help="Every child of this page"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-lab-pagelist-field
					:initial="state([], { pagination: empty })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-lab-pagelist-field
					:initial="state([], { pagination: empty })"
					empty="No pages have been added yet"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Invalid: fewer than min">
				<k-lab-pagelist-field
					:initial="state(pages.slice(0, 1), { pagination: single })"
					:min="2"
					help="The label marks the field as invalid until a second page is added"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-lab-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-lab-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-lab-pagelist-field
					:initial="state(pages)"
					label="Pages"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-lab-pagelist-field
					:initial="state(tableRows, { columns })"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-lab-pagelist-field
					:initial="state(tableRows, { columns: customColumns })"
					label="Pages"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Link to another parent">
				<k-lab-pagelist-field
					:initial="state(pages)"
					label="Pages"
					link="/pages/photography"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-lab-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages.slice(0, 2), { pagination: paginated })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-lab-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages)"
					:searchable="true"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-lab-pagelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="state(pages)"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="Add">
				<k-lab-pagelist-field
					:endpoints="endpoints"
					:initial="state(pages, { add: true })"
					label="Pages"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-lab-pagelist-field
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
const field = {
	extends: window.panel.app.component("k-pagelist-field"),
	props: {
		initial: Object
	},
	methods: {
		async reload() {
			if (this.initial === undefined) {
				return;
			}

			this.state = this.initial;
			this.isLoading = false;
		}
	}
};

export default {
	components: {
		"k-lab-pagelist-field": field
	},
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
