<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The field loads its entries from its own endpoint. There is no model
				behind the lab, so the examples answer that request with static state
				instead. Everything that writes (sorting, uploads and batch deletion)
				still talks to the endpoint and answers with a 404 here.
			</k-box>

			<k-lab-example label="Default">
				<k-lab-filelist-field :initial="state(files)" label="Files" />
			</k-lab-example>

			<k-lab-example label="Loading">
				<k-lab-filelist-field label="Files" />
			</k-lab-example>

			<k-lab-example label="Loading: table">
				<k-lab-filelist-field :columns="columns" label="Files" layout="table" />
			</k-lab-example>

			<k-lab-example label="Help">
				<k-lab-filelist-field
					:initial="state(files)"
					help="Every file of this page"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-lab-filelist-field
					:initial="state([], { pagination: empty })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-lab-filelist-field
					:initial="state([], { pagination: empty })"
					empty="No images have been added yet"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Invalid: fewer than min">
				<k-lab-filelist-field
					:initial="state(files.slice(0, 1), { pagination: single })"
					:min="2"
					help="The label marks the field as invalid until a second file is added"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-lab-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-lab-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-lab-filelist-field
					:initial="state(files)"
					label="Files"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-lab-filelist-field
					:initial="state(tableRows, { columns })"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-lab-filelist-field
					:initial="state(tableRows, { columns: customColumns })"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Link to another parent">
				<k-lab-filelist-field
					:initial="state(files)"
					label="Files"
					link="/pages/photography"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-lab-filelist-field
					:endpoints="endpoints"
					:initial="state(files.slice(0, 3), { pagination: paginated })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-lab-filelist-field
					:endpoints="endpoints"
					:initial="state(files)"
					:searchable="true"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-lab-filelist-field
					:batch="true"
					:endpoints="endpoints"
					:initial="state(files)"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Upload">
				<k-lab-filelist-field
					:endpoints="endpoints"
					:initial="state(files, { upload })"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-lab-filelist-field
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
const field = {
	extends: window.panel.app.component("k-filelist-field"),
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
		"k-lab-filelist-field": field
	},
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
