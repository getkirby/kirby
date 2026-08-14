<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The examples below are rendered from static props. There is no model
				behind them, so everything that talks to the field endpoint (search,
				pagination, sorting, uploads and batch deletion) answers with a 404 here
				and only works in a real model view.
			</k-box>

			<k-lab-example label="Default">
				<k-filelist-field :initial="state(files)" label="Files" />
			</k-lab-example>

			<k-lab-example label="Help">
				<k-filelist-field
					:initial="state(files)"
					help="Every file of this page"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-filelist-field
					:initial="state([], { pagination: empty })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-filelist-field
					:initial="state([], { pagination: empty })"
					empty="No images have been added yet"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Invalid: fewer than min">
				<k-filelist-field
					:initial="state(files.slice(0, 1), { pagination: single })"
					:min="2"
					help="The label marks the field as invalid until a second file is added"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-filelist-field
					:initial="state(tableRows, { columns })"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-filelist-field
					:initial="state(tableRows, { columns: customColumns })"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Link to another parent">
				<k-filelist-field
					:initial="state(files)"
					label="Files"
					link="/pages/photography"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-filelist-field
					:endpoints="endpoints"
					:initial="state(files.slice(0, 3), { pagination: paginated })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-filelist-field
					:endpoints="endpoints"
					:initial="state(files)"
					:searchable="true"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-filelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="state(files)"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Upload">
				<k-filelist-field
					:endpoints="endpoints"
					:initial="state(files, { upload })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-filelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="
						state(files.slice(0, 3), {
							pagination: paginated,
							sortable: true,
							upload
						})
					"
					:searchable="true"
					help="Search, batch select, sorting and uploads at once"
					label="Files"
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
		files: Array,
		paginated: Object,
		pagination: Object,
		upload: Object
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
			return this.files.map((file) => ({
				...file,
				alt: "Alt text for " + file.text,
				title: {
					text: file.text,
					href: file.link
				}
			}));
		}
	},
	methods: {
		/**
		 * `FileListField::state()` sends the entries together with
		 * the columns, pagination, sorting and upload settings
		 */
		state(models, state = {}) {
			return {
				columns: {},
				models,
				pagination: this.pagination,
				sortable: false,
				upload: false,
				...state
			};
		}
	}
};
</script>
