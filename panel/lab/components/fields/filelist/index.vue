<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The examples below are rendered from static props. There is no model
				behind them, so everything that talks to the field endpoint – search,
				pagination, sorting, uploads and batch deletion – answers with a 404
				here and only works in a real model view.
			</k-box>

			<k-lab-example label="Default">
				<k-filelist-field
					:data="files"
					:pagination="pagination"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-filelist-field
					:data="files"
					:pagination="pagination"
					help="Every file of this page"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Empty">
				<k-filelist-field :data="[]" :pagination="empty" label="Files" />
			</k-lab-example>

			<k-lab-example label="Empty with custom text">
				<k-filelist-field
					:data="[]"
					:pagination="empty"
					empty="No images have been added yet"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cardlets">
				<k-filelist-field
					:data="files"
					:pagination="pagination"
					label="Files"
					layout="cardlets"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards">
				<k-filelist-field
					:data="files"
					:pagination="pagination"
					label="Files"
					layout="cards"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: cards, size small">
				<k-filelist-field
					:data="files"
					:pagination="pagination"
					label="Files"
					layout="cards"
					size="small"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table">
				<k-filelist-field
					:columns="columns"
					:data="tableRows"
					:pagination="pagination"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Layout: table with columns">
				<k-filelist-field
					:columns="customColumns"
					:data="tableRows"
					:pagination="pagination"
					label="Files"
					layout="table"
				/>
			</k-lab-example>

			<k-lab-example label="Pagination">
				<k-filelist-field
					:data="files.slice(0, 3)"
					:endpoints="endpoints"
					:pagination="paginated"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Search">
				<k-filelist-field
					:data="files"
					:endpoints="endpoints"
					:pagination="pagination"
					:search="true"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Batch">
				<k-filelist-field
					:batch="true"
					:data="files"
					:endpoints="endpoints"
					:pagination="pagination"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="Upload">
				<k-filelist-field
					:data="files"
					:endpoints="endpoints"
					:pagination="pagination"
					:upload="upload"
					label="Files"
				/>
			</k-lab-example>

			<k-lab-example label="All options">
				<k-filelist-field
					:batch="true"
					:data="files.slice(0, 3)"
					:endpoints="endpoints"
					:pagination="paginated"
					:search="true"
					:sortable="true"
					:upload="upload"
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
	}
};
</script>
